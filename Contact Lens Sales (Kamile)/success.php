<!-- Kamile Kacinskaite -->
<!-- C00312390 -->
<!-- Project Contact Lens Sales Screen -->  
<!-- This form is used when a customer wishes to purchase a supply of disposable contact lenses -->
<!-- 19/02/26 -->

<?php
    $hostname='localhost:3306';
    $username='opticians';
    $password='18Z%i1OVlqfehh#v';

    $dbname='opticalcare_';

    $conn=mysqli_connect($hostname, $username, $password, $dbname);

    if (!$conn) 
    {
        die("Connection failed: " . mysqli_connect_error());
    }

?>