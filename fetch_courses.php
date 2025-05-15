<?php
include '../config/db.php';

$block = $_GET['block'] ?? '';

if ($block) {
    $stmt = $conn->prepare("SELECT course_code, description, credit_units, start_time, end_time, days, pre_requisite FROM courses WHERE block = ?");
    $stmt->bind_param("s", $block);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode($courses);
} else {
    echo json_encode([]);
}
?>
