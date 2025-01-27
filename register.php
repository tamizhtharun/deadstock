<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $user_gst = $_POST['user_gst'];

    // Insert user into the users table
    $sql = "INSERT INTO users (username, phone_number, email, password, user_gst) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $phone_number, $email, $password, $user_gst);

    if ($stmt->execute()) {
        // Now insert into user_login table
        $user_role = 'user'; // Set user role to 'user'
        $sql_login = "INSERT INTO user_login (user_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?)";
        $stmt_login = $conn->prepare($sql_login);
        $stmt_login->bind_param("ssss", $username, $email, $password, $user_role);

        if ($stmt_login->execute()) {
            echo '<script>
                alert("Registration successful");
                window.location.href = "index.php";
              </script>';
        } else {
            echo "Error: " . $stmt_login->error;
        }

        $stmt_login->close();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>