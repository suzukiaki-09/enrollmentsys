<?php
include '../config/db.php';

$block = $_GET['block'] ?? '';

if ($block) {
    $stmt = $conn->prepare("
        SELECT s.course_code, s.description, s.lec_hours, s.lab_hours, s.credit_units, nb.start_time, nb.end_time, nb.days, s.pre_requisite, s.faculty_id, s.instructor
        FROM subjects s
        LEFT JOIN schedules nb ON nb.subject_id = s.id
        WHERE nb.block = ?
    ");
    $stmt->bind_param('s', $block);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($courses);
} else {
    echo json_encode([]);
}
?>
