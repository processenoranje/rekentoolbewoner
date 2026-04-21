# Developer Quick Reference - Data Management System

## File Locations

### Core Admin Files
```
/admin/
  ├── content.php      - Content management interface
  ├── data.php         - Data management & export (NEW)
  └── users.php        - User management interface
```

### Core Application Files
```
/app/
  ├── Auth.php         - Authentication & permissions (UPDATED)
  ├── Database.php     - Database connection & queries (UPDATED)
  ├── ContentManager.php - Content management logic
  ├── FormHandler.php  - Form submission logic
  ├── Mailer.php       - Email functionality
  └── bootstrap.php    - Application initialization
```

### Documentation
```
/
  ├── DATA_MANAGEMENT.md          - Complete feature documentation
  ├── DATA_MANAGEMENT_SUMMARY.md  - Implementation overview
  ├── SETUP_DATA_MANAGEMENT.md    - Quick setup guide
  └── ADMIN_AUTH_UPGRADE.md       - Authentication system docs
```

---

## Key Functions for Developers

### In `Auth.php`

```php
// Check if user has permission
$auth->hasPermission('view_data');        // true/false
$auth->hasPermission('export_data');      // true/false
$auth->hasPermission('manage_users');     // true/false

// Convenience methods
$auth->canViewData();                     // Shortcut
$auth->canExportData();                   // Shortcut
$auth->canManageUsers();                  // Shortcut

// Require permission (exits if denied)
$auth->requirePermission('export_data');

// Get current user info
$user = $auth->getCurrentUser();
echo $user['role'];                       // 'admin', 'editor', 'viewer'
echo $user['username'];
echo $user['email'];
echo $user['full_name'];
```

### In `Database.php`

```php
// New method to get PDO connection
$pdo = $db->getConnection();

// Standard query methods still available
$db->insertFormData($data);
```

---

## Adding New Permissions

To add a new permission:

1. **Update `Auth.php`** - Add to `$rolePermissions` array:
```php
private array $rolePermissions = [
    'admin' => ['view_data', 'export_data', 'manage_users', 'new_permission'],
    'editor' => ['view_data', 'export_data'],
    'viewer' => ['view_data'],
];
```

2. **Update database** - Add new role enum value if needed:
```sql
ALTER TABLE admin_users MODIFY COLUMN role 
    enum('admin','editor','viewer','new_role') NOT NULL DEFAULT 'admin';
```

3. **Use in code**:
```php
$auth->requirePermission('new_permission');
// or
if ($auth->hasPermission('new_permission')) {
    // Allow action
}
```

---

## Database Schema

### admin_users Table
```sql
- id (int) - Primary key
- username (varchar 50) - Unique identifier
- password_hash (varchar 255) - Bcrypt hash
- email (varchar 100) - Optional
- full_name (varchar 100) - Optional
- role (enum: admin, editor, viewer) - User role
- active (tinyint) - 1 = active, 0 = inactive
- created_at (timestamp) - Account creation
- last_login (timestamp) - Last login time
- login_attempts (int) - Failed attempts counter
- locked_until (timestamp) - Account lockout end time
```

### household_data Table
```sql
- id (int) - Primary key
- postcode (varchar 10)
- huisnummer (varchar 10)
- toevoeging (varchar 10)
- zonnepanelen (tinyint) - 1 = yes, 0 = no
- preset (varchar 10) - Preset package selection
- verbruik (int) - Annual consumption kWh
- opwek (int) - Annual production kWh
- data_source (enum: preset, custom)
- submitted_at (timestamp) - Submission time
```

---

## CSV Export Format

The export uses semicolon delimiter for Excel compatibility:

```
id;postcode;huisnummer;toevoeging;zonnepanelen;preset;verbruik;opwek;data_source;submitted_at
1;1234AB;23;;1;2;3500;3000;preset;2026-04-14 07:37:17
2;5678CD;15;a;0;;3200;0;custom;2026-04-15 10:22:30
```

**Key points:**
- First line: BOM (Byte Order Mark) for UTF-8
- Delimiter: Semicolon (`;`) not comma
- Encoding: UTF-8
- Timestamp format: MySQL format (YYYY-MM-DD HH:MM:SS)

---

## Role Mapping

| Role | Permissions | Use Case |
|------|-------------|----------|
| `admin` | all | System administrators, full control |
| `editor` | view_data, export_data | Team members needing data export |
| `viewer` | view_data | Team members needing read-only access |

---

## Common Tasks for Developers

