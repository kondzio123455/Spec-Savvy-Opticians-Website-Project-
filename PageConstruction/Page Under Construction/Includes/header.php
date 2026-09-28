<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opticians</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">

    <!-- TOP HEADER (logo area) -->
    <header class="main-header">
        <div class="logo-area">
            <a href="customer_add.php">
                <img src="spec savvy logo.png" alt="Spec Savvy">
            </a>
        </div>
    </header>

    <!-- NAVBAR -->
    <nav class="navbar">
                <ul class="dropdown-menu">
                    <li><a href="customer_add.php">Add Customer</a></li>
                    <li><a href="customer_amend.php">Amend Customer</a></li>
                    <li><a href="customer_delete.php">Delete Customer</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="addStockitem.php">Stock</a></li>
                </ul>
    </nav>

    <!-- Page content wrapper -->
    <main class="main">