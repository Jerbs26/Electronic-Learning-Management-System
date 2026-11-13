<?php
$servername = "localhost";
$username   = "u961483968_elmsdb";
$password   = "u961483968_Kelmsdb";
$dbname     = "u961483968_elmsdb";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
