# Troubleshooting & Support Gids

> Handleiding voor het oplossen van veelvoorkomende problemen en fouten.

## ⚠️ 500 Error Diagnostiek

### Wat is een 500 Error?

Een **HTTP 500 Internal Server Error** betekent dat de server een onverwachte fout heeft tegengekomen en de request niet kan afhandelen. Dit kan verschillende oorzaken hebben.

### Hoe Debug je een 500 Error?

#### Stap 1: Error Display Inschakelen

Voeg dit toe aan de bovenkant van `app/bootstrap.php` (ALLEEN VOOR DEVELOPMENT):

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');  // Toon fouten op scherm
ini_set('log_errors', '1');      // Log fouten ook naar bestand
ini_set('error_log', '/tmp/php_errors.log');  // Log locatie
```

⚠️ **WAARSCHUWING**: Niet in productie! Dit geeft veiligheidsrisico's.

#### Stap 2: Check de Debug Pagina

Navigeer naar: `http://localhost/rekentoolbewoner/debug.php`

Dit script toont:
- Database verbindingsstatus
- Tabel aanwezigheid
- Tabel structuur
- Admin user aanwezigheid

#### Stap 3: Error Log Controleren

```bash
# MacOS/Linux
tail -50 /var/log/php_errors.log

# Windows (via PowerShell)
Get-Content "C:\xampp\php\logs\error.log" -Tail 50
```

---

## 🔧 Veelvoorkomende Problemen & Oplossingen

### Probleem 1: Data.php geeft 500 error

**Symptomen**:
- "Internal Server Error" bij navigatie naar Gegevens tab
- Ander content.php pages werken wel
- Login werkt normaal

**Mogelijke Oorzaken**:

#### Oorzaak A: household_data Tabel Bestaat Niet

```php
// Check tabel
$stmt = $pdo->query("SHOW TABLES LIKE 'household_data'");
if ($stmt->rowCount() === 0) {
    die("Tabel household_data bestaat niet!");
}
```

**Oplossing**: Importeer `bewoner.sql`:

```bash
# Via phpMyAdmin: Import → Select bewoner.sql
# Of via MySQL command line:
mysql -u root -p bewoner < bewoner.sql
```

#### Oorzaak B: Ontbrekende Kolommen in household_data

```php
// Controleer kolom structuur
$cols = $pdo->query("DESCRIBE household_data");
$columnNames = array_column($cols->fetchAll(), 'Field');

$requiredColumns = [
    'id', 'postcode', 'huisnummer', 'zonnepanelen',
    'preset', 'verbruik', 'opwek', 'submitted_at'
];

foreach ($requiredColumns as $col) {
    if (!in_array($col, $columnNames)) {
        echo "Kolom '$col' ontbreekt!";
    }
}
```

**Oplossing**: Recreate tabel met correct schema

```sql
DROP TABLE IF EXISTS household_data;
CREATE TABLE `household_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postcode` varchar(10) DEFAULT NULL,
  `huisnummer` varchar(10) DEFAULT NULL,
  `toevoeging` varchar(10) DEFAULT NULL,
  `zonnepanelen` tinyint(1) DEFAULT 0,
  `preset` varchar(10) DEFAULT NULL,
  `verbruik` int(11) DEFAULT NULL,
  `opwek` int(11) DEFAULT NULL,
  `data_source` enum('preset','custom') DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Oorzaak C: Permission Error

```php
// De user kan niet kijken naar gegevens
// Check current user's permissions
$user = $auth->getCurrentUser();
if (!$user) {
    die("Geen ingelogde gebruiker!");
}

echo "Huidige rol: " . $user['role'];
echo "Kan view_data: " . ($auth->canViewData() ? 'Ja' : 'Nee');
```

**Oplossing**: Verander rol naar admin of editor

```sql
UPDATE admin_users SET role = 'admin' WHERE username = 'jouwgebruiker';
```

---

### Probleem 2: Users.php geeft 500 error (SYNTAX ERROR)

**Symptomen**:
- "Internal Server Error" bij Gebruikersbeheer
- Ander admin pages werken

**Dit Is Opgelost**: De syntax error in users.php is al gecorrigeerd (extra break statement verwijderd)

**Controle**:

```php
// PHP syntax check
php -l admin/users.php

// Zou moeten uitvoeren:
// "No syntax errors detected in admin/users.php"
```

**Handmatige Fix** (indien nodig):
Zoek regel ~67-71 in `admin/users.php` en verwijder:
```php
                }
                break;  // ← Dit verwijderen
