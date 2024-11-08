<?php
include 'admin\inc\db_connection.php';

$error_message = ""; // Variable to store error messages

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
        // Verify password
        if (password_verify($password, $user['user_password'])) {
            // Start session and store user info
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role']; // Store user role

            // Redirect based on user role
            switch ($user['user_role']) {
                case 'user':
                    header("Location: index.php");
                    exit();
                case 'admin':
                    header("Location: admin/index.php");
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
                        if ($seller['seller_status'] == 0) {
                            $error_message = "Your seller account is inactive.";
                        } else {
                            header("Location: seller_dashboard.php");
                            exit();
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

// If there's an error message, display it as an alert
if ($error_message) {
    echo "<script>alert('$error_message');
    window.location.href = 'index.php';
    </script>";
}
?>