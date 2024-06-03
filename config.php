<?php
// Set the default timezone to Australia
date_default_timezone_set('Australia/Sydney');

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'invoice');
define('QUTATION_PREFIX', 'QT2024-');
define('CURRENCY_PREFIX', '$');
define('CURRENCY_SUFIX', ' AUD');


define('COMPANY', ' Bitbull Technologies');
define('CONTACT_EMAIL', ' contac@bitbulltech.com');
define('PHONE', ' 9876543210');
define('TAXNO', ' 1111111111');
define('ADDRESS', '#123, Sec 74, Mohali');


define('TERMS', 'Quotation valid for 10 days from the quotation date.');


// Base URL of your application
define('BASE_URL', 'http://YourDomainName.com/quotation');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Additional configuration (if needed)
?>
