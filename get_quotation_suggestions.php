<?php
// Include your database connection
include 'config.php';

// Check if the name parameter is set in the AJAX request
if (isset($_POST['name'])) {
    // Retrieve the name parameter from the AJAX request
    $name = $_POST['name'];

    // Perform a database query to fetch matching records based on the provided name
    $query = "SELECT name, company_name, tax_number, address, email, phone FROM quotations WHERE name LIKE ? LIMIT 5";
    $stmt = $conn->prepare($query);
    $likeName = "%" . $name . "%";
    $stmt->bind_param('s', $likeName);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = [
            'label' => $row['name'],
            'value' => $row['name'],
            'company_name' => $row['company_name'],
            'tax_number' => $row['tax_number'],
            'address' => $row['address'],
            'email' => $row['email'],
            'phone' => $row['phone']
        ];
    }

    // Return the data as JSON
    echo json_encode($suggestions);
}
?>