```

---

### Probleem 3: Login Mislukt

**Symptomen**:
- "Invalid username or password" bericht
- Je weet zeker dat credentials correct zijn

#### Oorzaak A: Account is Vergrendeld

```php
// Check lock status
$stmt = $pdo->prepare("SELECT locked_until FROM admin_users WHERE username = ?");
$stmt->execute(['admin']);
$result = $stmt->fetch();

if ($result['locked_until']) {
    $lockTime = strtotime($result['locked_until']);
    $now = time();
    if ($now < $lockTime) {
        $remaining = $lockTime - $now;
        echo "Account vergrendeld voor nog $remaining seconden";
    }
}
```

**Oplossing**: Wacht 15 minuten of reset handmatig

```sql
UPDATE admin_users 
SET locked_until = NULL, login_attempts = 0 
WHERE username = 'admin';
```

#### Oorzaak B: Default Wachtwoord is Veranderd

**Standaard credentials**:
```
Gebruikersnaam: admin
Wachtwoord: password
```

**Oplossing**: Reset wachtwoord in database

```php
// Genereer nieuwe hash voor "password"
$hash = password_hash('password', PASSWORD_DEFAULT);
echo $hash; // Kopieer dit

// Of gebruik deze directe SQL:
UPDATE admin_users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';
```

#### Oorzaak C: Account is Inactief

```sql
-- Check active status
SELECT id, username, active FROM admin_users WHERE username = 'admin';

-- Activeer account
UPDATE admin_users SET active = 1 WHERE username = 'admin';
```

---

### Probleem 4: "Access Denied" Fout

**Symptomen**:
- Je bent ingelogd
- Maar krijgt "Access denied" bericht op data.php

**Oorzaak**: Rol heeft niet genoeg permissies

| Feature | Viewer | Editor | Admin |
|---------|--------|--------|-------|
| Data bekijken | ✓ | ✓ | ✓ |
| Data exporteren | ✗ | ✓ | ✓ |
| Gebruikers beheren | ✗ | ✗ | ✓ |

**Oplossing**: Verander naar passende rol

```sql
-- Maak admin
UPDATE admin_users SET role = 'admin' WHERE username = 'jouwgebruiker';

-- Maak editor (export rechten)
UPDATE admin_users SET role = 'editor' WHERE username = 'jouwgebruiker';
```

---

### Probleem 5: Data Toont Niet in Tabel

**Symptomen**:
- Data.php laadt correct
- Tabel is leeg
- "Geen gegevens gevonden" bericht

#### Oorzaak A: Geen Data in Database

```php
// Count records
$stmt = $pdo->query("SELECT COUNT(*) FROM household_data");
$count = $stmt->fetchColumn();

if ($count === 0) {
    echo "Geen records in database!";
    echo "Dien formulier in op homepage.";
}
```

**Oplossing**: Voeg test data toe via formulier of SQL

```sql
INSERT INTO household_data 
(postcode, huisnummer, zonnepanelen, verbruik, opwek, data_source) 
VALUES 
('1234AB', '23', 1, 3500, 3000, 'preset');
```

#### Oorzaak B: Search Filter is Te Strict

```php
// Als je zoekt naar "1234" maar alle postcodes zijn "1234AB"
// De LIKE zal niet matchen (moet exact match zijn)

// Check: Verwijder zoekopdracht
?search=
// Zou alle records moeten tonen
```

**Oplossing**: Wissen van search filter

---

### Probleem 6: CSV Export is Leeg

**Symptomen**:
- Export knop werkt
- Bestand downloadt
- Maar bevat alleen headers, geen data

#### Oorzaak A: Geen Export Permissies

```php
// Check permissions
if (!$auth->canExportData()) {
    die('Je kan niet exporteren');
}
```

**Oplossing**: Maak user editor of admin

```sql
UPDATE admin_users SET role = 'editor' WHERE username = 'jouwuser';
```

#### Oorzaak B: Query Retourneert Niets

```php
// Debug query
$stmt = $pdo->query("SELECT * FROM household_data");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)) {
    die("Query retourneert geen data!");
}

var_dump($data); // Zie inhoud
```

**Oplossing**: Voeg data toe aan database

---

### Probleem 7: Wachtwoord Wijzigen Werkt Niet

**Symptomen**:
- "Password changed successfully" bericht
- Maar oude wachtwoord werkt nog steeds

#### Oorzaak A: Cache/Session Issue

```php
// Session kan oud wachtwoord cachen

