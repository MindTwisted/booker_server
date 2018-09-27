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

$tablePrefix = ENV === 'tests' ?
    DB_TABLE_TEST_PREFIX : DB_TABLE_PREFIX;

Input::collectInput();

Model::setDbPrefix($tablePrefix);
Model::setBuilder($queryBuilder);

Validator::setDbPrefix($tablePrefix);
Validator::setBuilder($queryBuilder);

Auth::setDbPrefix($tablePrefix);
Auth::setBuilder($queryBuilder);
Auth::setTokenExpiresTime(AUTH_TOKEN_EXPIRES);

View::setRenderType(DEFAULT_VIEW_TYPE);