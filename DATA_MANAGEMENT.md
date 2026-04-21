# Data Management System - Rekentool Bewoner

## Overview
A comprehensive data management interface has been added behind the admin login. This allows your team to view, search, filter, and export household data securely.

## Features

### 1. **Data Viewing & Searching**
- View all submitted household data in a paginated table
- Search by postcode or huisnummer
- Sort by any column (ID, postcode, huisnummer, solar panels, consumption, production, submission date)
- Pagination with 25 results per page

### 2. **Data Export**
- **Export to CSV**: Download all household data as a semicolon-delimited CSV file
- Excel-compatible format with UTF-8 BOM
- Includes all fields: postcode, huisnummer, toevoeging, zonnepanelen, verbruik, opwek, submission date
- CSV exports are timestamped in filename

### 3. **Data Management**
- **Delete entries**: Remove individual household submissions with confirmation dialog
- Safe deletion with warning modal to prevent accidental removal
- Soft delete support (entries are permanently removed from database)

### 4. **Analytics Dashboard**
Real-time statistics displayed at the top:
- **Total Entries**: Number of submissions received
- **With Solar Panels**: Count of households with solar installation
- **Average Consumption (kWh)**: Mean annual consumption
- **Average Production (kWh)**: Mean solar production

### 5. **Responsive Design**
- Clean, professional interface
- Mobile-friendly responsive layout
- Consistent styling with rest of admin panel

## Access Control & Roles

### Role Levels
Three role tiers are available with different permissions:

1. **Admin** - Full access
   - Access content manager
   - Manage users (create, delete, change roles/passwords)
   - View household data
   - Export data
   - Delete data entries

2. **Editor** - Data access
   - View household data
   - Export data to CSV
   - Cannot access content manager or user management
   - Cannot delete data entries

3. **Viewer** - Read-only
   - View household data only
   - Cannot export data
   - Cannot delete data entries
   - Cannot access content manager or user management

### Creating Users with Specific Roles
Use the user management interface (`/admin/users.php`) to:
1. Click "Add New User"
2. Select desired role from dropdown
3. For team members who only need data access, select "Editor" or "Viewer"

### Changing User Roles
1. Go to Gebruikersbeheer (User Management)
2. Click "Change Role" on any user
3. Select new role from dropdown
4. Click "Change Role" to update

## Navigation

### Admin Panel Navigation
The admin panel now includes quick access links:
- 📋 **Content Manager** - Edit page content
- 📊 **Gegevens (Data)** - Access household data
- 👥 **Gebruikersbeheer (Users)** - Manage user accounts
- **Logout** - End session

Users will only see navigation items they have permission to access.

## Usage Examples

### Scenario 1: Give a colleague export-only access
1. Admin creates new user with role "Editor"
2. Colleague can log in and access data.php
3. Colleague can view and search data, export to CSV
4. Colleague cannot delete data or manage users

### Scenario 2: Give a colleague view-only access
1. Admin creates new user with role "Viewer"
2. Colleague can log in and access data.php
3. Colleague can view and search data
4. Colleague cannot export data or delete entries

### Scenario 3: Export monthly data report
1. Editor or Admin logs in
2. Goes to Gegevens (Data Management)
3. Uses search/filters if needed
4. Clicks "Export naar CSV"
5. Opens in Excel or spreadsheet application
6. Can perform additional analysis or send to stakeholders

## Technical Details

### Database Permissions
- All users authenticate against `admin_users` table
- Data queries are read-only for non-admin roles
- Delete operations require database write permission

### Security Features
- Role-based access control (RBAC)
- Session validation on every page load
- Permission checks prevent unauthorized data access
- Delete confirmations prevent accidental data loss
- All user input is escaped/sanitized

### Performance
- Paginated results (25 per page) reduce memory usage
- Indexed database queries on common search fields
- Efficient sorting on indexed columns

## CSV Export Format
The exported CSV file includes:
- **Headers**: All column names from household_data table
- **Delimiter**: Semicolon (;) for Excel compatibility
- **Encoding**: UTF-8 with BOM
- **Filename**: `household_data_YYYY-MM-DD_HH-mm-ss.csv`
- **Fields**: id, postcode, huisnummer, toevoeging, zonnepanelen, preset, verbruik, opwek, data_source, submitted_at

### Opening in Excel
1. Download CSV file
2. Open Excel
3. Go to Data > Get External Data > From Text
4. Select the downloaded CSV file
5. Choose "Semicolon" as delimiter
6. Continue through wizard and finish

## Suggested Future Enhancements
(Feel free to request these features)
- **Advanced filters**: Date range, solar panel status, consumption thresholds
- **Bulk actions**: Delete multiple selected entries, change status
- **Data visualization**: Charts showing consumption patterns, solar adoption
- **Batch import**: Upload data via CSV
- **Audit log**: Track who viewed/deleted/exported data and when
- **Scheduled exports**: Automatic CSV generation on a schedule
- **Data validation**: Flag suspicious entries before submission
- **API access**: Programmatic data access for external systems

## Troubleshooting

### "Access denied. You do not have permission" error
- Your user role doesn't have permission to access that feature
- Contact an admin to change your role

### Export button not visible
- Your role is "Viewer" - only Editors and Admins can export
- Contact an admin to upgrade your role

### Data not appearing after new submission
- Page may be cached - try refreshing browser
- Data may still be processing - wait a few seconds and refresh

## Contact & Support
For issues or feature requests related to the data management system, contact your administrator.