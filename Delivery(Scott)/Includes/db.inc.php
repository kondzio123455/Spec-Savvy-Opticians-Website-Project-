<?php
$hostname='localhost:3306';
$username='opticians';
$password='18Z%i1OVlqfehh#v';

$dbname='opticalcare_';

$con=mysqli_connect($hostname, $username, $password, $dbname);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}