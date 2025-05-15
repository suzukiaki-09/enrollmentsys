<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "enrollment";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = 97; // Example student ID

$sql = "SELECT birth_certificate, highschool_card, good_moral, marriage_certificate, department, entrance_exam, honorable_dis, clearance, clearanace_file FROM tbl_new_student WHERE id = $student_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Requirement</th><th>File</th></tr>";
    while($row = $result->fetch_assoc()) {
        foreach ($row as $requirement => $file) {
            echo "<tr><td>" . ucfirst(str_replace('_', ' ', $requirement)) . "</td><td>";
            if (strpos($file, '.pdf') !== false) {
                echo "<a href='uploads/$file' target='_blank'>View PDF</a>";
            } else {
                echo "<img src='uploads/$file' alt='$requirement' width='100'>";
            }
            echo "</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "0 results";
}
$conn->close();
?>