// Oplossing: Browser cache wissen
// Ctrl+Shift+Delete → Clear browsing data
```

#### Oorzaak B: Database Update Failed Silent

```php
// Voeg error handling toe
try {
    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
    if (!$stmt->execute([$hash, $userId])) {
        echo "Update failed: " . implode(", ", $stmt->errorInfo());
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

**Oplossing**: Check database permissies

```bash
# MySQL
GRANT ALL PRIVILEGES ON bewoner.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📊 Database Checks

### Quick Diagnostics Script

Sla dit op als `check-db.php`:

```php
<?php
require 'app/bootstrap.php';
require 'config/database.php';

$db = new Database($dbConfig);
$pdo = $db->getConnection();

echo "<h1>Database Status Check</h1>";

// 1. Connection
try {
    $pdo->query("SELECT 1");
    echo "<p style='color: green'>✓ Database verbinding OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Verbinding fout: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Tables
foreach (['admin_users', 'household_data', 'page_content'] as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    $exists = $stmt->rowCount() > 0;
    echo "<p style='color: " . ($exists ? "green" : "red") . "'>" . 
         ($exists ? "✓" : "✗") . " Tabel: $table</p>";
}

// 3. Admin Users
$stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
$count = $stmt->fetchColumn();
echo "<p>Admin users: <strong>$count</strong></p>";

// 4. Data Records
$stmt = $pdo->query("SELECT COUNT(*) FROM household_data");
$count = $stmt->fetchColumn();
echo "<p>Huishoudgegevens: <strong>$count</strong></p>";

// 5. Content Sections
$stmt = $pdo->query("SELECT COUNT(*) FROM page_content");
$count = $stmt->fetchColumn();
echo "<p>Content secties: <strong>$count</strong></p>";

echo "<hr>";
echo "<p><a href='debug.php'>← Terug naar debug pagina</a></p>";
?>
```

### Table Structure Viewer

```php
// Voeg dit toe aan debug.php
$tables = ['admin_users', 'household_data'];

foreach ($tables as $table) {
    echo "<h3>Tabel: $table</h3>";
    
    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . $col['Default'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}
?>
```

---

## 🔐 Beveiliging Controleren

### Security Checklist

```
□ Standaard admin wachtwoord veranderd
  - Logg in als admin
  - Ga naar Gebruikersbeheer
  - Wijzig wachtwoord naar sterke password

□ Database credentials beveiligd
  - Check config/database.php
  - Zorg dat .htaccess config files blokkeert
  
□ Error display uitgeschakeld (production)
  - ini_set('display_errors', '0') in bootstrap.php
  - Error logging ingeschakeld
  
□ Session timeout geconfigureerd
  - Default: 1 uur (3600 seconden)
  - In Auth.php: SESSION_TIMEOUT constant
  
□ HTTPS ingeschakeld (production)
  - Certificate geïnstalleerd
  - Redirect http → https in .htaccess
  
□ File permissions correct
  - config/*.php: 600 (alleen eigenaar)
  - app/*.php: 644 (eigenaar lezen/schrijven)
  - uploads/: 755 (writable)
```

---

## 📞 Support Resources

### Debug Files

- `debug.php` - Volledige diagnostiek pagina
- `check-db.php` - Database status controle
- `config/database.php` - Database configuratie

### Log Files

- PHP Error Log: `{php-ini-error_log}`
- Apache Error Log: `{apache-dir}/logs/error.log`
- Application Log: `{app-dir}/logs/` (indien geconfigureerd)

### Documentatie

- `TECHNISCHE_DOCUMENTATIE.md` - Volledige technische gids
- `FRONTEND_DOCUMENTATIE.md` - Frontend components gids
- `DATA_MANAGEMENT.md` - Data management features
- `DEVELOPER_REFERENCE.md` - Developer quick reference

### Contactpunten

Voor verdere hulp:
1. Check de relevante documentatie
2. Run debug scripts (debug.php, check-db.php)
3. Check server error logs
4. Verificeer database integriteit

---

## ✅ Zelfherstel Gids

### 1. Volledig Reset van Admin Access

```bash
# 1. Database backup
mysqldump -u root -p bewoner > backup.sql

# 2. Verwijder en hermaak admin_users tabel
mysql -u root -p bewoner < admin_users.sql

# 3. Login met standaard credentials
# Username: admin
# Password: password

# 4. Wijzig wachtwoord in UI
```

### 2. Database Volledig Resetten

```bash
# 1. Dropt database
mysql -u root -p -e "DROP DATABASE IF EXISTS bewoner;"

# 2. Maak opnieuw aan
mysql -u root -p bewoner < bewoner.sql

# 3. Verifieer met check-db.php
```

### 3. Permission Volledige Reset

```sql
-- Zet alle users op admin
UPDATE admin_users SET role = 'admin';

-- Activeer alle users
UPDATE admin_users SET active = 1;

-- Reset lockouts
UPDATE admin_users SET locked_until = NULL, login_attempts = 0;
```

---

**Versie**: 1.0  
**Laatst bijgewerkt**: April 2026  
**Auteur**: Avery Vermaas