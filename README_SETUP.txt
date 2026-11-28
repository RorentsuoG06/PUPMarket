PUPMARKET PHP + MYSQL SETUP
============================

This version converts the PUPMarket pages into PHP pages and includes a
responsive visual design and modular product JavaScript features.

REQUIREMENTS
------------
1. XAMPP
2. Apache
3. MySQL
4. phpMyAdmin
5. PHP

PROJECT STRUCTURE
-----------------
PUPMarket_PHP/
│
├── index.php
├── products.php
├── about.php
├── contact.php
├── login.php
├── register.php
├── logout.php
├── database.sql
├── README_SETUP.txt
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
│
├── actions/
│   ├── process_contact.php
│   ├── process_login.php
│   └── process_register.php
│
├── css/
│   └── style.css
│
├── js/
│   └── script.js
│
└── images/
    └── your existing PUPMarket images


STEP 1 - PUT THE PROJECT IN XAMPP
---------------------------------
Copy the PUPMarket_PHP folder to:

C:\xampp\htdocs\

Your final location should be:

C:\xampp\htdocs\PUPMarket_PHP\


STEP 2 - START XAMPP
--------------------
Open XAMPP Control Panel.

Start:
- Apache
- MySQL


STEP 3 - CREATE THE DATABASE
----------------------------
Open phpMyAdmin:

http://localhost/phpmyadmin/

Go to the SQL tab.

Copy and run the contents of database.sql.

This creates:

db_pupmarket

with:

users
inquiries


STEP 3A - ENABLE ACCOUNT-LINKED LISTINGS AND NEXCONN CHAT
---------------------------------------------------------
After creating the database, run:

database/migrations/2026_11_27_nexconn_marketplace_chat.sql

Run this migration once after creating the database. It adds Nexconn
identities to users and creates the marketplace_listings table used for
account-owned listings and seller chat links.

Then run:

database/migrations/2026_11_27_marketplace_reports.sql

This adds listing/seller reports, the moderation queue, and the admin flag.
Promote a trusted account in phpMyAdmin's SQL tab:

UPDATE users SET is_admin = 1 WHERE username = 'your_admin_username';

Only grant this to trusted moderators. The Reports page is available in the
navbar only to accounts with this flag.

Then run:

database/migrations/2026_11_27_seed_sample_listings.sql

This seeds the sample products under the development PUPMarket Test Seller
account. It is safe to run again without duplicating those samples.

Create a Nexconn app in the Nexconn Console, then add its App Key, App Secret,
and data-center region to the project-root .env file:

NEXCONN_APP_KEY=your_app_key
NEXCONN_APP_SECRET=your_app_secret
NEXCONN_REGION=SG

Use the region matching the app's Nexconn data center (SG, NA, or SA). The App
Secret is used only by PHP on the server and must never be put in JavaScript.
If you change the data center, use the matching client region supported by the
Nexconn SDK.

Install the JavaScript packages and build the browser chat bundle from the
project root:

npm install
npm run build:chat

Make sure Apache can write product images under public/uploads/products.


STEP 4 - CHECK DATABASE CONNECTION
----------------------------------
Open:

config/database.php

Default XAMPP settings are:

$host = "localhost";
$username = "root";
$password = "";
$dbname = "db_pupmarket";

The project uses MySQLi (mysqli_connect, prepared statements, and mysqli queries) to match the PHP/MySQL approach demonstrated in class.

If your MySQL has a password, place it in $password.


STEP 5 - KEEP YOUR IMAGES
-------------------------
Copy your existing images folder into:

PUPMarket_PHP/images/

The current JavaScript expects these files:

datastructuresinjava.jpg
mdm.jpg
oopreviewer.jpg
unifset.jpg
peunif.jpg
calc.jpg
mouse.jpg
bndpaper.jpg
mica.jpg
cookie.jpg
no-image.jpg


STEP 6 - KEEP THE PROJECT CSS AND JAVASCRIPT
--------------------------------------------
Keep the project's css/style.css file in:

PUPMarket_PHP/css/style.css

The PHP files continue to use:

css/style.css

The browser behavior is consolidated in js/script.js. It initializes only the
features present on the current page.


STEP 7 - OPEN THE WEBSITE
-------------------------
Do NOT open index.php by double-clicking it.

Use Apache through a browser:

http://localhost/PUPMarket_PHP/


TESTING
-------

A. REGISTER

Open:

http://localhost/PUPMarket_PHP/register.php

Create a test account.

Then check phpMyAdmin:

db_pupmarket -> users

The account should appear there.

The password is stored as a password hash.

Registration requires an @iskolarngbayan.pup.edu.ph email and a password of
at least 6 characters. The OTP block is a UI preview only; it does not send
email or verify the entered code, so it must not be treated as student
verification.


B. LOGIN

Open:

http://localhost/PUPMarket_PHP/login.php

Use your registered @iskolarngbayan.pup.edu.ph email and password.

After successful login, you will be redirected to:

index.php

The yellow navbar button changes from:

Login

to:

Sign Out


C. LOGOUT

Click Sign Out.

The PHP session is destroyed and you are returned to:

login.php


D. CONTACT FORM

Open:

http://localhost/PUPMarket_PHP/contact.php

Submit an inquiry.

Then check:

db_pupmarket -> inquiries

The submitted inquiry should appear in the table.


IMPORTANT NOTE ABOUT PRODUCTS AND CHAT
--------------------------------------
The sample products are stored in MySQL under the development PUPMarket Test
Seller account. Listings posted by signed-in users are also saved in MySQL
with their seller account, and the Contact Seller action opens a Nexconn
direct chat. Product listings are loaded from the database, not browser
localStorage.

The product catalog uses server-side search, category filtering, sorting, and
pagination. Signed-in users can manage their own listings from the My Listings
page, including editing details or replacing a photo and deleting a listing.
These actions are ownership-checked and use the site's CSRF protection.
Signed-in users can report a listing or its seller. Moderators can review,
dismiss, or mark reports as actioned from the Moderation page.


WHY PHP INCLUDES ARE USED
-------------------------
The repeated website parts are modularized:

includes/header.php
includes/navbar.php
includes/footer.php

Each page uses:

require "includes/header.php";
require "includes/navbar.php";

and:

require "includes/footer.php";

This avoids repeating the navbar and footer in every PHP page.


PHP DATABASE FEATURES USED
---------------------------
- PDO
- Prepared statements
- INSERT query
- SELECT query
- password_hash()
- password_verify()
- PHP sessions
- POST forms
- PHP include/require
- Basic server-side validation


PHP FORM FLOW
-------------
The forms use the same basic structure demonstrated in class:

<form method="POST" action="actions/process_contact.php">
        ↓
$_POST values
        ↓
mysqli connection
        ↓
SQL query
        ↓
MySQL database

Contact form -> process_contact.php -> INSERT into inquiries
Register form -> process_register.php -> INSERT into users
Login form -> process_login.php -> SELECT from users -> $_SESSION

Prepared statements are used with MySQLi so form values are handled safely while still following the mysqli approach taught in class.
