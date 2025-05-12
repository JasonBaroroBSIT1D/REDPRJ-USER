<?php
require_once "config.php";

// Initialize variables
$error = "";
$success = "";

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password.";
        } else {
            // Prepare a select statement
            $sql = "SELECT id, student_id, fullname, password FROM users WHERE student_id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                
                // Set parameters
                $param_username = $username;
                
                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    // Check if username exists
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $student_id, $fullname, $hashed_password);
                        if (mysqli_stmt_fetch($stmt)) {
                            if ($password === $student_id) { // Since password is same as student ID
                                // Password is correct, start a new session
                                session_start();
                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["student_id"] = $student_id;
                                $_SESSION["fullname"] = $fullname;
                                
                                // Redirect user to index page
                                header("location: index.php");
                                exit;
                            } else {
                                $error = "Invalid password.";
                            }
                        }
                    } else {
                        $error = "No account found with that student ID.";
                    }
                } else {
                    $error = "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Red Cross Council</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
      background-color: #f5f5f5;
      background-image: url('/api/placeholder/1920/1080');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      position: relative;
    }
    
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(220, 53, 69, 0.7) 0%, rgba(173, 20, 30, 0.4) 100%);
      z-index: 0;
    }
    
    .login-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 2rem 3rem;
      border-radius: 16px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.2);
      width: 100%;
      max-width: 760px;
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
      display: flex;
      flex-direction: row;
    }
    
    /* Decorative elements */
    .decorative-circle {
      position: absolute;
      background: rgba(211, 47, 47, 0.1);
      border-radius: 50%;
      z-index: 0;
    }
    
    .circle-1 {
      width: 150px;
      height: 150px;
      bottom: -50px;
      right: -30px;
    }
    
    .circle-2 {
      width: 80px;
      height: 80px;
      top: -20px;
      right: 30%;
    }
    
    .circle-3 {
      width: 40px;
      height: 40px;
      bottom: 30px;
      right: 40%;
      opacity: 0.6;
    }
    
    .wave-pattern {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 50px;
      background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V100V0C120,16,220,32,321.39,56.44z' fill='rgba(211, 47, 47, 0.07)'%3E%3C/path%3E%3C/svg%3E");
      background-size: cover;
      opacity: 0.8;
      z-index: 0;
    }
    
    .cross-pattern {
      position: absolute;
      top: 20px;
      right: 20px;
      width: 100px;
      height: 100px;
      background: url("data:image/svg+xml,%3Csvg width='50' height='50' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M10,0 L10,24 M0,12 L24,12' stroke='rgba(211, 47, 47, 0.1)' stroke-width='2'/%3E%3C/svg%3E");
      background-repeat: repeat;
      opacity: 0.5;
      z-index: 0;
    }
    
    .left-side {
      flex: 0 0 35%;
      padding-right: 2rem;
      border-right: 1px solid #e9ecef;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      position: relative;
      z-index: 2;
    }
    
    .right-side {
      flex: 0 0 65%;
      padding-left: 2rem;
      position: relative;
      z-index: 2;
    }
    
    .login-container:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background-color: #d32f2f;
      border-radius: 16px 16px 0 0;
    }
    
    .logo-container {
      text-align: center;
      margin-bottom: 1rem;
      position: relative;
    }
    
    .logo {
      width: 160px;
      height: 160px;
      object-fit: cover;
      border-radius: 50%;
      margin-bottom: 1.5rem;
      box-shadow: 0 8px 20px rgba(211, 47, 47, 0.25);
      border: 4px solid white;
      transition: all 0.4s ease;
      position: relative;
      z-index: 2;
    }
    
    .logo:hover {
      transform: scale(1.08) rotate(5deg);
      box-shadow: 0 12px 30px rgba(211, 47, 47, 0.4);
    }
    
    .logo::after {
      content: '';
      position: absolute;
      top: -8px;
      left: -8px;
      right: -8px;
      bottom: -8px;
      background: rgba(211, 47, 47, 0.1);
      border-radius: 50%;
      z-index: -1;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .logo:hover::after {
      opacity: 1;
    }
    
    .logo-glow {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: radial-gradient(circle at center, rgba(211, 47, 47, 0.2) 0%, rgba(211, 47, 47, 0) 70%);
      top: 0;
      left: 0;
      transform: scale(1.2);
      z-index: 1;
    }

    /* Adjusted logo size */
    .logo-container {
      padding-top: 0.5rem;
    }
    
    h2 {
      color: #d32f2f;
      font-weight: 700;
      margin-bottom: 0.5rem;
      text-align: center;
    }
    
    .subtitle {
      color: #6c757d;
      font-size: 0.95rem;
      text-align: left;
      margin-bottom: 1.5rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-control {
      height: 54px;
      padding: 10px 15px 10px 45px;
      border: 2px solid #e9ecef;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s;
      background-color: #f8f9fa;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .form-control:focus {
      border-color: #d32f2f;
      background-color: #fff;
      box-shadow: 0 0 0 0.25rem rgba(211, 47, 47, 0.15);
    }
    
    .input-group {
      position: relative;
    }
    
    .input-icon {
      position: absolute;
      top: 17px;
      left: 15px;
      color: #adb5bd;
      transition: color 0.3s;
      z-index: 5;
    }
    
    .form-control:focus + .input-icon,
    .input-group:hover .input-icon {
      color: #d32f2f;
    }
    
    .btn-red {
      background-color: #d32f2f;
      color: white;
      border: none;
      height: 54px;
      border-radius: 12px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      z-index: 1;
    }
    
    .btn-red:before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: all 0.6s;
      z-index: -1;
    }
    
    .btn-red:hover:before {
      left: 100%;
    }
    
    .btn-red:hover, .btn-red:focus {
      background-color: #b71c1c;
      box-shadow: 0 8px 20px rgba(211, 47, 47, 0.25);
      transform: translateY(-2px);
    }
    
    .btn-red:active {
      transform: translateY(0);
    }
    
    .error-msg {
      color: #d32f2f;
      text-align: center;
      margin-bottom: 1.5rem;
      padding: 12px;
      background-color: rgba(211, 47, 47, 0.08);
      border-radius: 10px;
      font-size: 0.95rem;
      border-left: 4px solid #d32f2f;
    }
    
    .success-msg {
      color: #388e3c;
      text-align: center;
      margin-bottom: 1.5rem;
      padding: 12px;
      background-color: rgba(56, 142, 60, 0.08);
      border-radius: 10px;
      font-size: 0.95rem;
      border-left: 4px solid #388e3c;
    }
    
    .forgot-password {
      text-align: right;
      margin-bottom: 1.5rem;
    }
    
    .forgot-password a {
      color: #6c757d;
      font-size: 0.95rem;
      text-decoration: none;
      transition: all 0.2s;
      position: relative;
    }
    
    .forgot-password a:hover {
      color: #d32f2f;
    }
    
    .forgot-password a:after {
      content: '';
      position: absolute;
      width: 0;
      height: 1px;
      bottom: -2px;
      left: 0;
      background-color: #d32f2f;
      transition: width 0.3s;
    }
    
    .forgot-password a:hover:after {
      width: 100%;
    }
    
    .footer-text {
      text-align: center;
      font-size: 0.85rem;
      color: #adb5bd;
      margin-top: 2rem;
      padding-top: 1rem;
      border-top: 1px solid #f1f3f5;
    }
    
    .toggle-password {
      position: absolute;
      right: 15px;
      top: 17px;
      cursor: pointer;
      color: #adb5bd;
      transition: color 0.3s;
    }
    
    .toggle-password:hover {
      color: #d32f2f;
    }

    .remember-me {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .remember-me label {
      margin-left: 8px;
      color: #6c757d;
      font-size: 0.95rem;
      cursor: pointer;
    }

    .form-check-input:checked {
      background-color: #d32f2f;
      border-color: #d32f2f;
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        padding: 2rem 1.5rem;
        max-width: 440px;
      }
      
      .left-side {
        flex: 0 0 auto;
        padding-right: 0;
        border-right: none;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
      }
      
      .right-side {
        flex: 0 0 auto;
        padding-left: 0;
      }
      
      .circle-1 {
        width: 100px;
        height: 100px;
        bottom: -30px;
        right: -20px;
      }
      
      .circle-2 {
        width: 60px;
        height: 60px;
        top: -15px;
        right: 20%;
      }
      
      .cross-pattern {
        width: 60px;
        height: 60px;
      }
    }

    /* Animation for the messages */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .error-msg, .success-msg {
      animation: fadeIn 0.3s ease-out forwards;
    }
    
    /* Background pattern styling */
    .background-pattern {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.2'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      pointer-events: none;
      z-index: -1;
      opacity: 0.8;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="decorative-circle circle-1"></div>
    <div class="decorative-circle circle-2"></div>
    <div class="decorative-circle circle-3"></div>
    <div class="wave-pattern"></div>
    <div class="cross-pattern"></div>
    
    <div class="left-side">
      <div class="logo-container">
        <div class="logo-glow"></div>
        <img src="Red Cross.jpg" alt="Red Cross Logo" class="logo">
        
      </div>
    </div>
    
    <div class="right-side">
      <h2>Red Cross Council</h2>
      <p class="subtitle">Sign in to access your account</p>
      
      <?php if (!empty($error)): ?>
        <div class="error-msg">
          <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success-msg">
          <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      
      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="form-group">
          <div class="input-group">
            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
            <i class="fas fa-user input-icon"></i>
          </div>
        </div>
        
        <div class="form-group">
          <div class="input-group">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
            <i class="fas fa-lock input-icon"></i>
            <i class="fas fa-eye toggle-password" id="togglePassword"></i>
          </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
          <div class="remember-me">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember me</label>
          </div>
          <div class="forgot-password">
            <a href="forgot-password.php">Forgot Password?</a>
          </div>
        </div>
        
        <button type="submit" class="btn btn-red w-100">
          <i class="fas fa-sign-in-alt"></i> 
          <span>Login</span>
        </button>
        
        <div class="footer-text">
          &copy; <?= date('Y') ?> BSIT2A. All rights reserved.
        </div>
      </form>
    </div>
  </div>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('fa-eye');
      this.classList.toggle('fa-eye-slash');
    });
    
    // Form validation with visual feedback
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input[required]');
    
    inputs.forEach(input => {
      input.addEventListener('blur', function() {
        if (this.value.trim() === '') {
          this.style.borderColor = '#dc3545';
          this.classList.add('is-invalid');
        } else {
          this.style.borderColor = '#198754';
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        }
      });
      
      input.addEventListener('focus', function() {
        this.style.borderColor = '#d32f2f';
        this.classList.remove('is-invalid');
      });
    });
    
    form.addEventListener('submit', function(e) {
      let hasError = false;
      
      inputs.forEach(input => {
        if (input.value.trim() === '') {
          input.style.borderColor = '#dc3545';
          input.classList.add('is-invalid');
          hasError = true;
        }
      });
      
      if (hasError) {
        e.preventDefault();
        // Gentle shake animation for form on error
        form.animate([
          { transform: 'translateX(0)' },
          { transform: 'translateX(-5px)' },
          { transform: 'translateX(5px)' },
          { transform: 'translateX(-5px)' },
          { transform: 'translateX(0)' }
        ], {
          duration: 300,
          easing: 'ease-in-out'
        });
      }
    });
  });
  </script>
</body>
</html>