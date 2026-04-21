<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$config = require BASE_PATH . '/config/config.php';
$dbConfig = require BASE_PATH . '/config/database.php';

$db = new Database($dbConfig);
$auth = new Auth($db->getConnection());
$auth->requireLogin();
$auth->requirePermission('view_data');

$pdo = $db->getConnection();
$message = '';
$filter = [
    'search' => $_GET['search'] ?? '',
    'sort' => $_GET['sort'] ?? 'submitted_at',
    'order' => $_GET['order'] ?? 'DESC',
    'page' => (int)($_GET['page'] ?? 1),
    'per_page' => 25,
];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM household_data WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0;">✓ Entry deleted successfully!</div>';
        }
    } catch (Exception $e) {
        $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

// Handle CSV export
if ($_GET['export'] ?? false) {
    $auth->requirePermission('export_data');
    
    $stmt = $pdo->query("SELECT * FROM household_data ORDER BY submitted_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="household_data_' . date('Y-m-d_H-i-s') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM for Excel UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]), ';');
        
        // Data rows
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }
    
    fclose($output);
    exit;
}

// Build search query
$where = [];
$params = [];

if (!empty($filter['search'])) {
    $searchTerm = '%' . $filter['search'] . '%';
    $where[] = "(postcode LIKE ? OR huisnummer LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM household_data $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $filter['per_page']);

// Ensure page is valid
$filter['page'] = max(1, min($filter['page'], $totalPages ?: 1));

// Get data
$offset = ($filter['page'] - 1) * $filter['per_page'];
$allowedSorts = ['id', 'postcode', 'huisnummer', 'zonnepanelen', 'verbruik', 'opwek', 'submitted_at'];
$sortField = in_array($filter['sort'], $allowedSorts) ? $filter['sort'] : 'submitted_at';
$orderDir = $filter['order'] === 'ASC' ? 'ASC' : 'DESC';

$query = "SELECT * FROM household_data $whereClause ORDER BY $sortField $orderDir LIMIT $offset, {$filter['per_page']}";

//$query = "SELECT * FROM household_data $whereClause ORDER BY $sortField $orderDir LIMIT ?, ?";
//$params[] = $offset;
//$params[] = $filter['per_page'];

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_entries,
        SUM(CASE WHEN zonnepanelen = 1 THEN 1 ELSE 0 END) as with_solar,
        AVG(verbruik) as avg_consumption,
        AVG(opwek) as avg_production,
        MAX(submitted_at) as last_entry
    FROM household_data
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Management - Rekentool</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin: 0 0 20px 0;
            border-bottom: 3px solid #ff7716;
            padding-bottom: 10px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-right {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        .btn-success.disabled { background: #ccc; cursor: not-allowed; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #ff7716;
            border-radius: 4px;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        .filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
            align-items: end;
        }
        .search-box {
            width: 100%;
        }
        .search-box input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
            font-size: 13px;
            cursor: pointer;
            user-select: none;
        }
        th:hover { background: #e9ecef; }
        th a { color: #333; text-decoration: none; }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        tr:hover { background: #f9f9f9; }
        .actions-cell {
            display: flex;
            gap: 5px;
        }
        .action-btn {
            padding: 4px 8px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }
        .action-delete {
            background: #dc3545;
            color: white;
        }
        .action-delete:hover { background: #c82333; }
        .pagination {
            display: flex;
            gap: 5px;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
            font-size: 13px;
        }
        .pagination a:hover { background: #e9ecef; }
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .sort-arrow {
            margin-left: 5px;
            font-size: 11px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
        }
        .modal-header { font-weight: bold; margin-bottom: 15px; }
        .modal-buttons { margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Huishoudgegevens Management</h1>
            <div class="header-right">
                <a href="content.php" class="btn btn-secondary">← Terug naar Content</a>
                <?php if ($auth->canExportData()): ?>
                <a href="?export=1" class="btn btn-success">📥 Export naar CSV</a>
                <?php endif; ?>
                <a href="users.php" class="btn btn-secondary">Gebruikersbeheer</a>
                <a href="../logout.php" class="btn btn-danger">Uitloggen</a>
            </div>
        </div>

        <?php echo $message; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Totaal Inzendingen</div>
                <div class="stat-value"><?php echo $stats['total_entries']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Met Zonnepanelen</div>
                <div class="stat-value"><?php echo $stats['with_solar']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Gem. Verbruik (kWh)</div>
                <div class="stat-value"><?php echo round((float)($stats['avg_consumption'] ?? 0)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Gem. Opwek (kWh)</div>
                <div class="stat-value"><?php echo round((float)($stats['avg_production'] ?? 0)); ?></div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="filters">
            <div class="search-box">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="search" placeholder="Zoeken op postcode of huisnummer..." value="<?php echo htmlspecialchars($filter['search']); ?>">
                    <button type="submit" class="btn">Zoeken</button>
                    <?php if (!empty($filter['search'])): ?>
                    <a href="data.php" class="btn btn-secondary">Wissen</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Data Table -->
        <?php if (empty($data)): ?>
            <div class="empty-state">
                <p>Geen gegevens gevonden</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'id', 'order' => $filter['sort'] === 'id' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">ID <?php if ($filter['sort'] === 'id') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'postcode', 'order' => $filter['sort'] === 'postcode' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">Postcode <?php if ($filter['sort'] === 'postcode') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'huisnummer', 'order' => $filter['sort'] === 'huisnummer' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">Huisnummer <?php if ($filter['sort'] === 'huisnummer') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th>Toevoeging</th>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'zonnepanelen', 'order' => $filter['sort'] === 'zonnepanelen' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">Zonnepanelen <?php if ($filter['sort'] === 'zonnepanelen') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'verbruik', 'order' => $filter['sort'] === 'verbruik' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">Verbruik (kWh) <?php if ($filter['sort'] === 'verbruik') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'opwek', 'order' => $filter['sort'] === 'opwek' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">Opwek (kWh) <?php if ($filter['sort'] === 'opwek') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th><a href="?<?php echo http_build_query(array_merge($filter, ['sort' => 'submitted_at', 'order' => $filter['sort'] === 'submitted_at' && $filter['order'] === 'DESC' ? 'ASC' : 'DESC'])); ?>">Ingediend <?php if ($filter['sort'] === 'submitted_at') echo '<span class="sort-arrow">' . ($filter['order'] === 'DESC' ? '↓' : '↑') . '</span>'; ?></a></th>
                        <th style="width: 80px;">Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['postcode']); ?></td>
                        <td><?php echo htmlspecialchars($row['huisnummer']); ?></td>
                        <td><?php echo htmlspecialchars($row['toevoeging']); ?></td>
                        <td><?php echo $row['zonnepanelen'] ? '✓ Ja' : 'Nee'; ?></td>
                        <td><?php echo number_format($row['verbruik'], 0, ',', '.'); ?></td>
                        <td><?php echo number_format($row['opwek'], 0, ',', '.'); ?></td>
                        <td><?php echo date('d-m-Y H:i', strtotime($row['submitted_at'])); ?></td>
                        <td class="actions-cell">
                            <button onclick="showDeleteModal(<?php echo $row['id']; ?>)" class="action-btn action-delete">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($filter['page'] > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($filter, ['page' => 1])); ?>">« Eerste</a>
                    <a href="?<?php echo http_build_query(array_merge($filter, ['page' => $filter['page'] - 1])); ?>">‹ Vorige</a>
                <?php else: ?>
                    <span class="disabled">« Eerste</span>
                    <span class="disabled">‹ Vorige</span>
                <?php endif; ?>

                <?php for ($i = max(1, $filter['page'] - 2); $i <= min($totalPages, $filter['page'] + 2); $i++): ?>
                    <?php if ($i === $filter['page']): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($filter, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($filter['page'] < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($filter, ['page' => $filter['page'] + 1])); ?>">Volgende ›</a>
                    <a href="?<?php echo http_build_query(array_merge($filter, ['page' => $totalPages])); ?>">Laatste »</a>
                <?php else: ?>
                    <span class="disabled">Volgende ›</span>
                    <span class="disabled">Laatste »</span>
                <?php endif; ?>
            </div>
            <div style="text-align: center; margin-top: 10px; font-size: 13px; color: #666;">
                Pagina <?php echo $filter['page']; ?> van <?php echo $totalPages; ?> (<?php echo $total; ?> totaal)
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">Entry verwijderen</div>
            <p>Weet je zeker dat je deze entry wilt verwijderen? Dit kan niet ongedaan gemaakt worden.</p>
            <div class="modal-buttons">
                <button onclick="hideDeleteModal()" class="btn btn-secondary">Annuleren</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Verwijderen</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showDeleteModal(id) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function hideDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>