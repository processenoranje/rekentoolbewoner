<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$config = require BASE_PATH . '/config/config.php';
$dbConfig = require BASE_PATH . '/config/database.php';

$db = new Database($dbConfig);
$auth = new Auth($db->getConnection());
$auth->redirectIfLoggedIn();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0; border-radius: 3px;">Vul alle velden in.</div>';
    } else {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            header('Location: admin/content.php');
            exit;
        } else {
            $message = '<div style="color: red; padding: 10px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 10px 0; border-radius: 3px;">' . htmlspecialchars($result['message']) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Rekentool</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #ff7716;
            padding-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #ff7716;
            box-shadow: 0 0 0 3px rgba(255, 119, 22, 0.1);
        }
        button {
            width: 100%;
            background: #ff7716;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        button:hover {
            background: #e56c00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 119, 22, 0.3);
        }
        .message {
            margin-bottom: 20px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #ff7716;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Gebruikersnaam:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Wachtwoord:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit">Inloggen</button>
        </form>

        <div class="back-link">
            <a href="rekentoolbewoner.html">← Terug naar Rekentool</a>
        </div>
    </div>
</body>
</html>