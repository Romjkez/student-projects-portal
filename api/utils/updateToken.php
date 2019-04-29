<?php
function updateToken($token)
{
    return [
        'iat' => $token->iat,
        'upd' => time(),
        'jti' => $token->jti,
        'iss' => $token->iss,
        'exp' => $token->exp + ($token->upd - $token->iat),
        'data' => [
            'email' => $token->data->email,
            'usergroup' => $token->data->usergroup,
            'id' => $token->data->id
        ]
    ];
}
