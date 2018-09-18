<?php

use libs\Validator\Validator;
use libs\Auth;
use libs\View;
use libs\Input\Input;
use libs\QueryBuilder\src\QueryBuilder;
use app\models\Model;

$queryBuilder = new QueryBuilder(
    'mysql',
    DB_HOST,
    DB_PORT,
    DB_DATABASE,
    DB_USER,
    DB_PASSWORD
);

Input::collectInput();

Model::setDbPrefix(DB_TABLE_PREFIX);
Model::setBuilder($queryBuilder);

Validator::setDbPrefix(DB_TABLE_PREFIX);
Validator::setBuilder($queryBuilder);

Auth::setDbPrefix(DB_TABLE_PREFIX);
Auth::setBuilder($queryBuilder);
Auth::setTokenExpiresTime(AUTH_TOKEN_EXPIRES);

View::setRenderType(DEFAULT_VIEW_TYPE);