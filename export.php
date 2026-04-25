<?php
require_once 'includes/config.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$format = $_GET['format'] ?? 'csv';

// Fetch all active tasks
$query = "SELECT task_name, description, category, priority, due_date, task_time, end_time, status FROM tasks WHERE user_id = $user_id AND deleted_at IS NULL ORDER BY due_date ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Export Failed: " . mysqli_error($conn));
}

// Log Activity
logActivity($conn, $user_id, 'EXPORT_DATA', "Exported tasks in $format format");

if ($format === 'ics') {
    // iCalendar Format
    $filename = "GotSchedule_" . date('Y-m-d') . ".ics";
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//GotSchedule//NONSGML v1.0//EN\r\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $dtstart = date('Ymd\THis', strtotime($row['due_date'] . ' ' . $row['task_time']));
        $dtend = date('Ymd\THis', strtotime($row['due_date'] . ' ' . $row['end_time']));
        
        echo "BEGIN:VEVENT\r\n";
        echo "UID:" . md5($row['task_name'] . $row['due_date']) . "@gotschedule.com\r\n";
        echo "DTSTAMP:" . date('Ymd\THis\Z') . "\r\n";
        echo "DTSTART:$dtstart\r\n";
        echo "DTEND:$dtend\r\n";
        echo "SUMMARY:" . str_replace(',', '\,', $row['task_name']) . "\r\n";
        echo "DESCRIPTION:" . str_replace(["\r", "\n", ","], ["", "\\n", "\\,"], $row['description'] ?? '') . "\r\n";
        echo "CATEGORIES:" . $row['category'] . "\r\n";
        echo "END:VEVENT\r\n";
    }
    echo "END:VCALENDAR";

} else {
    // Default CSV Format
    $filename = "GotSchedule_Backup_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Task Name', 'Description', 'Category', 'Priority', 'Due Date', 'Start Time', 'End Time', 'Status']);
    
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    fclose($output);
}
exit();
?>
