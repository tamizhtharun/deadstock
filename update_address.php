<?php
// Include database connection
include 'db_connection.php'; // Replace with your actual database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $id = intval($_POST['id']); // ID of the address to update
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $state = htmlspecialchars(trim($_POST['state']));
    $pincode = htmlspecialchars(trim($_POST['pincode']));
    $address_type = htmlspecialchars(trim($_POST['address_type']));

    // Validate required fields
    if (empty($full_name) || empty($address) || empty($city) || empty($state) || empty($pincode) || empty($address_type)) {
        echo "All fields are required.";
        exit;
    }

    // SQL query to update the address
    $sql = "UPDATE users_addresses 
            SET full_name = ?, address = ?, city = ?, state = ?, pincode = ?, address_type = ?, updated_at = NOW()
            WHERE id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ssssssi", $full_name, $address, $city, $state, $pincode, $address_type, $id);

        // Execute the query
        if ($stmt->execute()) {
            // Redirect back to the order page or show success message
            header("Location: checkout-page.php?update=success");
            exit;
        } else {
            echo "Error updating address: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
