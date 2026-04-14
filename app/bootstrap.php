<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/Database.php';
require BASE_PATH . '/app/Mailer.php';
require BASE_PATH . '/app/FormHandler.php';
// require BASE_PATH . '/app/Security.php';