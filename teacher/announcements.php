<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Fetch all announcements
$sql = "SELECT * FROM announcements ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Teacher Panel</a>
    <div class="ml-auto">
      <a href="dashboard.php" class="btn btn-light">Back</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <h2>Announcements</h2>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                <small class="float-end"><?php echo $row['created_at']; ?></small>
            </div>
            <div class="card-body">
                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
            </div>
        </div>
    <?php endwhile; ?>

</div>

</body>
</html>
