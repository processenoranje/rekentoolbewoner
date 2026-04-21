# Technische Documentatie Rekentool Bewoner

## Inhoudsopgave

1. [Verificatie & Beveiliging](#verificatie--beveiliging)
2. [Datalaag](#datalaag)
3. [Admin Interfaces](#admin-interfaces)
4. [Frontend Componenten](#frontend-componenten)
5. [CSS Grid & Layout](#css-grid--layout)
6. [Foutafhandeling](#foutafhandeling)
7. [Rolgebaseerde Toegangscontrole](#rolgebaseerde-toegangscontrole)

---

## Verificatie & Beveiliging

### Authenticatie Proces

De authenticatie wordt beheerd door de `Auth` klasse in `app/Auth.php`.

#### Login Stroom
```
Gebruiker voert credentials in
         ↓
Controle op account vergrendeling (5 mislukte pogingen = 15 min lockout)
         ↓
Opzoeken van gebruiker in database
         ↓
Wachtwoord verificatie met bcrypt
         ↓
Controle of account actief is
         ↓
Zet session variabelen
         ↓
Update last_login timestamp
         ↓
Login succesvol
```

#### Session Management

```php
// Session constants
SESSION_KEY = 'admin_logged_in'        // Boolean flag
SESSION_USER = 'admin_user'            // Username
SESSION_USER_ID = 'admin_user_id'      // User ID
SESSION_TIME = 'admin_login_time'      // Timestamp

// Session timeout: 1 uur (3600 seconden)
// Na timeout: automatische logout bij volgende request
```

#### Beveiliging tegen Brute Force

- **Max pogingen**: 5 mislukte login-pogingen
- **Lockout duur**: 15 minuten na 5e mislukte poging
- **Reset**: Successful login reset de poging-teller
- **Database tracking**: `login_attempts` en `locked_until` kolommen

#### Wachtwoord Hashing

```php
// Wachtwoord hashing met bcrypt (PASSWORD_DEFAULT = bcrypt)
password_hash($password, PASSWORD_DEFAULT)

// Verificatie
password_verify($inputPassword, $hashFromDatabase)

// Kosten factor: 10 (standaard, automatisch bepaald door PASSWORD_DEFAULT)
// Resultaat: ~60 characters lange hash
```

---

## Datalaag

### Database Verbinding

Bestand: `app/Database.php`

```php
class Database {
    private PDO $pdo;
    
    public function __construct(array $config) {
        // Bouwt DSN (Data Source Name)
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        
        // Maakt PDO verbinding met error handling
        $this->pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    
    // Geeft PDO-instance terug voor Auth klasse
    public function getConnection(): PDO {
        return $this->pdo;
    }
}
```

**Configuratie**: `config/database.php`
```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'bewoner',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
```

### Tabel Schema's

#### admin_users Tabel

```sql
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,          -- Gebruikersnaam (uniek)
  `password_hash` varchar(255) NOT NULL,           -- Bcrypt hash
  `email` varchar(100) DEFAULT NULL,               -- Optioneel email
  `full_name` varchar(100) DEFAULT NULL,           -- Optioneel volledige naam
  `role` enum('admin','editor','viewer') NOT NULL  -- Rol: admin/editor/viewer
            DEFAULT 'admin',
  `active` tinyint(1) NOT NULL DEFAULT 1,          -- 1=actief, 0=inactief
  `created_at` timestamp NOT NULL 
            DEFAULT current_timestamp(),            -- Account aangemaakt
  `last_login` timestamp NULL DEFAULT NULL,        -- Laatste login moment
  `login_attempts` int(11) NOT NULL DEFAULT 0,     -- Mislukte pogingen teller
  `locked_until` timestamp NULL DEFAULT NULL,      -- Account lockout eindtijd
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Index verklaring**:
- `PRIMARY KEY (id)` - Primaire sleutel, auto_increment
- `UNIQUE KEY (username)` - Garandeert unieke gebruikersnamen
- `KEY (active)` - Versnelt queries op actieve status

#### household_data Tabel

```sql
CREATE TABLE `household_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postcode` varchar(10) DEFAULT NULL,             -- Huispostcode
  `huisnummer` varchar(10) DEFAULT NULL,           -- Huisnummer
  `toevoeging` varchar(10) DEFAULT NULL,           -- Toevoeging (a, b, etc.)
  `zonnepanelen` tinyint(1) DEFAULT 0,             -- 1=ja, 0=nee
  `preset` varchar(10) DEFAULT NULL,               -- Preset pakket (1-5)
  `verbruik` int(11) DEFAULT NULL,                 -- Jaarlijkse verbruik kWh
  `opwek` int(11) DEFAULT NULL,                    -- Jaarlijkse opwekking kWh
  `data_source` enum('preset','custom') DEFAULT NULL, -- Bron: preset/custom
  `submitted_at` timestamp NOT NULL 
            DEFAULT current_timestamp(),            -- Indiening moment
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Admin Interfaces

### 1. Gegevens Interface (admin/data.php)

**Doel**: Bekijk, zoek, filter, sorteer en exporteer huishoudgegevens

#### Zoek & Filter Functionaliteit

```php
// Bouwt WHERE clausule op basis van zoekinvoer
$where = [];
$params = [];

if (!empty($filter['search'])) {
    $searchTerm = '%' . $filter['search'] . '%';  // Wildcard voor LIKE
    $where[] = "(postcode LIKE ? OR huisnummer LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Construeert query
$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Voorbeeld zoekopdracht:
// Zoek "1234AB" → Vindt alle ingediende gegevens met postcode 1234AB
```

**Ondersteunde zoekvelden**:
- Postcode (exact + fuzzy match)
- Huisnummer (exact + fuzzy match)

#### Sortering

```php
// Toegestane sorteervelden (whitelist voor veiligheid)
$allowedSorts = [
    'id', 'postcode', 'huisnummer', 'zonnepanelen',
    'verbruik', 'opwek', 'submitted_at'
];

// Sanitize sorteerveld (default: submitted_at)
$sortField = in_array($filter['sort'], $allowedSorts) 
    ? $filter['sort'] 
    : 'submitted_at';

// Sorteerrichting
$orderDir = $filter['order'] === 'ASC' ? 'ASC' : 'DESC';

// SQL query met sorting
$query = "SELECT * FROM household_data 
          $whereClause 
          ORDER BY $sortField $orderDir 
          LIMIT ?, ?";
```

**Sorteermogelijkheden** (klikbare kolom headers):
- ID (oplopend/aflopend)
- Postcode
- Huisnummer
- Zonnepanelen (ja/nee)
- Verbruik (kWh)
- Opwekking (kWh)
- Indiend op (datum/tijd)

#### Paginering

```php
$filter = [
    'page' => (int)($_GET['page'] ?? 1),
    'per_page' => 25,  // 25 resultaten per pagina
];

// Bereken totaal pagina's
$totalPages = ceil($total / $filter['per_page']);

// Valideer huidige pagina
$filter['page'] = max(1, min($filter['page'], $totalPages ?: 1));

// Bereken offset voor LIMIT
$offset = ($filter['page'] - 1) * $filter['per_page'];

// Query met LIMIT
$query = "SELECT * FROM household_data 
          $whereClause 
          ORDER BY $sortField $orderDir 
          LIMIT ?, ?";
$params[] = $offset;
$params[] = $filter['per_page'];
```

**Pagineringlinks**:
- « Eerste (naar pagina 1)
- ‹ Vorige (naar vorige pagina)
- [1] [2] [3] ... (paginanummers, max 5 zichtbaar)
- Volgende › (naar volgende pagina)
- Laatste » (naar laatste pagina)

#### CSV Export

```php
// Controle exportrechten
if ($_GET['export'] ?? false) {
    $auth->requirePermission('export_data');
    
    // Haal alle gegevens op
    $stmt = $pdo->query("SELECT * FROM household_data ORDER BY submitted_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // HTTP headers voor download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="household_data_' . 
           date('Y-m-d_H-i-s') . '.csv"');
    
    // CSV writer
    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM voor Excel compatibiliteit
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Schrijf headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]), ';');  // ; delimiter
        
        // Schrijf data rows
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }
    
    fclose($output);
    exit;
}
```

**CSV Format Details**:
- **Scheidingsteken**: `;` (puntkomma) voor Excel compatibiliteit
- **Encoding**: UTF-8 met BOM (Byte Order Mark)
- **Bestandsnaam**: `household_data_YYYY-MM-DD_HH-MM-SS.csv`
- **Headers**: Kolommen van household_data tabel
- **Gegevens**: Alle rijen, chronologisch aflopend

#### Delete Functionaliteit

```php
// POST request met delete actie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    isset($_POST['action']) && 
    $_POST['action'] === 'delete') {
    
    $id = (int)$_POST['id'];
    
    try {
        // Verwijder met prepared statement (SQL injection safe)
        $stmt = $pdo->prepare("DELETE FROM household_data WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Success bericht
            $message = '<div class="success">✓ Entry verwijderd!</div>';
        }
    } catch (Exception $e) {
        // Error bericht
        $message = '<div class="error">✗ Fout: ' . 
                   htmlspecialchars($e->getMessage()) . '</div>';
    }
}
```

**Delete Workflow**:
1. Gebruiker klikt "Verwijderen" knop
2. Modal dialoog bevestigt actie
3. POST verzoek met verwijderings-ID
4. Database verwijdert record
5. Pagina vernieuwt met nieuw totaal

#### Analytics Dashboard

```php
// Statistieken queries
$stats = [];

// Totale indieningen
$stmt = $pdo->query("SELECT COUNT(*) FROM household_data");
$stats['total'] = $stmt->fetchColumn();

// Met zonnepanelen
$stmt = $pdo->query("SELECT COUNT(*) FROM household_data WHERE zonnepanelen = 1");
$stats['solar'] = $stmt->fetchColumn();

// Gemiddeld verbruik
$stmt = $pdo->query("SELECT AVG(verbruik) FROM household_data WHERE verbruik > 0");
$stats['avg_consumption'] = round($stmt->fetchColumn(), 0);

// Gemiddelde opwekking
$stmt = $pdo->query("SELECT AVG(opwek) FROM household_data WHERE opwek > 0");
$stats['avg_production'] = round($stmt->fetchColumn(), 0);
```

**Weergegeven Statistieken**:
- Totale indieningen
- Aantal installaties met zonnepanelen
- Gemiddelde jaarlijkse verbruik (kWh)
- Gemiddelde jaarlijkse opwekking (kWh)

---

### 2. Gebruikersbeheer Interface (admin/users.php)

**Doel**: Beheer admin gebruikers, rollen en wachtwoorden

#### Gebruiker Aanmaken

```php
case 'create_user':
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    
    // Validatie
    if (empty($username) || empty($password)) {
        $message = '<div class="error">Gebruikersnaam en wachtwoord verplicht.</div>';
    } elseif ($auth->createUser($username, $password, $email, $fullName, $role)) {
        $message = '<div class="success">Gebruiker succesvol aangemaakt.</div>';
        $users = $auth->getAllUsers();  // Vernieuw lijst
    } else {
        $message = '<div class="error">Gebruikersnaam bestaat mogelijk al.</div>';
    }
    break;
```

**Vereisten**:
- Gebruikersnaam: Vereist, minimum 3 chars, moet uniek zijn
- Wachtwoord: Vereist, minimum 6 chars
- E-mail: Optioneel, voor notificaties
- Volledige naam: Optioneel, voor weergave
- Rol: admin (default) | editor | viewer

#### Wachtwoord Wijzigen

```php
case 'change_password':
    $userId = (int)($_POST['user_id'] ?? 0);
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($newPassword)) {
        $message = '<div class="error">Nieuw wachtwoord verplicht.</div>';
    } elseif ($auth->changePassword($userId, $newPassword)) {
        $message = '<div class="success">Wachtwoord gewijzigd.</div>';
    } else {
        $message = '<div class="error">Kan wachtwoord niet wijzigen.</div>';
    }
    break;
```

**Wachtwoord Wijziging Details**:
- Oude wachtwoord verificatie: Niet vereist voor admin
- Nieuwe wachtwoord hashing: Automatisch met bcrypt
- Session invalidering: Gebruiker moet opnieuw inloggen

#### Rol Wijzigen

```php
case 'change_role':
    $userId = (int)($_POST['user_id'] ?? 0);
    $newRole = $_POST['role'] ?? 'viewer';
    $allowedRoles = ['admin', 'editor', 'viewer'];
    
    // Valideer rol
    if (!in_array($newRole, $allowedRoles)) {
        $message = '<div class="error">Ongeldige rol.</div>';
    } elseif ($auth->updateUser($userId, ['role' => $newRole])) {
        $message = '<div class="success">Rol gewijzigd.</div>';
        $users = $auth->getAllUsers();
    } else {
        $message = '<div class="error">Kan rol niet wijzigen.</div>';
    }
    break;
```

**Beschikbare Rollen**:
- **admin**: Volledige toegang (alle features)
- **editor**: Gegevens view + export
- **viewer**: Gegevens view-only

#### Gebruiker Deactiveren

```php
case 'toggle_user':
    $userId = (int)($_POST['user_id'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;
    
    if ($auth->updateUser($userId, ['active' => $active])) {
        $message = '<div class="success">Gebruiker status gewijzigd.</div>';
        $users = $auth->getAllUsers();
    }
    break;
```

**Deactivatie Details**:
- Inactieve gebruikers kunnen niet inloggen
- Account kan opnieuw geactiveerd worden
- Gegevens blijven behouden
- Bruikbaar voor tijdelijke toegang

#### Gebruiker Verwijderen

```php
case 'delete_user':
    $userId = (int)($_POST['user_id'] ?? 0);
    
    // Zelf-verwijdering voorkomen
    if ($userId === $currentUser['id']) {
        $message = '<div class="error">Je kan je eigen account niet verwijderen.</div>';
    } elseif ($auth->deleteUser($userId)) {
        $message = '<div class="success">Gebruiker verwijderd.</div>';
        $users = $auth->getAllUsers();
    } else {
        $message = '<div class="error">Kan gebruiker niet verwijderen.</div>';
    }
    break;
```

**Verwijdering Details**:
- Permanente verwijdering van account
- Gegevens kunnen niet hersteld worden
- Zelf-verwijdering is niet mogelijk
- Only admins kunnen gebruikers verwijderen

---

### 3. Content Management (admin/content.php)

**Doel**: Beheer pagina-inhoud en secties

#### Content Laad Systeem

```php
// ContentManager haalt content uit database
$contentManager = new ContentManager($pdo);

// Laad specifieke content
$omschrijving = $contentManager->getContent('omschrijving');

// Laad met default fallback
$content = $contentManager->getContent('section_key') ?? 'Standaard tekst';
```

#### Content Bewerk Workflow

```php
// 1. Check admin permissies
$auth->requireLogin();

// 2. Handle POST updates
if ($_POST['action'] === 'update_content') {
    $sectionKey = $_POST['section_key'] ?? '';
    $content = $_POST['content'] ?? '';
    
    $contentManager->updateContent($sectionKey, $content);
}

// 3. Laad huidige content
$content = $contentManager->getContent('section_key');

// 4. Display in form
echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
```

---

## Frontend Componenten

### Modal Dialoog System

#### Delete Confirmation Modal

```php
<!-- HTML Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Entry verwijderen</div>
        <p>Weet je zeker? Dit kan niet ongedaan gemaakt worden.</p>
        <div class="modal-buttons">
            <button onclick="hideDeleteModal()" class="btn btn-secondary">
                Annuleren
            </button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="btn btn-danger">Verwijderen</button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript Functions -->
<script>
function showDeleteModal(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Sluit modal bij klik buiten
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>
```

**Modal States**:
- `display: none` - Verborgen (standaard)
- `display: block` - Zichtbaar

#### Change Role Modal

```php
<!-- Role Change Modal -->
<div id="roleChangeModal_<?php echo $user['id']; ?>" class="modal">
    <div class="modal-content">
        <div class="modal-header">Rol wijzigen</div>
        <form method="POST">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-group">
                <label>Nieuwe Rol:</label>
                <select name="role" required>
                    <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>
                        Viewer (View Only)
                    </option>
                    <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>
                        Editor (View + Export)
                    </option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                        Admin (Full Access)
                    </option>
                </select>
            </div>
            
            <div class="modal-buttons">
                <button type="button" onclick="hideRoleModal(<?php echo $user['id']; ?>)" 
                        class="btn btn-secondary">
                    Annuleren
                </button>
                <button type="submit" class="btn btn-primary">
                    Wijzigen
                </button>
            </div>
        </form>
    </div>
</div>
```

---

## CSS Grid & Layout

### Container Layout

```css
/* Hoofd container */
.container {
    max-width: 1200px;           /* Max breedte */
    margin: 0 auto;              /* Gecentreerd */
    background: white;           /* Witte achtergrond */
    padding: 20px;               /* Interne ruimte */
    border-radius: 8px;          /* Afgeronde hoeken */
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);  /* Schaduw effect */
}

/* Header Grid - Flexbox voor responsiviteit */
.container > div:first-child {
    display: flex;               /* Flex layout */
    justify-content: space-between;  /* Space tussen items */
    align-items: center;         /* Verticaal gecentreerd */
    margin-bottom: 20px;         /* Ruimte beneden */
    flex-wrap: wrap;             /* Wrap op mobiel */
    gap: 10px;                   /* Ruimte tussen items */
}
```

### Tabel Layout

```css
table {
    width: 100%;                 /* Volledige breedte */
    border-collapse: collapse;   /* Geen dubbele borders */
    margin-bottom: 20px;
}

th {
    background: #f8f9fa;         /* Licht grijze achtergrond */
    padding: 12px;               /* Interne ruimte */
    text-align: left;            /* Links uitgelijnd */
    font-weight: 600;            /* Dikgedrukt */
    border-bottom: 2px solid #ddd;  /* Dunne lijn */
    font-size: 13px;
    cursor: pointer;             /* Wijzer cursor (sorteerbaar) */
    user-select: none;           /* Tekst kan niet geselecteerd worden */
}

th:hover {
    background: #e9ecef;         /* Lichtere achtergrond bij hover */
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;  /* Subtiele scheiding */
    font-size: 13px;
}

tr:hover {
    background: #f9f9f9;         /* Highlight rij */
}
```

### Search Box Layout

```css
.search-container {
    padding: 15px;
    background: #f8f9fa;         /* Licht grijs */
    border-radius: 4px;
    margin-bottom: 20px;
    display: grid;               /* Grid layout */
    grid-template-columns: 1fr auto;  /* 2 kolommen: 1 automatisch breed, 1 automatisch smal */
    gap: 15px;
    align-items: end;            /* Onderaan uitlijnen */
}

.search-box {
    width: 100%;                 /* Vol breedte in grid */
}

.search-box input {
    width: 100%;
    padding: 8px 12px;           /* Verticaal/horizontaal padding */
    border: 1px solid #ddd;      /* Dunne border */
    border-radius: 4px;
    font-size: 13px;
}
```

### Buttons Layout

```css
/* Actions cel met flex layout */
.actions-cell {
    display: flex;               /* Flex voor inline buttons */
    gap: 5px;                    /* Ruimte tussen buttons */
}

.action-btn {
    padding: 4px 8px;
    font-size: 12px;
    border: none;
    cursor: pointer;
    border-radius: 3px;
    text-decoration: none;       /* Verwijder link underline */
}

.action-delete {
    background: #dc3545;         /* Rood */
    color: white;
}

.action-delete:hover {
    background: #c82333;         /* Donkerder rood */
}

.action-edit {
    background: #007bff;         /* Blauw */
    color: white;
}

.action-edit:hover {
    background: #0056b3;         /* Donkerder blauw */
}
```

### Pagination Layout

```css
.pagination {
    display: flex;               /* Flex layout */
    gap: 5px;                    /* Ruimte tussen links */
    justify-content: center;     /* Gecentreerd */
    margin-top: 20px;
    flex-wrap: wrap;             /* Wrap op mobiel */
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;      /* Grijs border */
    border-radius: 4px;
    text-decoration: none;
    color: #007bff;              /* Blauw */
    font-size: 13px;
}

.pagination a:hover {
    background: #e9ecef;         /* Hover effect */
}

.pagination .current {
    background: #007bff;         /* Blauw */
    color: white;
    border-color: #007bff;
}

.pagination .disabled {
    color: #ccc;                 /* Grijs */
    cursor: not-allowed;
    opacity: 0.5;
}
```

### Modal Dialog Layout

```css
.modal {
    display: none;               /* Verborgen standaard */
    position: fixed;             /* Over alles heen */
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);  /* Semi-transparent zwart */
    z-index: 1000;               /* Bovenop alles */
    justify-content: center;
    align-items: center;
}

.modal.show {
    display: flex;               /* Zichtbaar */
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;            /* Max breedte */
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);  /* Diepte effect */
}

.modal-header {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
}

.modal-buttons {
    display: flex;               /* Flex voor button layout */
    gap: 10px;
    justify-content: flex-end;   /* Rechts */
    margin-top: 20px;
}
```

### Form Layout

```css
.form-group {
    margin-bottom: 15px;
}

label {
    display: block;              /* Label op eigen regel */
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

input[type="text"],
input[type="password"],
input[type="email"],
select {
    width: 100%;
    padding: 8px 12px;           /* Ruimte in input */
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    font-family: inherit;        /* Gebruik default font */
}

input[type="text"]:focus,
input[type="password"]:focus,
input[type="email"]:focus,
select:focus {
    outline: none;
    border-color: #007bff;       /* Blauw border */
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);  /* Blauw glow */
}
```

### Responsive Grid

```css
/* Desktop: 2 kolommen */
@media (min-width: 768px) {
    .search-container {
        grid-template-columns: 1fr auto;
    }
}

/* Mobiel: 1 kolom */
@media (max-width: 767px) {
    .search-container {
        grid-template-columns: 1fr;  /* Stack verticaal */
    }
    
    .pagination {
        gap: 2px;
    }
    
    .pagination a,
    .pagination span {
        padding: 6px 8px;
        font-size: 12px;
    }
}
```

---

## Foutafhandeling

### Exception Handling

```php
try {
    // Database operatie
    $stmt = $pdo->prepare("DELETE FROM household_data WHERE id = ?");
    $stmt->execute([$id]);
    
} catch (PDOException $e) {
    // Database error
    error_log("Database error: " . $e->getMessage());
    $message = '<div class="error">Database fout opgetreden.</div>';
    
} catch (Exception $e) {
    // Generieke error
    error_log("Error: " . $e->getMessage());
    $message = '<div class="error">Onbekende fout opgetreden.</div>';
}
```

### Permission Denied Handling

```php
// Check permission
if (!$auth->hasPermission('export_data')) {
    // Option 1: Show message
    die('Je hebt geen rechten om gegevens te exporteren.');
    
    // Option 2: Redirect
    // header('Location: admin/content.php');
    // exit;
}

// Alternatief: requirePermission method
$auth->requirePermission('export_data');  // Dies if denied
```

### Validation Error Messages

```php
// Success message
$message = '<div style="color: green; padding: 10px; background: #d4edda; 
            border-left: 4px solid #28a745; margin: 10px 0;">
            ✓ Actie succesvol voltooid!
            </div>';

// Error message
$message = '<div style="color: red; padding: 10px; background: #f8d7da; 
            border-left: 4px solid #dc3545; margin: 10px 0;">
            ✗ Fout: ' . htmlspecialchars($errorText) . '
            </div>';
```

---

## Rolgebaseerde Toegangscontrole

### Permissie Systeem

```php
// In Auth.php: hasPermission method
public function hasPermission(string $permission): bool {
    $user = $this->getCurrentUser();
    if (!$user) return false;
    
    // Admins hebben alle permissies
    if ($user['role'] === 'admin') {
        return true;
    }
    
    // Rol-specifieke permissies
    $rolePermissions = [
        'admin'  => ['view_data', 'export_data', 'manage_users'],
        'editor' => ['view_data', 'export_data'],
        'viewer' => ['view_data'],
    ];
    
    $userPermissions = $rolePermissions[$user['role']] ?? [];
    return in_array($permission, $userPermissions);
}
```

### Rol Beschrijvingen

#### Admin Rol
```
Permissies: 
  ✓ Bekijk gegevens
  ✓ Exporteer gegevens
  ✓ Verwijder gegevens
  ✓ Beheer gebruikers
  ✓ Beheer content

Gebruiksscenario:
  - Systeembeheerders
  - Volledige controle
```

#### Editor Rol
```
Permissies:
  ✓ Bekijk gegevens
  ✓ Exporteer gegevens
  ✗ Verwijder gegevens
  ✗ Beheer gebruikers
  ✗ Beheer content

Gebruiksscenario:
  - Team leden die data nodig hebben
  - Kunnen data downloaden voor analyse
  - Kunnen niet verwijderen/wijzigen
```

#### Viewer Rol
```
Permissies:
  ✓ Bekijk gegevens
  ✗ Exporteer gegevens
  ✗ Verwijder gegevens
  ✗ Beheer gebruikers
  ✗ Beheer content

Gebruiksscenario:
  - Read-only toegang
  - Kunnen data bekijken maar niet downloaden
  - Voor stakeholders/rapportage
```

### Permission Checking in Code

```php
// 1. Check before displaying UI element
<?php if ($auth->canExportData()): ?>
    <a href="?export=1" class="btn">Exporteer naar CSV</a>
<?php endif; ?>

// 2. Require permission (blocks execution)
$auth->requirePermission('manage_users');
// Code onder deze regel alleen voor admins

// 3. Check permission for action
if ($auth->hasPermission('delete_data')) {
    // Delete knop tonen
}

// 4. Check specific permissions
if ($auth->canViewData()) {
    // Show data table
}
if ($auth->canExportData()) {
    // Show export button
}
if ($auth->canManageUsers()) {
    // Show user management
}
```

---

## Beveiligingspraktijken

### SQL Injection Preventie

```php
// ❌ NIET DOEN - Kwetsbaar
$id = $_GET['id'];
$query = "SELECT * FROM household_data WHERE id = $id";

// ✅ DO DOEN - Veilig met prepared statements
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM household_data WHERE id = ?");
$stmt->execute([$id]);
```

### XSS Prevention

```php
// ❌ NIET DOEN - XSS kwetsbaar
echo $_POST['username'];

// ✅ DO DOEN - HTML escape
echo htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
```

### CSRF Protection

```php
// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// In form
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Verify CSRF token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token validation failed');
}
```

---

## Troubleshooting

### 500 Errors Debuggen

1. **Enable error display** (dev only):
```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
```

2. **Check PHP error log**:
```bash
tail -100 /var/log/php_errors.log
```

3. **Verify database connection**:
```php
try {
    $pdo->query("SELECT 1");
    echo "Connected!";
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage();
}
```

4. **Check permissions**:
```bash
chmod 755 /var/www/html/rekentoolbewoner
chmod 644 /var/www/html/rekentoolbewoner/*.php
```

### Login Issues

| Probleem | Oorzaak | Oplossing |
|----------|---------|----------|
| "Invalid username or password" | Fout wachtwoord | Check default admin credentials |
| Account locked | 5 mislukte pogingen | Wacht 15 minuten |
| "Account disabled" | active=0 in DB | Update admin_users tabel |
| Session timeout | Te lang inactief | Login opnieuw |

### Data Not Showing

1. Check table exists: `SHOW TABLES LIKE 'household_data'`
2. Check data exists: `SELECT COUNT(*) FROM household_data`
3. Check permissions: User must have 'view_data' permission
4. Check database connection in config/database.php

---

**Versie**: 1.0  
**Laatst bijgewerkt**: April 2026  
**Auteur**: Avery Vermaas