<?php
try {
    require __DIR__ . '/app/bootstrap.php';

    $config     = require BASE_PATH . '/config/config.php';
    $dbConfig   = require BASE_PATH . '/config/database.php';
    $mailConfig = require BASE_PATH . '/config/mail.php';

    $db     = new Database($dbConfig);
    $mailer= new Mailer($mailConfig);
    $form  = new FormHandler($db, $mailer);

    // Log the POST data
    error_log(date('Y-m-d H:i:s') . ' - POST: ' . json_encode($_POST));

    $form->handle($_POST);
    echo "Data submitted successfully.";
} catch (Exception $e) {
    error_log(date('Y-m-d H:i:s') . ' - Error: ' . $e->getMessage());
    echo "Error: " . $e->getMessage();
}