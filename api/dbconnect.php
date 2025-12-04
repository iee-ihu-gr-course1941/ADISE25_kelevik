<?php
$host = 'localhost';
$db = 'xeri_db';
$user = 'root';
$pass = '';

if(gethostname()=='users.iee.ihu.gr') {
    $user = 'IT123456'; 
    $pass = '******';   
}

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}
?>