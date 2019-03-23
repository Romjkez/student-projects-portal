<?php
require_once '../headers.php';
require_once '../../database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (is_numeric($_POST['id']) && is_numeric($_POST['status']) && ($_POST['status'] == 1 || $_POST['status'] == 2)) {
        $db = new Database();
        $check = $db->connection->prepare("SELECT * FROM applications WHERE id=:id");
        $check->bindParam(':id', $_POST['id']);
        $check->execute();
        $checkResult = $check->fetchObject();
        if ($checkResult->status == $_POST['status']) {
            http_response_code(200);
            echo json_encode(['message' => 'This value is already set']);
        } else {
            if ($_POST['status'] == 1 && $checkResult->status != 1) {
                // include worker in project
                $project = $db->connection->prepare("SELECT members FROM projects_new WHERE id=:project_id");
                $project->bindParam(':project_id', $checkResult->project_id);
                $project->execute();
                $projectMembers = json_decode(($project->fetch())[0]);
                $projectMembers = updateMembers($projectMembers, $checkResult->team, $checkResult->role, $checkResult->worker_id);

                if (is_array($projectMembers)) {
                    $insertMembers = $db->connection->prepare("UPDATE projects_new SET members=:members WHERE id=:id");
                    $insertMembers->bindParam(':id', $checkResult->project_id);
                    $insertMembers->bindParam(':members', json_encode($projectMembers));
                    $insertResult = $insertMembers->execute();
                    http_response_code(200);
                    if ($insertResult == true) {
                        // set project from app active for worker
                        $userUpdate = $db->connection->prepare("SELECT active_projects FROM users WHERE id=:worker_id");
                        $userUpdate->bindParam(':worker_id', $checkResult->worker_id);
                        $userUpdate->execute();
                        $activeProjects = $userUpdate->fetch()[0];
                        if (count($activeProjects) < 1) {
                            $activeProjects .= $checkResult->project_id;
                        } else {
                            $activeProjects = explode(',', $activeProjects);
                            array_push($activeProjects, $checkResult->project_id);
                            $activeProjects = implode($activeProjects, ',');
                        }

                        $insertActiveProjects = $db->connection->prepare("UPDATE users SET active_projects=:ap WHERE id=:worker_id");
                        $insertActiveProjects->bindParam(':ap', $activeProjects);
                        $insertActiveProjects->bindParam(':worker_id', $checkResult->worker_id);
                        $actResult = $insertActiveProjects->execute();
                        if ($actResult == true) {
                            // update status of application
                            $q = $db->connection->prepare("UPDATE applications SET status=:s WHERE id=:id");
                            $q->bindParam(':id', $_POST['id']);
                            $q->bindParam(':s', $_POST['status']);
                            $res = $q->execute();
                            if ($res == true) {
                                echo json_encode(['message' => true]);
                            } else {
                                echo json_encode(['message' => false, 'code' => '110']);
                            }
                        } else {
                            echo json_encode(['message' => false, 'code' => '100']);
                        }
                    } else {
                        echo json_encode(['message' => false, 'code' => '000']);
                    }
                } else {
                    echo json_encode(['message' => $projectMembers]);
                }

            } else if ($_POST['status'] == 2 && $checkResult->status != 2) {
                $q = $db->connection->prepare("UPDATE applications SET status=:s WHERE id=:id");
                $q->bindParam(':id', $_POST['id']);
                $q->bindParam(':s', $_POST['status']);
                $res = $q->execute();
                if ($res == true) {
                    echo json_encode(['message' => true]);
                } else {
                    echo json_encode(['message' => false, 'code' => '000']);
                }
            } else {
                echo json_encode(['message' => 'Application is already accepted/declined']);
            }
        }
    } else {
        http_response_code(200);
        echo json_encode(['message' => 'Specify both status and application id(1 or 2)']);
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Method not supported']);
}

/**
 * @param array $members
 * @param int $team
 * @param string $role
 * @param int $worker_id
 * @return string | array
 */
function updateMembers(array $members, int $team, string $role, int $worker_id)
{
    for ($i = 0; $i < count($members); $i++) {
        if ($i === $team) {
            foreach ($members[$i] as $key => &$value) {
                if ($key == $role) {
                    if ($value === 0) {
                        $value = $worker_id;
                    } else {
                        return 'This role is already occupied by other user';
                    }
                }
            }
        }
    }
    return $members;
}

/**
 * @param object $application
 * @return string
 */
function getUserActiveProjects(object $application)
{
    $db = new Database();

    //return $activeProjects;
}
