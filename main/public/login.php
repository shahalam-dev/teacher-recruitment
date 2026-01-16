<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/AuthService.php';

$error = "";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (AuthService::login($pdo, $email, $password)) {
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid credentials. Please try again.";
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#f5f7fb;">
    <div
        style="background:#fff; padding:40px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,.06); width:100%; max-width:400px;">
        <h2 style="text-align:center; margin-bottom:20px;">Login</h2>
        <?php if ($error): ?>
            <div style="color:red; margin-bottom:15px; text-align:center;"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div style="margin-bottom:15px;">
                <label>Email Address</label>
                <input type="email" name="email" required
                    style="width:100%; padding:10px; margin-top:5px; border:1px solid #e5e7eb; border-radius:8px;">
            </div>
            <div style="margin-bottom:20px;">
                <label>Password</label>
                <input type="password" name="password" required
                    style="width:100%; padding:10px; margin-top:5px; border:1px solid #e5e7eb; border-radius:8px;">
            </div>
            <button type="submit"
                style="width:100%; background:#2563eb; color:#fff; border:none; padding:12px; border-radius:10px; cursor:pointer; font-weight:600;">Sign
                In</button>
        </form>
    </div>
    <!-- Custom Scripts -->
    <script src="assets/form.js"></script>
</body>

</html>