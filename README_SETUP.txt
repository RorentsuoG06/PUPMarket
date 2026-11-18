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

Use your registered @iskolarngbayan.pup.edu.ph email, student number, and password.

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


IMPORTANT NOTE ABOUT PRODUCTS
-----------------------------
The current Post an Item feature remains frontend/localStorage based,
because the professor's three listed requirements specifically require
database POST/query functionality for:

1. Contact Us
2. Register
3. Login

Products can be converted to MySQL in a later phase if required.

The existing product search, filter, sort, modal, local images, and
frontend Post an Item behavior are preserved.


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
