<?php
//login.php
include 'db_connection.php';

$error_message = ""; // Variable to store error messages
$user_data = []; // Array to store user data

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve user from the database
    $sql = "SELECT * FROM user_login WHERE user_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $user_data = [
            'id' => $user['id'],
            'name' => $user['user_name'],
            'email' => $user['user_email']
        ];

        // Modified password verification based on user role
        if ($user['user_role'] === 'seller') {
            // For sellers, verify password from sellers table
            $sql_seller = "SELECT * FROM sellers WHERE seller_email = ?";
            $stmt_seller = $conn->prepare($sql_seller);
            $stmt_seller->bind_param("s", $email);
            $stmt_seller->execute();
            $result_seller = $stmt_seller->get_result();
            
            if ($result_seller->num_rows > 0) {
                $seller = $result_seller->fetch_assoc();
                
                // Check if the seller is verified before verifying the password
                if ($seller['seller_verified'] == 0) {
                    $error_message = "Your seller account is not verified. Please check your email.";
                    $password_verified = false;
                } else {
                    $password_verified = password_verify($password, $seller['seller_password']);
                }
            } else {
                $password_verified = false;
            }
            
            $stmt_seller->close();
        } elseif ($user['user_role'] === 'user') {
            // For users, verify password from users table
            $sql_user = "SELECT * FROM users WHERE email = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("s", $email);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            
            if ($result_user->num_rows > 0) {
                $user_details = $result_user->fetch_assoc();
                
                // **Check if email is verified (status = 0) before verifying the password**
                if ($user_details['status'] == 0) {
                    $error_message = "Your email is not verified. Please check your email.";
                    $password_verified = false; // Prevent further login attempt
                } else {
                    $password_verified = password_verify($password, $user_details['password']);
                    $user_data_ = $user_details; // Store user details for session
                }
            } else {
                $password_verified = false;
            }
            $stmt_user->close();
        } else {
            // For admin, verify password from user_login table
            $password_verified = password_verify($password, $user['user_password']);
        }

        // **Ensure error message is properly displayed**
        if (isset($user_details) && $user_details['status'] == 0) {
            $password_verified = false; // Prevents login
        }

        if ($password_verified) {
            // Handle user roles
            switch ($user['user_role']) {
                case 'admin': 
                    session_start();
                    $_SESSION['admin_session'] = $user;
                    $_SESSION['admin_role'] = 'admin';
                    header("Location: admin/index.php");
                    exit();
                    case 'user':
                        session_start();
                        $_SESSION['user_session'] = $user_data_;
                        $_SESSION['user_email'] = $user['user_email'];
                        $_SESSION['user_role'] = 'user';
                        $_SESSION['loggedin'] = true;
                    
                        // Check if HTTP_REFERER is set; otherwise, use a default page
                        if (!empty($_SERVER['HTTP_REFERER'])) {
                            $referrer = $_SERVER['HTTP_REFERER'];
                    
                            // Remove only 'showLoginModal' query param, keep others
                            $referrer = preg_replace('/([?&])showLoginModal=true(&|$)/', '$1', $referrer);
                            $referrer = rtrim($referrer, '?&'); // Clean trailing ? or &
                        } else {
                            $referrer = "index.php"; // Default fallback
                        }
                    
                        header("Location: $referrer");
                        exit();
                    
                case 'seller':
                    // Check seller status
                    if ($seller['seller_status'] == '2') {
                        $error_message = "Your seller account is inactive.";
                    } else {
                        session_start();
                        $_SESSION['seller_session'] = $seller;
                        $_SESSION['seller_role'] = 'seller';
                        header("Location: seller/index.php");
                        exit();
                    }
                    break;
                default:
                    $error_message = "Invalid user role.";
            }
        } else {
            // **Ensure proper error message is displayed**
            if (empty($error_message)) {
                $error_message = "Invalid email or password.";
            }
        }
    } else {
        $error_message = "No user found with this email.";
    }

    $stmt->close();
}
$conn->close();

// If there's an error, save it to the session and redirect
if (!empty($error_message)) {
    session_start();
    $_SESSION['error_message'] = $error_message;
    header("Location: index.php?showLoginModal=true"); // Ensure modal is shown
    exit();
}
?>
