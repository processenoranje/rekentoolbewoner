<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$config = require BASE_PATH . '/config/config.php';
$dbConfig = require BASE_PATH . '/config/database.php';

$db = new Database($dbConfig);
$auth = new Auth($db->getConnection());
$auth->requireLogin();
$auth->requirePermission('manage_users');

$message = '';
$users = $auth->getAllUsers();
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'create_user':
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $email = trim($_POST['email'] ?? '');
                $fullName = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? 'admin';

                if (empty($username) || empty($password)) {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Username and password are required.</div>';
                } elseif ($auth->createUser($username, $password, $email, $fullName, $role)) {
                    $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0;">User created successfully.</div>';
                    $users = $auth->getAllUsers(); // Refresh the list
                } else {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Failed to create user. Username may already exist.</div>';
                }
                break;

            case 'change_password':
                $userId = (int)($_POST['user_id'] ?? 0);
                $newPassword = $_POST['new_password'] ?? '';

                if (empty($newPassword)) {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">New password is required.</div>';
                } elseif ($auth->changePassword($userId, $newPassword)) {
                    $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0;">Password changed successfully.</div>';
                } else {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Failed to change password.</div>';
                }
                break;

            case 'toggle_user':
                $userId = (int)($_POST['user_id'] ?? 0);
                $active = isset($_POST['active']) ? 1 : 0;

                if ($auth->updateUser($userId, ['active' => $active])) {
                    $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0;">User status updated successfully.</div>';
                    $users = $auth->getAllUsers(); // Refresh the list
                } else {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Failed to update user status.</div>';
                }
                break;

            case 'change_role':
                $userId = (int)($_POST['user_id'] ?? 0);
                $newRole = $_POST['role'] ?? 'viewer';
                $allowedRoles = ['admin', 'editor', 'viewer'];

                if (!in_array($newRole, $allowedRoles)) {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Invalid role.</div>';
                } elseif ($auth->updateUser($userId, ['role' => $newRole])) {
                    $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0;">User role updated successfully.</div>';
                    $users = $auth->getAllUsers(); // Refresh the list
                } else {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Failed to update user role.</div>';
                }
                break;

            case 'delete_user':
                $userId = (int)($_POST['user_id'] ?? 0);

                if ($userId === $currentUser['id']) {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">You cannot delete your own account.</div>';
                } elseif ($auth->deleteUser($userId)) {
                    $message = '<div style="color: green; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; margin: 10px 0;">User deleted successfully.</div>';
                    $users = $auth->getAllUsers(); // Refresh the list
                } else {
                    $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0;">Failed to delete user.</div>';
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users - Rekentool</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-bottom: 30px; }
        .btn {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th { background: #f8f9fa; font-weight: 600; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 500; }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        .status-active { color: #28a745; }
        .status-inactive { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h1>Admin User Management</h1>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="content.php" class="btn btn-secondary">← Content Manager</a>
                <a href="data.php" class="btn btn-secondary">📊 Gegevens</a>
                <a href="../logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php echo $message; ?>

        <button onclick="showModal('createUserModal')" class="btn btn-success">Add New User</button>

        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <span class="status-<?php echo $user['active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never'; ?></td>
                    <td>
                        <button onclick="changePassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="btn">Change Password</button>
                        <button onclick="changeRole(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')" class="btn">Change Role</button>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_user">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="active" value="<?php echo $user['active'] ? '0' : '1'; ?>">
                            <button type="submit" class="btn <?php echo $user['active'] ? 'btn-danger' : 'btn-success'; ?>">
                                <?php echo $user['active'] ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </form>
                        <?php if ($user['id'] !== $currentUser['id']): ?>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="btn btn-danger">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <h2>Add New User</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_user">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name">
                </div>
                <div class="form-group">
                    <label>Role:</label>
                    <select name="role">
                        <option value="admin">Admin (full access)</option>
                        <option value="editor">Editor (view & export data)</option>
                        <option value="viewer">Viewer (view data only)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Create User</button>
                <button type="button" onclick="hideModal('createUserModal')" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <h2>Change Password</h2>
            <form method="post">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="user_id" id="changePasswordUserId">
                <p>Changing password for: <strong id="changePasswordUsername"></strong></p>
                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-success">Change Password</button>
                <button type="button" onclick="hideModal('changePasswordModal')" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <h2>Delete User</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" id="deleteUserId">
                <p>Are you sure you want to delete user: <strong id="deleteUsername"></strong>?</p>
                <p style="color: red;">This action cannot be undone!</p>
                <button type="submit" class="btn btn-danger">Delete User</button>
                <button type="button" onclick="hideModal('deleteUserModal')" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function changePassword(userId, username) {
            document.getElementById('changePasswordUserId').value = userId;
            document.getElementById('changePasswordUsername').textContent = username;
            showModal('changePasswordModal');
        }

        function deleteUser(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUsername').textContent = username;
            showModal('deleteUserModal');
        }

        // Close modal when clicking outside
    <!-- Change Role Modal -->
    <div id="changeRoleModal" class="modal">
        <div class="modal-content">
            <h2>Change User Role</h2>
            <form method="post">
                <input type="hidden" name="action" value="change_role">
                <input type="hidden" name="user_id" id="changeRoleUserId">
                <p>Changing role for: <strong id="changeRoleUsername"></strong></p>
                <div class="form-group">
                    <label>New Role:</label>
                    <select name="role" id="changeRoleSelect">
                        <option value="admin">Admin (full access)</option>
                        <option value="editor">Editor (view & export data)</option>
                        <option value="viewer">Viewer (view data only)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Change Role</button>
                <button type="button" onclick="hideModal('changeRoleModal')" class="btn btn-secondary">Cancel</button>
            </form>
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

        function changeRole(userId, username, currentRole) {
            document.getElementById('changeRoleUserId').value = userId;
            document.getElementById('changeRoleUsername').textContent = username;
            document.getElementById('changeRoleSelect').value = currentRole;
            showModal('changeRoleModal');
        }

        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function changePassword(userId, username) {
            document.getElementById('changePasswordUserId').value = userId;
            document.getElementById('changePasswordUsername').textContent = username;
            showModal('changePasswordModal');
        }

        function deleteUser(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUsername').textContent = username;
            showModal('deleteUserModal');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>