<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$dbConfig = require BASE_PATH . '/config/database.php';
$db = new Database($dbConfig);
$auth = new Auth($db->getConnection());
$auth->logout();

header('Location: login.php');
exit;
?>