<?php
$host="localhost:3306";
$user="root";
$pass="";
$database="employee_management";
$conn = mysqli_connect($host,$user,$pass,$database);
if(!$conn){
    die("Database connection failed: " . mysqli_connect_error());
}
?>