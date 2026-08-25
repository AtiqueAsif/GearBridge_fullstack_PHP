# GearBridge
## Peer-to-Peer Campus Tool & Equipment Library

GearBridge is a dynamic, database-driven web application built for the CSE 3120 Web Programming open-ended lab. It allows students and staff to list underused equipment, browse shared resources, send borrowing requests, approve or reject incoming requests, track active borrowings, and confirm returns.

### Technology
- HTML5
- CSS3
- JavaScript
- PHP 8.1+
- MySQL / MariaDB
- PDO prepared statements


### Visual Theme
- High-contrast **Yellow + Black + White** campus-tech design system
- Bold condensed display typography with clean system-font body copy
- Functional HTML/PHP navigation and CTA buttons (the approved hero artwork is used as visual inspiration, not as a clickable image map)
- Black dashboard sidebar with yellow active states and white work area
- Responsive public pages and dashboard for desktop, tablet and mobile

### Core Features
- Student/staff registration and login
- Secure password hashing and session authentication
- Public equipment browsing
- Search, category, condition and availability filters
- Pagination
- Item details
- Add, edit and soft-delete owned items
- Secure image upload (JPG/PNG/WEBP)
- Borrow requests with date validation
- Duplicate pending-request protection
- Owner approval/rejection
- Competing pending requests automatically rejected after approval
- Active borrowing / lending view
- Owner-confirmed return workflow
- Borrow history
- Profile update
- Responsive public website and user dashboard
- CSRF protection, authorization checks and output escaping
- Transaction-based approval and return state changes

## Folder Structure

```text
gearbridge/
├── index.php
├── browse.php
├── item-details.php
├── about.php
├── login.php
├── register.php
├── dashboard/
├── actions/
│   ├── auth/
│   ├── items/
│   ├── borrow/
│   └── profile/
├── includes/
├── components/
├── assets/
├── uploads/items/
└── database/
    ├── gearbridge_db.sql
    ├── schema.sql
    └── seed.sql
```

## XAMPP Setup

### 1. Copy the project
Extract the ZIP and copy the **gearbridge** folder to:

```text
C:\xampp\htdocs\gearbridge
```

### 2. Start services
Open XAMPP Control Panel and start:
- Apache
- MySQL

### 3. Import the database
Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Choose **Import** and import:

```text
database/gearbridge_db.sql
```

The SQL file creates the `gearbridge_db` database, all required tables, relationships, indexes, default categories, **20 demo users, 31 demo items, and 23 demo borrow records**.

### 4. Check database configuration
Default XAMPP settings are already configured in:

```text
includes/config.php
```

Defaults:

```text
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = gearbridge_db
DB_USER = root
DB_PASS = (empty)
```

If your MySQL settings are different, edit only `includes/config.php`.

### 5. Open the application
Use:

```text
http://localhost/gearbridge/
```

The project expects the folder name `gearbridge`. If you rename the folder, update `BASE_URL` in `includes/config.php`.

## Replacing Built-in Item Images with Real Photos

All built-in item images are stored in one folder:

```text
assets/images/items/
```

To replace any built-in image, use a real 4:3 JPG photo (recommended **1200×900 px**) and overwrite the matching file while keeping **exactly the same filename**. No PHP or SQL change is required. A complete item-to-filename map is provided in:

```text
assets/images/items/HOW_TO_REPLACE_ITEM_PHOTOS.txt
```

Images uploaded normally through the Add Item form continue to be stored in `uploads/items/`.

## Built-in Demo Data

The final package includes a ready-to-use demo dataset so the application is populated immediately after import.

- **10 Student accounts**
- **10 Staff accounts**
- **31 shareable campus items** across Electronics, Cameras, Lab Tools, Textbooks and Other
- **23 borrowing records** including Pending, Approved, Rejected, Cancelled and Returned examples
- Four items are intentionally marked as currently borrowed so the Active Borrowings screens are populated
- Built-in item photos are centralized in `assets/images/items/` so they can be replaced with real photos without changing database paths

All demo accounts use the same password:

```text
Demo@123
```

Student emails:

```text
student01@demo.com
student02@demo.com
...
student10@demo.com
```

Staff emails:

```text
staff01@demo.com
staff02@demo.com
...
staff10@demo.com
```

For a quick test, use:

```text
Student: student01@demo.com
Staff:   staff01@demo.com
Password: Demo@123
```

A complete account/name list is available in:

```text
database/DEMO_ACCOUNTS.txt
```

### If you already imported an older version of the database

You do **not** need to delete your database. Import only:

```text
database/demo_data.sql
```

It safely refreshes the built-in demo accounts, their demo items and related demo borrowing records while leaving your non-demo accounts/items untouched.

For a brand-new setup, import only:

```text
database/gearbridge_db.sql
```

That file contains the complete schema, categories and demo dataset.

## Recommended Functional Test

Use at least three accounts to test the complete peer-to-peer workflow.

1. Register User A.
2. User A lists a camera or lab tool.
3. Register User B and User C.
4. User B and User C each send a request for User A's item.
5. User A approves User B.
6. Confirm:
   - User B's request becomes **Approved**.
   - User C's pending request becomes **Rejected**.
   - The item becomes **Borrowed**.
7. User A confirms the physical return.
8. Confirm:
   - The request becomes **Returned**.
   - The item becomes **Available** again.
   - The completed transaction appears in Borrow History.

## Important Business Rules
- Guests may browse, but login is required to borrow or list equipment.
- A user cannot borrow their own item.
- Only an item owner can edit/delete that item or manage its incoming requests.
- A user cannot create duplicate pending requests for the same item.
- Only one active approved borrowing is allowed for an item.
- Item availability is controlled by the borrowing workflow.
- A currently borrowed item cannot be deleted.
- Item deletion is soft deletion so historical borrowing records remain intact.
- The owner confirms return after receiving the physical item.

## Image Upload
Supported item image formats:
- JPG/JPEG
- PNG
- WEBP

Maximum file size: **5 MB**.

Uploaded files are saved in:

```text
uploads/items/
```

The upload directory blocks PHP/script execution through its `.htaccess` file when Apache supports the rule.

## Notes
- There is intentionally no payment, cart, checkout, delivery, live chat, OTP, or complex administrator module because they are outside the defined peer-to-peer borrowing scope.
- Student and staff accounts have the same peer-level platform permissions.
- Built-in demo accounts are included only for assignment demonstration/testing. Their password is stored as a secure PHP-compatible hash, not plain text.
