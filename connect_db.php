<?php
require_once 'vendor/autoload.php';
session_start();

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'test');
 
// init configuration
// $clientID = '328613557058-2vlgubore5g2r1demrrua2o7isvl9n09.apps.googleusercontent.com';
$clientID = '1008299941069-hli9f86obona315uo3hgkb1j2m8gokgp.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/test/dashboard.php';

// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>