<?php
session_start();

// Include database configuration
require_once 'config.php';

// Check if $pdo is defined (for debugging)
if (!isset($pdo)) {
    die("Database connection failed. Please check your config.php file.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'admin_login') {
        $username = $_POST['adminUsername'];
        $password = $_POST['adminPassword'];
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            // Modified condition to handle both plain text and hashed passwords
            if ($admin && ($password === $admin['password'] || password_verify($password, $admin['password']))) {
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['user_type'] = 'admin';
                header('Location: admindashboard.php');
                exit();
            } else {
                header('Location: login.html?error=admin_login_failed');
                exit();
            }
        } catch(PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            header('Location: login.html?error=database_error');
            exit();
        }
    }
    
    elseif ($action == 'user_login') {
        $email = $_POST['userEmail'];
        $password = $_POST['userPassword'];
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_name'] = $user['nama_lengkap'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'user';
                header('Location: lowongan.php');
                exit();
            } else {
                header('Location: login.html?error=user_login_failed');
                exit();
            }
        } catch(PDOException $e) {
            error_log("User login error: " . $e->getMessage());
            header('Location: login.html?error=database_error');
            exit();
        }
    }
    
    elseif ($action == 'user_register') {
        $nama = $_POST['regName'];
        $email = $_POST['regEmail'];
        $phone = $_POST['regPhone'];
        $password = $_POST['regPassword'];
        $confirm_password = $_POST['regConfirmPassword'];
        
        if ($password !== $confirm_password) {
            header('Location: login.html?error=password_mismatch');
            exit();
        }
        
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                header('Location: login.html?error=email_exists');
                exit();
            }
            
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$nama, $email, $hashed_password])) {
                header('Location: login.html?success=registration_success');
                exit();
            } else {
                header('Location: login.html?error=registration_failed');
                exit();
            }
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            header('Location: login.html?error=database_error');
            exit();
        }
    }
}
?>