<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$dbConfig = require BASE_PATH . '/config/database.php';
$contentManager = new ContentManager($dbConfig);

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$sectionKey = $_GET['section'] ?? '';

try {
    if ($action === 'get' && $sectionKey) {
        $content = $contentManager->getContent($sectionKey);
        http_response_code(200);
        echo json_encode(['success' => true, 'content' => $content], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid request parameters']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>