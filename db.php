<?php
$host = "localhost";
$user = "root";       
$pass = "";           
$dbname = "lifedrop_db";


$conn = new mysqli($host, $user, $pass, $dbname);


if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>