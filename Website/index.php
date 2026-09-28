<?php require_once "Includes/header.php";
require_once "Includes/nav.php";
require_once "Includes/auth.php";



if(isset($_GET["logout"])){
    session_destroy();
    header("Location: index.php");
    exit();
}

?>
<main class="main">
    <header class="main-header">
        <h1> Options </h1>
        <span class="subtitle">Customer & Stock Screens</span>
    </header>

    <section class="content">
    <h2>Welcome</h2>
    <p>Allocated screens.</p>
    </section>

    <footer class="main-footer">@ <?php echo date("Y"); ?> Opticians.</footer>
</main>
<?php require_once "Includes/footer.php"; ?>
        