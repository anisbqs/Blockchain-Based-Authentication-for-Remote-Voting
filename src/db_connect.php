<?php
$servername = "localhost";  
$username = "votinghub";         
$password = "mmuvote";             
$dbname = "electiondb";     

//Creating connection to the databasr
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