### Grant data export permission to a user
```php
$auth->updateUser($userId, ['role' => 'editor']);
```

### Check if user can perform action before showing button
```php
<?php if ($auth->canExportData()): ?>
    <a href="?export=1" class="btn">Export CSV</a>
<?php endif; ?>
```

### Require permission before processing form
```php
$auth->requirePermission('export_data');
// If user doesn't have permission, dies with 403 error

// Code below only runs if permission granted
$csv = generateCsv();
```

### Get current user's role for conditional logic
```php
$user = $auth->getCurrentUser();
if ($user['role'] === 'admin') {
    // Show admin-only features
}
```

### Add new role to system
```php
// 1. Update database
ALTER TABLE admin_users MODIFY COLUMN role 
    enum('admin','editor','viewer','newrole') NOT NULL DEFAULT 'admin';

// 2. Update Auth.php
private array $rolePermissions = [
    'admin' => ['view_data', 'export_data', 'manage_users'],
    'editor' => ['view_data', 'export_data'],
    'viewer' => ['view_data'],
    'newrole' => ['specific_permission'],  // Add here
];

// 3. Use in code
$auth->requirePermission('specific_permission');
```

---

## Query Patterns

### Retrieve all users with specific role
```php
$pdo = $db->getConnection();
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE role = ?");
$stmt->execute(['editor']);
$editors = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Get household data with search
```php
$pdo = $db->getConnection();
$stmt = $pdo->prepare("
    SELECT * FROM household_data 
    WHERE postcode LIKE ? OR huisnummer LIKE ?
    ORDER BY submitted_at DESC
");
$stmt->execute(['%'.$search.'%', '%'.$search.'%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Count submissions with solar panels
```php
$pdo = $db->getConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM household_data WHERE zonnepanelen = 1");
$count = $stmt->fetchColumn();
```

---

## Error Handling

### Permission denied
```php
// In page that requires permission:
if (!$auth->canViewData()) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied.');
}
```

### Database errors
```php
try {
    // Database operation
} catch (Exception $e) {
    error_log($e->getMessage());
    die('Database error occurred.');
}
```

### Invalid role assignment
```php
$allowedRoles = ['admin', 'editor', 'viewer'];
if (!in_array($newRole, $allowedRoles)) {
    throw new InvalidArgumentException('Invalid role');
}
```

---

## Performance Considerations

### Pagination improves performance
- Default: 25 results per page
- Reduce database memory usage
- Faster page loads with large datasets
- Adjustable via `$filter['per_page']` in data.php

### Database indexes
Key indexes for performance:
- `admin_users.username` (UNIQUE)
- `admin_users.active`
- `household_data.submitted_at`
- `household_data.postcode`
- `household_data.zonnepanelen`

### CSV generation
- Streams directly to browser (not buffered)
- Handles large datasets efficiently
- Memory-efficient output

---

## Testing Tips

### Test permission denied
```php
$db = new Database($dbConfig);
$auth = new Auth($db->getConnection());

// Temporarily set role to 'viewer'
$_SESSION['admin_user_role'] = 'viewer';

// Should deny permission
$auth->requirePermission('export_data'); // Dies with 403
```

### Test CSV export
```bash
# Download and check format
curl 'http://localhost/admin/data.php?export=1' > test.csv
head -c 3 test.csv  # Should show BOM bytes (EF BB BF)
```

### Verify database integrity
```sql
-- Check for locked accounts
SELECT * FROM admin_users WHERE locked_until > NOW();

-- Check for inactive users
SELECT * FROM admin_users WHERE active = 0;

-- Check recent submissions
SELECT * FROM household_data ORDER BY submitted_at DESC LIMIT 10;
```

---

## Deployment Checklist

- [ ] Update `admin_users.sql` schema if upgrading
- [ ] Test with development data
- [ ] Create test Editor and Viewer accounts
- [ ] Verify CSV export in Excel
- [ ] Test permission denied scenarios
- [ ] Clear browser cache/sessions
- [ ] Verify file permissions (data.php, etc.)
- [ ] Check database backups working
- [ ] Document admin procedures
- [ ] Train admins on new system

---

## References

- `DATA_MANAGEMENT.md` - Full feature documentation
- `DATA_MANAGEMENT_SUMMARY.md` - Implementation overview
- `SETUP_DATA_MANAGEMENT.md` - Setup guide
- `ADMIN_AUTH_UPGRADE.md` - Authentication docs
- `app/Auth.php` - Permission checking implementation
- `admin/data.php` - Data management interface