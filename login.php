<?php
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

    $sql = "SELECT * FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $user_result = $stmt->get_result();

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
                $password_verified = password_verify($password, $seller['seller_password']);
            } else {
                $password_verified = false;
            }
            $stmt_seller->close();
        } else {
            // For other users, verify password from user_login table
            $password_verified = password_verify($password, $user['user_password']);
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
                    $sql_user_data = "SELECT * FROM users WHERE email = ?";
                    $stmt_user_data = $conn->prepare($sql_user_data);
                    $stmt_user_data->bind_param("s", $email);
                    $stmt_user_data->execute();
                    $result_user_data = $stmt_user_data->get_result();
            
                    if ($result_user_data->num_rows > 0) {
                        $user_data_ = $result_user_data->fetch_assoc();
                    }

                    session_start();
                    $_SESSION['user_session'] = $user_data_;
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['user_role'] = 'user';
                    $_SESSION['loggedin'] = true;
                    $referrer = $_SERVER['HTTP_REFERER'];
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
            $error_message = "Invalid password.";
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
    header("Location: index.php");
    exit();
}
?>