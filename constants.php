<?php
const SECRET_KEY = 'a4074458293g';
const ALGORITHM = 'HS512';
const SESSION_DURATION = 86400;

const WRONG_OR_MISSING_HEADERS_ERROR = 'Отсутствуют необходимые заголовки или значения указанных заголовков невалидны'; // 400
const WRONG_OR_MISSING_PARAMS_ERROR = 'Отсутствуют необходимые параметры или указанные параметры невалидны'; // 400
const EXPIRED_SESSION_ERROR = 'Сессия устарела. Авторизируйтесь заново.'; // 401
const EXPIRED_SESSION_OR_WRONG_TOKEN_ERROR = 'Сессия устарела или токен аутенфикации неверный'; // 401
const FORBIDDEN_ERROR = 'У вас недостаточно прав для выполнения этого запроса'; // 403
const WRONG_METHOD_ERROR = 'Метод не поддерживается'; // 405
