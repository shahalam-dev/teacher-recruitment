<?php
class AuthService {
    /**
     * Verifies user credentials and starts a secure session.
     */
    public static function login($pdo, $email, $password) {
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and password is correct
        if ($user && password_verify($password, $user['password_hash'])) {
            if (session_status() == PHP_SESSION_NONE) { session_start(); }
            
            // Prevent Session Fixation attacks
            session_regenerate_id(true); 
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            return true;
        }
        return false;
    }

    /**
     * Use this at the top of every protected UI page.
     */
    public static function checkAuth() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header("Location: login.php");
            exit();
        }
    }

    public static function logout() {
        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        session_destroy();
        header("Location: login.php");
        exit();
    }
}