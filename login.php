<?php
include 'db_connection.php';

$error_message = ""; // Variable to store error messages



// include 'index.php';
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
            'email' => $user['user_email'],
            'role' => 'admin'
        ];
        // Verify password
        if (password_verify($password, $user['user_password'])) {
            // Handle user roles
            switch ($user['user_role']) {
                case 'admin': 
                    // Start session
                    session_start();
                    $_SESSION['admin_session'] = $user;
                    $_SESSION['admin_role'] = 'admin';
                    header("Location: admin/index.php");
                    exit();
                case 'user':
                    // Start session
                    // session_start();
                    $sql_user_data = "SELECT * FROM users WHERE email = ?";
                            $stmt_user_data = $conn->prepare($sql_user_data);
                            $stmt_user_data->bind_param("s", $email);
                            $stmt_user_data->execute();
                            $result_user_data = $stmt_user_data->get_result();
                
                            if ($result_user_data->num_rows > 0) {
                                $user_data_ = $result_user_data->fetch_assoc();}

                                session_start();
                    // $_SESSION['user_session'] = $user_data;
                    $_SESSION['user_session'] = $user_data_;
                    // $_SESSION['user_email'] = $user['user_email'];
                    // $_SESSION['user_role'] = 'user';
                    $_SESSION['loggedin'] = true; // Store user role
                    header("Location: index.php");
                    exit();
                case 'seller':
                    // Check seller status
                    $sql_seller = "SELECT seller_status FROM sellers WHERE seller_email = ?";
                    $stmt_seller = $conn->prepare($sql_seller);
                    $stmt_seller->bind_param("s", $email);
                    $stmt_seller->execute();
                    $result_seller = $stmt_seller->get_result();
                
                    if ($result_seller->num_rows > 0) {
                        $seller = $result_seller->fetch_assoc();
                        
                        // Check if seller account is active
                        if ($seller['seller_status'] == 0) {
                            $error_message = "Your seller account is inactive.";
                        } else {
                            // Retrieve complete seller data
                            $sql_seller_data = "SELECT * FROM sellers WHERE seller_email = ?";
                            $stmt_seller_data = $conn->prepare($sql_seller_data);
                            $stmt_seller_data->bind_param("s", $email);
                            $stmt_seller_data->execute();
                            $result_seller_data = $stmt_seller_data->get_result();
                
                            if ($result_seller_data->num_rows > 0) {
                                $seller_data = $result_seller_data->fetch_assoc(); // Fetch seller data

                                // Start session
                                session_start();

                                // Store seller data in session
                                $_SESSION['seller_session'] = $seller_data;
                                $_SESSION['seller_role'] = 'seller'; // Store seller role
                                
                                header("Location: seller/index.php");
                                exit();
                            } else {
                                $error_message = "Seller account not found.";
                            }
                            $stmt_seller_data->close();
                        }
                    } else {
                        $error_message = "Seller account not found.";
                    }
                    $stmt_seller->close();
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
    $_SESSION['error_message'] = $error_message; // Save error message to session
    header("Location: index.php"); // Redirect to index.php
    exit();
}


// If there's an error message, display it as an alert
// if ($error_message) {
//     echo "<script>alert('$error_message');
//     window.location.href = 'index.php';
//     </script>";
// }
?>