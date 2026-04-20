<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$dbConfig = require BASE_PATH . '/config/database.php';
$contentManager = new ContentManager($dbConfig);

$message = '';
$sectionKey = '';
$currentContent = '';
$allContent = [];

// Try to get all content entries
try {
    $allContent = $contentManager->getAllContent();
} catch (Exception $e) {
    $message = '<div style="color: orange; padding: 10px; background: #fff3cd; margin: 10px 0;">Note: No content found yet. <a href="' . dirname(__DIR__) . '/create_content_table.php">Click here to initialize the database table.</a></div>';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_key'], $_POST['content'])) {
    $sectionKey = trim($_POST['section_key']);
    $content = $_POST['content'];
    
    try {
        $contentManager->setContent($sectionKey, $content);
        $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0; border-radius: 3px;">✓ Content saved successfully!</div>';
        // Refresh the content list
        $allContent = $contentManager->getAllContent();
    } catch (Exception $e) {
        $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0; border-radius: 3px;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_section'])) {
    $toggleKey = trim($_POST['toggle_section']);
    
    try {
        $contentManager->toggleActive($toggleKey);
        $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0; border-radius: 3px;">✓ Content toggled successfully!</div>';
        // Refresh the content list
        $allContent = $contentManager->getAllContent();
    } catch (Exception $e) {
        $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0; border-radius: 3px;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Load content for preview
if ($sectionKey) {
    $currentContent = $contentManager->getContent($sectionKey);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Manager - Rekentool</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #ff7716;
            padding-bottom: 15px;
            margin-top: 0;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        @media (max-width: 1000px) {
            .content-grid { grid-template-columns: 1fr; }
        }
        .editor-panel, .browser-panel {
            padding: 20px;
            background: #fafafa;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        .panel-title {
            font-weight: bold;
            color: #ff7716;
            margin-bottom: 15px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        input[type="text"], textarea, input[type="search"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, textarea:focus, input[type="search"]:focus {
            outline: none;
            border-color: #ff7716;
            box-shadow: 0 0 0 3px rgba(255, 119, 22, 0.1);
        }
        textarea {
            min-height: 250px;
            resize: vertical;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        button {
            background: #ff7716;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }
        button:hover {
            background: #e56c00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 119, 22, 0.3);
        }
        button:active {
            transform: translateY(0);
        }
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 5px solid;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .content-table thead {
            background: #f0f0f0;
            border-bottom: 2px solid #ff7716;
        }
        .content-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
        }
        .content-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .content-table tbody tr:hover {
            background: #f9f9f9;
            cursor: pointer;
        }
        .content-table tbody tr {
            transition: all 0.2s;
        }
        .key-cell {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #ff7716;
        }
        .preview-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
        }
        .updated-cell {
            font-size: 12px;
            color: #999;
        }
        .search-box {
            margin-top: 15px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        .empty-state p {
            font-size: 16px;
            margin: 10px 0;
        }
        .info {
            background: #f0f0f0;
            padding: 15px;
            border-left: 4px solid #ff7716;
            margin-top: 20px;
            color: #666;
            border-radius: 3px;
            font-size: 13px;
            line-height: 1.6;
        }
        .info strong {
            color: #333;
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Content Beheer - Rekentool</h1>
        
        <?php if ($message): ?>
            <div class="message-box" style="color: <?php echo strpos($message, 'Error') !== false ? '#721c24' : '#155724'; ?>; background: <?php echo strpos($message, 'Error') !== false ? '#f8d7da' : '#d4edda'; ?>; border-left-color: <?php echo strpos($message, 'Error') !== false ? '#dc3545' : '#28a745'; ?>;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-grid">
            <!-- Editor Panel -->
            <div class="editor-panel">
                <div class="panel-title">Content wijzigen</div>
                
                <form method="POST">
                    <label for="section_key">Sectie Key:</label>
                    <input type="text" id="section_key" name="section_key" placeholder="e.g., section_left, info_banner" required value="<?php echo htmlspecialchars($sectionKey); ?>">
                    
                    <label for="content">Content (HTML toegestaan):</label>
                    <textarea id="content" name="content" placeholder="Enter your HTML content here..." required><?php echo htmlspecialchars($currentContent); ?></textarea>
                    
                    <label for="preview">Voorvertoning:</label>
                    <div id="preview" style="border: 1px solid #ddd; padding: 10px; min-height: 100px; background: #f9f9f9; margin-top: 8px; border-radius: 4px;"></div>
                    
                    <button type="submit">Aanpassingen opslaan</button>
                </form>
            </div>
            
            <!-- Browser Panel -->
            <div class="browser-panel">
                <div class="panel-title">Bestaande content</div>
                
                <?php if (empty($allContent)): ?>
                    <div class="empty-state">
                        <p>Nog geen content</p>
                        <p style="font-size: 12px;">Maak een eerste veld links</p>
                    </div>
                <?php else: ?>
                    <label for="search_box">Zoeken:</label>
                    <input type="search" id="search_box" placeholder="Zoek op content..." class="search-box">
                    
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>Sectie Key</th>
                                <th>Voorbeeld</th>
                                <th>Laatst gewijzigd</th>
                                <th>Actief</th>
                            </tr>
                        </thead>
                        <tbody id="content_table_body">
                            <?php foreach ($allContent as $item): ?>
                                <tr class="content-row" data-key="<?php echo htmlspecialchars($item['section_key']); ?>" data-content="<?php echo htmlspecialchars($item['content']); ?>" onclick="loadContent(this)">
                                    <td class="key-cell"><?php echo htmlspecialchars($item['section_key']); ?></td>
                                    <td class="preview-cell" title="<?php echo htmlspecialchars(strip_tags($item['content'])); ?>"><?php echo htmlspecialchars(substr(strip_tags($item['content']), 0, 50)); ?>...</td>
                                    <td class="updated-cell"><?php echo date('M d, H:i', strtotime($item['updated_at'])); ?></td>                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Weet je zeker dat je deze sectie wilt togglen?')">
                                            <input type="hidden" name="toggle_section" value="<?php echo htmlspecialchars($item['section_key']); ?>">
                                            <button type="submit" style="background: <?php echo $item['active'] ? '#28a745' : '#dc3545'; ?>; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                                <?php echo $item['active'] ? 'Aan' : 'Uit'; ?>
                                            </button>
                                        </form>
                                    </td>                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button id="show-more-btn" style="margin-top: 15px; background: #ff7716; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Meer tonen</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="info">
            <strong>Hoe te gebruiken:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Nieuwe aanmaken:</strong> Vul een unieke key in en de daarbij behorende content, klik daarna op opslaan. Let op, dit maakt niet direct een nieuwe sectie op de tool.</li>
                <li><strong>Wijzig bestaande:</strong> Klik op een bestaan de key, vul de nieuwe inhoud in en klik op opslaan. Let op, alleen bestaande keys op de tool worden aangepast.</li>
                <li><strong>Sectie keys:</strong> in het overzicht wordt gehanteerd: titlex - de titel in de meest linker kolom, woordx - 1 meest linker, 2 middelste, 3 meest rechter kolom.</li>
                <li><strong>Caching:</strong> De content wordt voor 1 uur gecached voor betere prestaties.</li>
                <li><strong>Cell uitzetten:</strong> Als je een cell wilt uitzetten (en daarmee terug gaat naar de standaard tekst) klik je op Aan en ja. Wil je hem weer aan? Klik dan op Uit en ja.</li>
                <li><strong>Regel uitzetten:</strong> Wil je een regel uitzetten? Doe dan hetzelfde als bij cell uitzetten, maar enkel op de titel van de rij (pakketx of overzichtx; zonder a-b-c), de hele rij wordt onzichtbaar.</li>
            </ul>
        </div>
    </div>
    
    <script>
        function loadContent(row) {
            const sectionKey = row.getAttribute('data-key');
            const content = row.getAttribute('data-content');
            document.getElementById('section_key').value = sectionKey;
            document.getElementById('content').value = content;
            updatePreview();
            document.getElementById('section_key').focus();
            
            // Scroll form into view
            document.querySelector('.editor-panel').scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Highlight selected row
            document.querySelectorAll('.content-row').forEach(r => r.style.background = '');
            row.style.background = '#fff3cd';
        }
        
        // Search functionality
        document.getElementById('search_box')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.content-row').forEach(row => {
                const sectionKey = row.getAttribute('data-key').toLowerCase();
                row.style.display = sectionKey.includes(searchTerm) ? '' : 'none';
            });
        });

        // Live preview
        function updatePreview() {
            const content = document.getElementById('content').value;
            const preview = document.getElementById('preview');
            preview.innerHTML = content;
        }
        document.getElementById('content').addEventListener('input', updatePreview);
        // Initialize preview
        updatePreview();

        // Show more functionality
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.content-row');
            const showMoreBtn = document.getElementById('show-more-btn');
            const initialVisible = 5;
            let showingMore = false;

            if (rows.length > initialVisible) {
                // Hide rows beyond initial visible
                rows.forEach((row, index) => {
                    if (index >= initialVisible) {
                        row.style.display = 'none';
                    }
                });

                showMoreBtn.addEventListener('click', function() {
                    showingMore = !showingMore;
                    rows.forEach((row, index) => {
                        if (index >= initialVisible) {
                            row.style.display = showingMore ? '' : 'none';
                        }
                    });
                    showMoreBtn.textContent = showingMore ? 'Minder tonen' : 'Meer tonen';
                });
            } else {
                showMoreBtn.style.display = 'none';
            }
        });
    </script>
</body>
</html>