<?php
session_start();
require_once __DIR__ . "/Includes/config.php";


if (isset($_SESSION["user"])) {
  header("Location: " . BASE_URL . "/index.php");
  exit;
}

$error = "";
$username = "";
$password = "";


$error="";
if($_SERVER["REQUEST_METHOD"]=== "POST"){
    $username = trim($_POST['username']);
    $password=$_POST['password'];
}

if($username=="admin" && $password=="admin123"){
    $_SESSION["user"]=$username;
    header("Location: " . BASE_URL . "/index.php");
    exit;
}else
{
    $error="invalid username or password";
}
require_once "Includes/header.php";
?> 

<main class="main">
  <header class="main-header">
    <h1>Start-Up Login</h1>
    <span class="subtitle">Please login to continue</span>
  </header>

  <section class="content">
    <?php if ($error): ?>
      <p class="muted"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form class="card-form" method="post">
      <label>Username
        <input type="text" name="username" required>
      </label>

      <label>Password
        <input type="password" name="password" required>
      </label>

      <div class="form-actions">
        <button type="submit">Login</button>
      </div>
    </form>
  </section>

  <footer class="main-footer">© <?php echo date("Y"); ?> Opticians</footer>
</main>

<?php require_once "Includes/footer.php"; ?>

