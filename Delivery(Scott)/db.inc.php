<!--Student Name: Scott Cardiff-->
<!--Purpose of Screen: The database Connector-->
<!--Student ID: C00311728-->
<!--Name of Screen: Add Eye Test-->
<!--Date: 26/02/2026-->



<?php
$hostname = 'localhost:3306';   // name of host or ip address
$username = 'opticians';     // MySQL username - Students need to change this to their own
$password = '18Z%i1OVlqfehh#v';     // MySQL Password - Students need to change this to their own

$dbname = 'opticalcare_';       // database Name - Students need to change this to their own

$con = mysqli_connect($hostname, $username, $password, $dbname);

if (!$con)
{
    die ("Failed to connect to MySQL: " . mysqli_connect_error());
}
?>

