<?php

$conn = mysqli_connect("localhost", "root", "", "Studenthub");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>