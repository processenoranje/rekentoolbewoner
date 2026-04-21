# Frontend & JavaScript Documentatie

## Inhoudsopgave

1. [Tabel Componenten](#tabel-componenten)
2. [Sorteer Systeem](#sorteer-systeem)
3. [Zoek & Filter](#zoek--filter)
4. [Modal Dialogen](#modal-dialogen)
5. [Buttons & Action Elements](#buttons--action-elements)
6. [Responsive Design](#responsive-design)
7. [Formulieren](#formulieren)
8. [Berichten & Notifications](#berichten--notifications)

---

## Tabel Componenten

### Tabel Header (th)

De tabel header is **klikbaar** voor sortering.

```html
<!-- HTML -->
<table>
  <thead>
    <tr>
      <th>
        <a href="?sort=id&order=<?php echo $filter['order'] === 'ASC' ? 'DESC' : 'ASC'; ?>">
          ID
          <?php if ($filter['sort'] === 'id'): ?>
            <span class="sort-arrow">
              <?php echo $filter['order'] === 'ASC' ? '↑' : '↓'; ?>
            </span>
          <?php endif; ?>
        </a>
      </th>
      <th>
        <a href="?sort=postcode&order=<?php echo $filter['order'] === 'ASC' ? 'DESC' : 'ASC'; ?>">
          Postcode
          <?php if ($filter['sort'] === 'postcode'): ?>
            <span class="sort-arrow">↑↓</span>
          <?php endif; ?>
        </a>
      </th>
    </tr>
  </thead>
</table>

<!-- CSS Styling -->
<style>
  th {
    cursor: pointer;        /* Wijzer cursor wijst op klikbaarheid */
    user-select: none;      /* Tekst kan niet geselecteerd worden */
    background: #f8f9fa;    /* Licht grijze achtergrond */
  }
  
  th:hover {
    background: #e9ecef;    /* Donkerder grijs bij hover */
  }
  
  th a {
    color: #333;            /* Donkergrijs tekst */
    text-decoration: none;  /* Geen underline */
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  
  .sort-arrow {
    margin-left: 5px;
    font-size: 11px;
    color: #007bff;         /* Blauw pijltje */
  }
</style>
```

**Sorteer Indicators**:
- `↑` = Oplopend (A naar Z, klein naar groot)
- `↓` = Aflopend (Z naar A, groot naar klein)

### Tabel Data Rows (td)

```html
<!-- Normale data rij -->
<tr>
  <td><?php echo htmlspecialchars($data['postcode']); ?></td>
  <td><?php echo htmlspecialchars($data['huisnummer']); ?></td>
  <td><?php echo $data['zonnepanelen'] ? 'Ja' : 'Nee'; ?></td>
  <td><?php echo number_format($data['verbruik'], 0, ',', '.'); ?> kWh</td>
  <td>
    <div class="actions-cell">
      <button onclick="showDeleteModal(<?php echo $data['id']; ?>)" 
              class="action-btn action-delete">
        Verwijderen
      </button>
    </div>
  </td>
</tr>

<!-- Hover effect CSS -->
<style>
  tr:hover {
    background-color: #f9f9f9;  /* Zeer licht grijs achtergrond */
  }
  
  td {
    padding: 12px;              /* Interne ruimte */
    border-bottom: 1px solid #eee;  /* Subtiele lijn tussen rijen */
  }
</style>
```

### Lege Tabel State

```html
<!-- Als geen gegevens beschikbaar -->
<div class="empty-state">
  <p>Geen gegevens gevonden.</p>
  <p style="color: #ccc; font-size: 12px;">
    Pas je zoekopdracht aan of voeg gegevens toe.
  </p>
</div>

<!-- CSS -->
<style>
  .empty-state {
    text-align: center;     /* Gecentreerd */
    padding: 40px;          /* Veel ruimte */
    color: #999;            /* Grijs tekst */
    background: #f9f9f9;    /* Licht achtergrond */
    border-radius: 4px;
    border: 1px dashed #ddd;  /* Gestippelde border */
  }
</style>
```

---

## Sorteer Systeem

### URL Query Parameters

```
Basis URL: admin/data.php

Sorteer opties:
  ?sort=id&order=ASC          → ID oplopend
  ?sort=id&order=DESC         → ID aflopend
  ?sort=postcode&order=ASC    → Postcode A-Z
  ?sort=submitted_at&order=DESC → Nieuwste eerst

Combinatie met zoeken:
  ?search=1234AB&sort=postcode&order=ASC&page=1
  → Zoek naar 1234AB, sorteer op postcode, pagina 1
```

### PHP Implementatie

```php
// Huidige sorteerwaarden uit URL
$filter = [
    'sort' => $_GET['sort'] ?? 'submitted_at',    // Standaard: datum
    'order' => $_GET['order'] ?? 'DESC',          // Standaard: aflopend
    'page' => (int)($_GET['page'] ?? 1),
];

// Validatie: alleen toegestane velden
$allowedSorts = [
    'id', 'postcode', 'huisnummer', 
    'zonnepanelen', 'verbruik', 'opwek', 'submitted_at'
];

// Sanitize veld (default naar submitted_at als ongeldig)
$sortField = in_array($filter['sort'], $allowedSorts) 
    ? $filter['sort'] 
    : 'submitted_at';

// Sanitize richting
$orderDir = $filter['order'] === 'ASC' ? 'ASC' : 'DESC';

// SQL query
$query = "SELECT * FROM household_data 
          WHERE [conditions] 
          ORDER BY $sortField $orderDir 
          LIMIT ?, ?";
```

### Sorteer Toggle

```php
<!-- Toggle tussen ASC en DESC -->
<?php
// Bepaal volgende sorteerrichting
$nextOrder = $filter['order'] === 'ASC' ? 'DESC' : 'ASC';

// Bouw URL met geklikte kolom en omgekeerde richting
$newUrl = http_build_query(array_merge(
    $filter,
    ['sort' => 'postcode', 'order' => $nextOrder]
));
?>

<a href="?<?php echo $newUrl; ?>">
    Postcode
    <?php if ($filter['sort'] === 'postcode'): ?>
        <span class="sort-arrow"><?php echo $filter['order'] === 'ASC' ? '↑' : '↓'; ?></span>
    <?php endif; ?>
</a>
```

---

## Zoek & Filter

### Zoek Box HTML

```html
<!-- Zoek formulier -->
<div class="search-container">
    <div class="search-box">
        <input 
            type="text" 
            name="search" 
            placeholder="Zoek postcode of huisnummer..."
            value="<?php echo htmlspecialchars($filter['search']); ?>"
            autofocus>
    </div>
    <button type="submit" class="btn btn-primary">
        🔍 Zoeken
    </button>
    <?php if (!empty($filter['search'])): ?>
        <a href="?" class="btn btn-secondary">
            ✕ Wissen
        </a>
    <?php endif; ?>
</div>
```

### Zoek Logica (PHP)

```php
// Haal zoekopdracht op
$searchTerm = $_GET['search'] ?? '';

// Bouw WHERE clause
$where = [];
$params = [];

if (!empty($searchTerm)) {
    $searchPattern = '%' . $searchTerm . '%';  // Wildcards
    
    // Zoek in postcode EN huisnummer
    $where[] = "(postcode LIKE ? OR huisnummer LIKE ?)";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

// Construeer volledige WHERE
$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// SQL met WHERE
$query = "SELECT * FROM household_data $whereClause";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
```

### Zoek Voorbeelden

```
Zoekinvoer: "1234AB"
  → Vindt alle rijen met postcode = 1234AB
  
Zoekinvoer: "23"
  → Vindt alle rijen met huisnummer = 23
  
Zoekinvoer: "12"
  → Vindt postcode 1234AB (bevat 12) EN huisnummer 12, 123, 1200, etc.
```

---

## Modal Dialogen

### Delete Confirmation Modal

```html
<!-- Modal HTML -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <!-- Modal header -->
        <div class="modal-header">
            🗑️ Entry Verwijderen
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
            <p><strong>Weet je hier zeker van?</strong></p>
            <p style="color: #666; font-size: 13px;">
                Dit kan <strong>niet</strong> ongedaan gemaakt worden. 
                De gegevens worden permanent verwijderd.
            </p>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-buttons">
            <!-- Cancel button -->
            <button 
                onclick="hideDeleteModal()" 
                class="btn btn-secondary">
                ✕ Annuleren
            </button>
            
            <!-- Delete form -->
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                
                <!-- Delete button -->
                <button 
                    type="submit" 
                    class="btn btn-danger"
                    onclick="return confirm('Definitief verwijderen?')">
                    🗑️ Verwijderen
                </button>
            </form>
        </div>
    </div>
</div>

<!-- CSS -->
<style>
    .modal {
        display: none;                  /* Verborgen standaard */
        position: fixed;                /* Blijft op plaats */
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);  /* Semi-transparante overlay */
        z-index: 1000;                  /* Bovenop alles */
        
        /* Flex voor centering */
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .modal.show {
        display: flex;                  /* Zichtbaar */
    }
    
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
    }
    
    .modal-header {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    
    .modal-body {
        margin-bottom: 20px;
        color: #666;
    }
    
    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
</style>
```

### JavaScript Modal Functions

```javascript
/**
 * Toon delete modal
 * @param {number} id - Entry ID om te verwijderen
 */
function showDeleteModal(id) {
    // Vul de hidden input met het ID
    document.getElementById('deleteId').value = id;
    
    // Zet modal zichtbaar
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'block';
    
    // Optioneel: voeg focus toe aan button
    modal.querySelector('.btn-danger').focus();
}

/**
 * Verberg delete modal
 */
function hideDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'none';
}

/**
 * Sluit modal bij klik op overlay (buiten modal-content)
 */
window.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    
    // Als geklikt op overlay (niet modal-content)
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

/**
 * Sluit modal bij ESC toets
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('deleteModal');
        if (modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    }
});
```

### Change Role Modal

```html
<div id="roleChangeModal_<?php echo $user['id']; ?>" class="modal">
    <div class="modal-content">
        <div class="modal-header">👤 Rol Wijzigen</div>
        
        <form method="POST">
            <input type="hidden" name="action" value="change_role">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-group">
                <label>Selecteer Nieuwe Rol:</label>
                <select name="role" required>
                    <option value="viewer" 
                            <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>
                        👁️ Viewer (Alleen Kijken)
                    </option>
                    <option value="editor" 
                            <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>
                        ✏️ Editor (Kijken + Exporteren)
                    </option>
                    <option value="admin" 
                            <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                        👨‍💼 Admin (Volledige Toegang)
                    </option>
                </select>
            </div>
            
            <div class="modal-buttons">
                <button 
                    type="button" 
                    onclick="document.getElementById('roleChangeModal_<?php echo $user['id']; ?>').style.display='none'" 
                    class="btn btn-secondary">
                    ✕ Annuleren
                </button>
                <button type="submit" class="btn btn-primary">
                    ✓ Wijzigen
                </button>
            </div>
        </form>
    </div>
</div>
```

---

## Buttons & Action Elements

### Button Types

```html
<!-- Primary Button (blauw) -->
<button class="btn btn-primary">Primaire Actie</button>

<!-- Success Button (groen) -->
<button class="btn btn-success">✓ Succes</button>

<!-- Danger Button (rood) -->
<button class="btn btn-danger">✗ Verwijderen</button>

<!-- Secondary Button (grijs) -->
<button class="btn btn-secondary">Secundaire Actie</button>

<!-- Link als Button -->
<a href="?" class="btn btn-secondary">Terug</a>

<!-- Button Group -->
<div style="display: flex; gap: 10px;">
    <button class="btn btn-secondary">← Annuleren</button>
    <button class="btn btn-primary">Opslaan →</button>
</div>
```

### Button Styling CSS

```css
/* Base button styles */
.btn {
    background: #007bff;            /* Standaard blauw */
    color: white;
    padding: 8px 16px;              /* Verticaal en horizontaal padding */
    border: none;
    border-radius: 4px;             /* Afgeronde hoeken */
    cursor: pointer;
    text-decoration: none;          /* Geen link underline */
    display: inline-block;          /* Inline toch blok model */
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;      /* Smooth hover effect */
}

.btn:hover {
    background: #0056b3;            /* Donkerder blauw */
    transform: translateY(-1px);    /* Licht tillen */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);  /* Schaduw */
}

.btn:active {
    transform: translateY(0);       /* Terug naar normaal */
}

/* Button Varianten */
.btn-primary {
    background: #007bff;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-success {
    background: #28a745;
}

.btn-success:hover {
    background: #218838;
}

.btn-danger {
    background: #dc3545;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-warning {
    background: #ffc107;
    color: #333;                    /* Donker tekst voor geel */
}

.btn-warning:hover {
    background: #e0a800;
}

/* Kleine buttons */
.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

/* Large buttons */
.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

/* Full width */
.btn-block {
    display: block;
    width: 100%;
}

/* Disabled state */
.btn:disabled,
.btn[disabled] {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn:disabled:hover {
    background: #007bff;            /* Geen hover effect */
    transform: none;
}
```

### Action Cell Layout

```html
<!-- Delete knop in tabel -->
<td>
    <div class="actions-cell">
        <button 
            onclick="showDeleteModal(<?php echo $data['id']; ?>)" 
            class="action-btn action-delete">
            🗑️ Verwijderen
        </button>
    </div>
</td>

<!-- Multiple buttons in tabel -->
<td>
    <div class="actions-cell">
        <a href="?edit=<?php echo $data['id']; ?>" 
           class="action-btn action-edit">
            ✏️ Wijzigen
        </a>
        <button 
            onclick="showDeleteModal(<?php echo $data['id']; ?>)" 
            class="action-btn action-delete">
            🗑️ Verwijderen
        </button>
    </div>
</td>

<!-- CSS -->
<style>
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
        text-decoration: none;
    }
    
    .action-edit {
        background: #007bff;         /* Blauw */
        color: white;
    }
    
    .action-edit:hover {
        background: #0056b3;
    }
    
    .action-delete {
        background: #dc3545;         /* Rood */
        color: white;
    }
    
    .action-delete:hover {
        background: #c82333;
    }
</style>
```

---

## Responsive Design

### Mobile-First Breakpoints

```css
/* Extra Small (mobiel) - Standaard */
@media (max-width: 576px) {
    body {
        font-size: 14px;
    }
    
    .container {
        padding: 10px;
    }
    
    table {
        font-size: 12px;
    }
    
    .pagination {
        gap: 2px;
    }
    
    .pagination a,
    .pagination span {
        padding: 6px 8px;
        font-size: 11px;
    }
}

/* Small (tablet) */
@media (min-width: 576px) {
    .container {
        margin: 10px auto;
    }
}

/* Medium (klein laptop) */
@media (min-width: 768px) {
    .search-container {
        grid-template-columns: 1fr auto;
    }
}

/* Large (laptop) */
@media (min-width: 992px) {
    .container {
        max-width: 960px;
    }
}

/* Extra Large (groot scherm) */
@media (min-width: 1200px) {
    .container {
        max-width: 1200px;
    }
}
```

### Responsive Tabel

```css
/* Mobiel: gestapelde tabel */
@media (max-width: 768px) {
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead {
        display: none;              /* Verberg headers */
    }
    
    tr {
        display: block;             /* Volledige breedte */
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    td {
        display: block;
        text-align: right;          /* Rechts uitgelijnd */
        padding-left: 50%;
        position: relative;
    }
    
    td::before {
        content: attr(data-label);  /* "Postcode:", "Huisnummer:", etc. */
        position: absolute;
        left: 6px;
        font-weight: 600;
        text-align: left;
    }
}

<!-- HTML met data attributes -->
<td data-label="Postcode:">1234AB</td>
<td data-label="Huisnummer:">23</td>
```

### Responsive Grid

```css
/* Container grid */
.container > div:first-child {
    display: grid;
    grid-template-columns: 1fr;    /* Mobiel: 1 kolom */
    gap: 10px;
}

@media (min-width: 768px) {
    .container > div:first-child {
        grid-template-columns: auto 1fr;  /* Tablet: titel en buttons naast elkaar */
    }
}

/* Search container */
.search-container {
    display: grid;
    grid-template-columns: 1fr;    /* Mobiel: stapel */
    gap: 10px;
}

@media (min-width: 768px) {
    .search-container {
        grid-template-columns: 1fr auto;  /* Desktop: naast elkaar */
    }
}
```

---

## Formulieren

### Form Group Layout

```html
<!-- Basis form -->
<form method="POST">
    <!-- Form groep -->
    <div class="form-group">
        <label for="username">Gebruikersnaam:</label>
        <input 
            type="text" 
            id="username" 
            name="username" 
            required
            autofocus>
    </div>
    
    <div class="form-group">
        <label for="password">Wachtwoord:</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            required>
    </div>
    
    <div class="form-group">
        <label for="role">Rol:</label>
        <select id="role" name="role" required>
            <option value="viewer">Viewer</option>
            <option value="editor">Editor</option>
            <option value="admin">Admin</option>
        </select>
    </div>
    
    <!-- Submit buttons -->
    <div style="display: flex; gap: 10px;">
        <button type="submit" class="btn btn-primary">✓ Opslaan</button>
        <button type="reset" class="btn btn-secondary">↺ Herstellen</button>
    </div>
</form>

<!-- CSS -->
<style>
    .form-group {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
    }
    
    input[type="text"],
    input[type="password"],
    input[type="email"],
    select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        font-family: inherit;
    }
    
    input:focus,
    select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    }
    
    input:invalid {
        border-color: #dc3545;
    }
</style>
```

### Validation Indicators

```html
<!-- Required field indicator -->
<label>
    Gebruikersnaam:
    <span style="color: red; font-weight: bold;">*</span>
</label>

<!-- Error message -->
<input type="text" name="username">
<span style="color: #dc3545; font-size: 12px; margin-top: 3px;">
    ✗ Gebruikersnaam is verplicht
</span>

<!-- Success message -->
<span style="color: #28a745; font-size: 12px; margin-top: 3px;">
    ✓ Wachtwoord sterk
</span>
```

---

## Berichten & Notifications

### Success Message

```html
<div style="
    color: green; 
    padding: 10px; 
    background: #d4edda; 
    border-left: 4px solid #28a745; 
    margin: 10px 0;
    border-radius: 4px;
">
    ✓ Actie succesvol voltooid!
</div>

<!-- CSS Klasse -->
<style>
    .message-success {
        color: #155724;
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        border-left: 4px solid #28a745;
        padding: 12px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    .message-success::before {
        content: "✓ ";
        font-weight: bold;
    }
</style>
```

### Error Message

```html
<div style="
    color: red; 
    padding: 10px; 
    background: #f8d7da; 
    border-left: 4px solid #dc3545; 
    margin: 10px 0;
    border-radius: 4px;
">
    ✗ Fout: <?php echo htmlspecialchars($errorText); ?>
</div>

<!-- CSS Klasse -->
<style>
    .message-error {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        border-left: 4px solid #dc3545;
        padding: 12px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    .message-error::before {
        content: "✗ ";
        font-weight: bold;
    }
</style>
```

### Warning Message

```html
<div style="
    color: #856404; 
    padding: 10px; 
    background: #fff3cd; 
    border-left: 4px solid #ffc107; 
    margin: 10px 0;
    border-radius: 4px;
">
    ⚠️ Let op: Weet je zeker dat je verder wil gaan?
</div>

<!-- CSS Klasse -->
<style>
    .message-warning {
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        border-left: 4px solid #ffc107;
        padding: 12px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    .message-warning::before {
        content: "⚠️ ";
    }
</style>
```

### Info Message

```html
<div style="
    color: #0c5460; 
    padding: 10px; 
    background: #d1ecf1; 
    border-left: 4px solid #17a2b8; 
    margin: 10px 0;
    border-radius: 4px;
">
    ℹ️ Informatie: Je account wordt beschermd door twee-factor authenticatie.
</div>

<!-- CSS Klasse -->
<style>
    .message-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        border-left: 4px solid #17a2b8;
        padding: 12px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    .message-info::before {
        content: "ℹ️ ";
    }
</style>
```

### Auto-Hide Notifications

```html
<div id="successMessage" class="message-success" style="display: none;">
    ✓ Opgeslagen!
</div>

<script>
// Toon bericht
function showSuccess(text) {
    const msg = document.getElementById('successMessage');
    msg.textContent = '✓ ' + text;
    msg.style.display = 'block';
    
    // Verberg na 3 seconden
    setTimeout(() => {
        msg.style.display = 'none';
    }, 3000);
}

// Gebruik
showSuccess('Wijzigingen opgeslagen!');
</script>
```

---

**Versie**: 1.0  
**Laatst bijgewerkt**: April 2026  
**Auteur**: Avery Vermaas