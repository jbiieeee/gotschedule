<?php
require_once '../includes/config.php';

echo "Updating database for Collaborations...\n";

$updates = [
    // Table to store task collaborations
    "CREATE TABLE IF NOT EXISTS `task_collaborators` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($updates as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Success.\n";
    } else {
        echo "Error: " . mysqli_error($conn) . "\n";
    }
}
echo "Migration complete.\n";
?>
