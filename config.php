<?php
$host = "notes-app-db.c54g68aewzak.us-east-2.rds.amazonaws.com";
$dbname = "notesapp";
$username = "admin";
$password = "YOUR_DB_PASSWORD_HERE";
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}
?>
