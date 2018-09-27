<?php

use libs\Env\Env;

define('DB_HOST', Env::get('DB_HOST'));
define('DB_PORT', Env::get('DB_PORT'));
define('DB_DATABASE', Env::get('DB_DATABASE'));
define('DB_USER', Env::get('DB_USER'));
define('DB_PASSWORD', Env::get('DB_PASSWORD'));
define('DB_TABLE_PREFIX', Env::get('DB_TABLE_PREFIX'));
define('DB_TABLE_TEST_PREFIX', Env::get('DB_TABLE_TEST_PREFIX'));

define('ROOT_DIR', Env::get('ROOT_DIR'));
define('STORAGE_PATH', Env::get('STORAGE_PATH'));
define('ROOT_URL', Env::get('ROOT_URL'));

define('SECRET_KEY', Env::get('SECRET_KEY'));

define('ENV', Env::get('ENV'));

define('DEFAULT_VIEW_TYPE', 'json'); // json, html, xml, txt
define('AUTH_TOKEN_EXPIRES', 3600); // seconds