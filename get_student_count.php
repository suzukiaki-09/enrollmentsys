<?php
include '../../config/db.php'; // Include database connection

// Validate 'year_level' from the request
$year_level = isset($_GET['year_level']) ? $conn->real_escape_string($_GET['year_level']) : '';

if (!empty($year_level)) {
    // Query to count students based on the selected year level
    $query = "SELECT COUNT(*) as count FROM student WHERE year_level = '$year_level'";
    $result = $conn->query($query);

    if ($result) {
        $data = $result->fetch_assoc();
        echo (int) $data['count']; // Return only the count as plain text
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Error: Invalid year level";
}

// Close the database connection
$conn->close();
?>
