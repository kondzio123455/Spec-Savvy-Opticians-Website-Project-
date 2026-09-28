<!-- Kamile Kacinskaite -->
<!-- C00312390 -->
<!-- Project Add New Supplier Screen -->  
<!-- The user supplies details about a new supplier and when they confirm all details are correct a new record is added to the Supplier Table -->
<!-- 12/02/26 -->

<!-- file to connect to the database and find the supplier table -->
<?php
$hostname='localhost:3306';
$username='opticians';
$password='18Z%i1OVlqfehh#v';

$dbname='opticalcare_';

$con=mysqli_connect($hostname, $username, $password, $dbname);

if (!$con) // if the connection to database fails display error message 
{
    die("Connection failed: " . mysqli_connect_error());
}