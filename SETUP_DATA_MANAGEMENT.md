# Data Management System - Quick Setup Guide

## Step 1: Update Database (if upgrading)
If you're upgrading from the previous admin_users table, you need to update the role enum to include 'viewer':

```sql
ALTER TABLE admin_users MODIFY COLUMN role enum('admin','editor','viewer') NOT NULL DEFAULT 'admin';
```

## Step 2: Access the Data Management Interface
1. Navigate to `http://yoursite.com/admin/data.php`
2. You'll be prompted to log in if not already authenticated
3. The system will check your permissions automatically

## Step 3: Create Team Member Accounts

### For someone who needs to EXPORT data:
1. Go to **Gebruikersbeheer** (User Management)
2. Click **Add New User**
3. Fill in:
   - Username: `john_sales` (example)
   - Password: `[secure password]`
   - Email: `john@company.com`
   - Full Name: `John Smith`
   - Role: **Editor** ← (can view and export data)
4. Click **Create User**

### For someone who needs VIEW-ONLY access:
1. Follow same steps as above
2. Select Role: **Viewer** ← (can only view data, no export/delete)

### For someone who needs FULL ADMIN access:
1. Follow same steps as above
2. Select Role: **Admin** ← (full system access)

## Step 4: Team Members Can Now:
- Log in at `http://yoursite.com/login.php`
- Access the data via **Gegevens** link
- See all submitted household data
- Search by postcode or huisnummer
- Sort any column
- Export to CSV (if Editor or Admin role)
- View statistics dashboard

## Features Available by Role

| Feature | Admin | Editor | Viewer |
|---------|-------|--------|--------|
| View data | ✓ | ✓ | ✓ |
| Search/filter data | ✓ | ✓ | ✓ |
| Sort columns | ✓ | ✓ | ✓ |
| Export to CSV | ✓ | ✓ | ✗ |
| Delete entries | ✓ | ✗ | ✗ |
| Manage content | ✓ | ✗ | ✗ |
| Manage users | ✓ | ✗ | ✗ |

## Password Management
Default admin password: `password`

**⚠️ IMPORTANT**: Change this immediately after setup!

To change your own password:
1. Go to **Gebruikersbeheer** (Users)
2. Click **Change Password** on your user
3. Enter new password
4. Click **Change Password**

## Admin Panel Navigation
Once logged in, you'll see navigation for:
- **Content Manager** (📋) - Edit page content
- **Gegevens** (📊) - View and export household data
- **Gebruikersbeheer** (👥) - Manage user accounts [Admin only]
- **Logout** - End your session

## File Locations
- **Data page**: `/admin/data.php`
- **Users page**: `/admin/users.php`
- **Content page**: `/admin/content.php`
- **Login page**: `/login.php`

## Exporting Data in Excel Format
1. Log in with Admin or Editor account
2. Go to **Gegevens** (Data)
3. Use search/filters if needed to narrow results
4. Click **📥 Export naar CSV**
5. Open file in Excel
6. Excel will automatically detect the semicolon delimiter

## Troubleshooting

### I can't see the Data link
- You may be logged out - refresh the page
- Your role may not have permission - ask admin to change your role

### The export button is greyed out
- Your role is "Viewer" - only Editors and Admins can export
- Contact admin to upgrade your role

### I forgot my password
- Contact an admin who can reset it via the user management panel

### Data seems outdated
- Try refreshing the page (Ctrl+R or Cmd+R)
- Make sure new submissions were actually received

## Security Notes
- All data access is logged via your user account
- Export operations record who exported what and when
- Do not share your login credentials
- Log out when finished, especially on shared computers
- Deletion is permanent - confirm carefully

## Next Steps
- Test the system with a sample Editor account
- Train team members on how to access and export data
- Consider which colleagues need which access levels
- Review exported data regularly for quality assurance

---

For detailed documentation, see `DATA_MANAGEMENT.md`