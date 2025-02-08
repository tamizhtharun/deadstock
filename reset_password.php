<?php
session_start();
require_once 'db_connection.php';
require_once 'messages.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify token
    $verify_token_query = "SELECT *, CASE 
        WHEN reset_token IS NULL THEN 'invalid'
        WHEN reset_token_expires < NOW() THEN 'expired'
        ELSE 'valid'
    END as token_status 
    FROM user_login 
    WHERE reset_token = ?";
    
    $stmt = $pdo->prepare($verify_token_query);
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user || $user['token_status'] !== 'valid') {
        $message = $user['token_status'] === 'expired' 
            ? 'Your password reset link has expired. Please request a new one.' 
            : 'Invalid password reset link. Please request a new password reset.';
        
        $_SESSION['error_message'] = $message;
        $_SESSION['message_type'] = 'error';
        header("Location: index.php?showLoginModal=true");
        exit();
    }

    // Server-side validation
    if (strlen($new_password) < 8 || 
        !preg_match("/[A-Z]/", $new_password) || 
        !preg_match("/[a-z]/", $new_password) || 
        !preg_match("/[0-9]/", $new_password)) {
        $_SESSION['error_message'] = 'Password does not meet requirements.';
        header("Location: reset_password.php?token=$token");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = 'Passwords do not match.';
        header("Location: reset_password.php?token=$token");
        exit();
    }

    $email = $user['user_email'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in all relevant tables
    $pdo->beginTransaction();

    try {
        // Update user_login table
        $update_user_login = "UPDATE user_login SET user_password = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_email = ?";
        $stmt = $pdo->prepare($update_user_login);
        $stmt->execute([$hashed_password, $email]);

        // Update users table
        $update_users = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $pdo->prepare($update_users);
        $stmt->execute([$hashed_password, $email]);

        // Update sellers table
        $update_sellers = "UPDATE sellers SET seller_password = ? WHERE seller_email = ?";
        $stmt = $pdo->prepare($update_sellers);
        $stmt->execute([$hashed_password, $email]);

        $pdo->commit();
        $_SESSION['success_message'] = 'Password reset successful. Please login with your new password.';
        $_SESSION['message_type'] = 'success';
        header("Location: index.php?showLoginModal=true");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'An error occurred while resetting your password. Please try again.';
        header("Location: reset_password.php?token=$token");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Deadstock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;600&display=swap');

        :root {
            --apple-blue: #007AFF;
            --apple-red: #FF3B30;
            --apple-green: #34C759;
            --apple-gray: #8E8E93;
            --apple-light-gray: #F2F2F7;
        }

        body {
            font-family: -apple-system, 'SF Pro Display', system-ui, sans-serif;
            background-color: #FBFBFD;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #1D1D1F;
            line-height: 1.47059;
            letter-spacing: -0.022em;
        }

        .reset-container {
            width: 100%;
            max-width: 430px;
            margin: 0 auto;
            perspective: 1000px;
        }

        .reset-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo img {
            max-width: 160px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .form-title {
            font-size: 28px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 0.5rem;
            color: #1D1D1F;
        }

        .form-subtitle {
            font-size: 17px;
            text-align: center;
            color: #86868B;
            margin-bottom: 2.5rem;
            font-weight: 400;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            height: 52px;
            border: 1px solid #D2D2D7;
            border-radius: 12px;
            padding: 0 16px;
            font-size: 17px;
            transition: all 0.2s ease;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
            background-color: #fff;
        }

        .form-floating label {
            padding: 0.75rem 1rem;
            color: #86868B;
        }

        .password-requirements {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 14px;
            padding: 1.25rem;
            margin-top: 1.5rem;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: #86868B;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .requirement:last-child {
            margin-bottom: 0;
        }

        .requirement i {
            font-size: 12px;
            color: var(--apple-gray);
            transition: all 0.2s ease;
        }

        .requirement.valid {
            color: var(--apple-green);
        }

        .requirement.valid i {
            color: var(--apple-green);
        }

        .error-message {
            background-color: rgba(255, 59, 48, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: var(--apple-red);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-reset {
            background-color: var(--apple-blue);
            border: none;
            color: white !important;
            padding: 12px;
            font-size: 17px;
            font-weight: 600;
            border-radius: 12px;
            width: 100%;
            margin-top: 2rem;
            transition: all 0.2s ease;
        }

        .btn-reset:hover {
            background-color: #0071EB;
            transform: translateY(-1px);
            color: white !important;
        }

        .btn-reset:active {
            background-color: #0068D9;
            transform: translateY(1px);
        }

        .password-message {
            display: none;
            font-size: 14px;
            padding: 8px;
            margin-top: 8px;
            border-radius: 8px;
            text-align: center;
        }

        .password-message.success {
            background-color: rgba(52, 199, 89, 0.1);
            color: var(--apple-green);
            display: block;
        }

        .password-message.error {
            background-color: rgba(255, 59, 48, 0.1);
            color: var(--apple-red);
            display: block;
        }

        @media (max-width: 480px) {
            .reset-form {
                padding: 2rem;
                border-radius: 16px;
            }

            .form-title {
                font-size: 24px;
            }

            .form-subtitle {
                font-size: 15px;
            }

            .form-control {
                height: 48px;
                font-size: 16px;
            }

            .password-requirements {
                padding: 1rem;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reset-form {
            animation: fadeIn 0.6s ease-out;
        }

        .requirement i {
            transition: transform 0.2s ease;
        }

        .requirement.valid i {
            transform: scale(1.1);
        }

        .form-control:focus-visible {
            outline: none;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--apple-light-gray);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--apple-gray);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6E6E73;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-form">
            <div class="logo">
                <img src="assets/uploads/<?php echo $logo; ?>" alt="Deadstock Logo">
            </div>
            
            <h1 class="form-title">Reset Password</h1>
            <p class="form-subtitle">Enter a new password for your account</p>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <form id="resetForm" method="POST" action="reset_password.php" novalidate>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <div class="form-floating">
                    <input type="password" 
                           class="form-control" 
                           id="new_password" 
                           name="new_password" 
                           placeholder="New Password"
                           required>
                    <label for="new_password">New Password</label>
                </div>

                <div class="form-floating">
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Confirm Password"
                           required>
                    <label for="confirm_password">Confirm Password</label>
                </div>

                <div id="passwordMessage" class="password-message"></div>

                <div class="password-requirements">
                    <div class="requirement" id="length">
                        <i class="fas fa-check-circle"></i>
                        At least 8 characters
                    </div>
                    <div class="requirement" id="uppercase">
                        <i class="fas fa-check-circle"></i>
                        One uppercase letter
                    </div>
                    <div class="requirement" id="lowercase">
                        <i class="fas fa-check-circle"></i>
                        One lowercase letter
                    </div>
                    <div class="requirement" id="number">
                        <i class="fas fa-check-circle"></i>
                        One number
                    </div>
                </div>

                <button type="submit" class="btn btn-reset">
                    Reset Password
                </button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const messageDiv = document.getElementById('passwordMessage');
        let isValid = true;

        // Reset message
        messageDiv.className = 'password-message';
        messageDiv.textContent = '';

        // Check password requirements
        if (password.length < 8 || 
            !/[A-Z]/.test(password) || 
            !/[a-z]/.test(password) || 
            !/[0-9]/.test(password)) {
            isValid = false;
            messageDiv.textContent = 'Password does not meet all requirements.';
            messageDiv.classList.add('error');
        }

        // Check if passwords match
        if (password !== confirmPassword) {
            isValid = false;
            messageDiv.textContent = 'Passwords do not match.';
            messageDiv.classList.add('error');
        }

        if (!isValid) {
            e.preventDefault();
        } else {
            messageDiv.textContent = 'Password requirements met!';
            messageDiv.classList.add('success');
        }
    });

    // Real-time password validation
    document.getElementById('new_password').addEventListener('input', function(e) {
        const password = e.target.value;
        const messageDiv = document.getElementById('passwordMessage');
        
        // Update requirement indicators
        document.getElementById('length').classList.toggle('valid', password.length >= 8);
        document.getElementById('uppercase').classList.toggle('valid', /[A-Z]/.test(password));
        document.getElementById('lowercase').classList.toggle('valid', /[a-z]/.test(password));
        document.getElementById('number').classList.toggle('valid', /[0-9]/.test(password));

        // Show real-time message
        if (password.length > 0) {
            if (password.length >= 8 && 
                /[A-Z]/.test(password) && 
                /[a-z]/.test(password) && 
                /[0-9]/.test(password)) {
                messageDiv.textContent = 'Password meets all requirements!';
                messageDiv.className = 'password-message success';
            } else {
                messageDiv.textContent = 'Password does not meet all requirements.';
                messageDiv.className = 'password-message error';
            }
        } else {
            messageDiv.className = 'password-message';
            messageDiv.textContent = '';
        }
    });

    // Check password match in real-time
    document.getElementById('confirm_password').addEventListener('input', function(e) {
        const password = document.getElementById('new_password').value;
        const confirmPassword = e.target.value;
        const messageDiv = document.getElementById('passwordMessage');

        if (confirmPassword.length > 0) {
            if (password === confirmPassword) {
                messageDiv.textContent = 'Passwords match!';
                messageDiv.className = 'password-message success';
            } else {
                messageDiv.textContent = 'Passwords do not match.';
                messageDiv.className = 'password-message error';
            }
        }
    });
</script>