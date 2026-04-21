<?php
declare(strict_types=1);

class Auth {
    private const SESSION_KEY = 'admin_logged_in';
    private const SESSION_USER = 'admin_user';
    private const SESSION_USER_ID = 'admin_user_id';
    private const SESSION_TIME = 'admin_login_time';
    private const SESSION_TIMEOUT = 3600; // 1 hour
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(string $username, string $password): array {
        // Check if account is locked
        if ($this->isAccountLocked($username)) {
            return ['success' => false, 'message' => 'Account is temporarily locked due to too many failed login attempts.'];
        }

        // Get user from database
        $user = $this->getUserByUsername($username);
        if (!$user) {
            $this->recordFailedLogin($username);
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->recordFailedLogin($username);
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Check if user is active
        if (!$user['active']) {
            return ['success' => false, 'message' => 'Account is disabled.'];
        }

        // Reset login attempts on successful login
        $this->resetLoginAttempts($username);

        // Update last login
        $this->updateLastLogin($user['id']);

        // Set session variables
        $_SESSION[self::SESSION_KEY] = true;
        $_SESSION[self::SESSION_USER] = $user['username'];
        $_SESSION[self::SESSION_USER_ID] = $user['id'];
        $_SESSION[self::SESSION_TIME] = time();

        return ['success' => true, 'message' => 'Login successful.'];
    }

    public function logout(): void {
        session_destroy();
    }

    public function isLoggedIn(): bool {
        if (!isset($_SESSION[self::SESSION_KEY]) || $_SESSION[self::SESSION_KEY] !== true) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION[self::SESSION_TIME]) &&
            (time() - $_SESSION[self::SESSION_TIME]) > self::SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        return true;
    }

    public function getCurrentUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_USER_ID] ?? null;
        if (!$userId) {
            return null;
        }

        return $this->getUserById($userId);
    }

    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
    }

    public function redirectIfLoggedIn(): void {
        if ($this->isLoggedIn()) {
            header('Location: admin/content.php');
            exit;
        }
    }

    private function getUserByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function getUserById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, username, email, full_name, role, active, created_at, last_login FROM admin_users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function isAccountLocked(string $username): bool {
        $stmt = $this->db->prepare("SELECT locked_until FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || !$result['locked_until']) {
            return false;
        }

        $lockedUntil = strtotime($result['locked_until']);
        if (time() < $lockedUntil) {
            return true;
        }

        // Lock has expired, reset it
        $this->resetLoginAttempts($username);
        return false;
    }

    private function recordFailedLogin(string $username): void {
        $stmt = $this->db->prepare("
            UPDATE admin_users
            SET login_attempts = login_attempts + 1,
                locked_until = CASE
                    WHEN login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                    ELSE NULL
                END
            WHERE username = ?
        ");
        $stmt->execute([self::MAX_LOGIN_ATTEMPTS, self::LOCKOUT_TIME, $username]);
    }

    private function resetLoginAttempts(string $username): void {
        $stmt = $this->db->prepare("UPDATE admin_users SET login_attempts = 0, locked_until = NULL WHERE username = ?");
        $stmt->execute([$username]);
    }

    private function updateLastLogin(int $userId): void {
        $stmt = $this->db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    // Admin user management methods
    public function createUser(string $username, string $password, string $email = null, string $fullName = null, string $role = 'admin'): bool {
        // Check if user already exists
        if ($this->getUserByUsername($username)) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            INSERT INTO admin_users (username, password_hash, email, full_name, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$username, $passwordHash, $email, $fullName, $role]);
    }

    public function updateUser(int $userId, array $data): bool {
        $allowedFields = ['username', 'email', 'full_name', 'role', 'active'];
        $updates = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $params[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $params[] = $userId;
        $stmt = $this->db->prepare("UPDATE admin_users SET " . implode(', ', $updates) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function changePassword(int $userId, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $userId]);
    }

    public function deleteUser(int $userId): bool {
        // Prevent deleting the current user
        if ($this->isLoggedIn() && $_SESSION[self::SESSION_USER_ID] == $userId) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM admin_users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function getAllUsers(): array {
        $stmt = $this->db->query("
            SELECT id, username, email, full_name, role, active, created_at, last_login, login_attempts
            FROM admin_users
            ORDER BY username
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}