<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Database connection
$mysqli = new mysqli("localhost", "root", "", "red_cross_council");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

// Fetch events from database
$sql = "SELECT * FROM events WHERE status = 'approved' ORDER BY event_date ASC, start_time ASC";
$result = $mysqli->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Cross USTP Council - User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Top Navigation (always visible, replaces sidebar) -->
    <nav class="navbar navbar-expand-lg main-nav mb-4">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-cross me-2"></i>RED CROSS <span class="d-none d-md-inline ms-2">USTP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="announcements.php"><i class="fas fa-bullhorn"></i> Announcement</a></li>
                    <li class="nav-item"><a class="nav-link" href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="fas fa-user-circle"></i> Profile</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="profileModalLabel"><i class="fas fa-user-circle me-2"></i>User Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4 class="mb-1"><?php echo htmlspecialchars($_SESSION["fullname"]); ?></h4>
                        <p class="text-muted mb-0">Student ID: <?php echo htmlspecialchars($_SESSION["student_id"]); ?></p>
                    </div>
                    <div class="profile-info">
                        <div class="row mb-3">
                            <div class="col-4 fw-bold">Full Name:</div>
                            <div class="col-8"><?php echo htmlspecialchars($_SESSION["fullname"]); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-bold">Student ID:</div>
                            <div class="col-8"><?php echo htmlspecialchars($_SESSION["student_id"]); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Member Since:</div>
                            <div class="col-8"><?php echo date('F Y'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Events Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-danger text-white fw-bold d-flex align-items-center">
                <i class="fas fa-calendar-alt me-2"></i>UPCOMING EVENTS
            </div>
            <div class="card-body">
                <div class="event-list">
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $event_date = new DateTime($row['event_date']);
                            $start_time = new DateTime($row['start_time']);
                            $end_time = new DateTime($row['end_time']);
                            $status_class = $row['status'] === 'approved' ? 'bg-success' : 'bg-secondary';
                            $status_text = ucfirst($row['status']);
                    ?>
                    <div class="event-item p-3 mb-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar me-2"></i><?php echo htmlspecialchars($row['title']); ?>
                            </h5>
                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </div>
                        <div class="event-details mb-3">
                            <p class="mb-1">
                                <i class="far fa-calendar-alt me-2"></i><?php echo $event_date->format('F d, Y'); ?>
                            </p>
                            <p class="mb-1">
                                <i class="far fa-clock me-2"></i><?php echo $start_time->format('h:i A') . ' - ' . $end_time->format('h:i A'); ?>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($row['location']); ?>
                            </p>
                        </div>
                        <p class="mb-3"><?php echo htmlspecialchars($row['description']); ?></p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#event<?php echo $row['id']; ?>Modal">
                                <i class="fas fa-info-circle me-1"></i>View Details
                            </button>
                        </div>
                    </div>

                    <!-- Event Modal -->
                    <div class="modal fade" id="event<?php echo $row['id']; ?>Modal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-calendar me-2"></i><?php echo htmlspecialchars($row['title']); ?>
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <h6>Event Details</h6>
                                        <p class="mb-1"><i class="far fa-calendar-alt me-2"></i>Date: <?php echo $event_date->format('F d, Y'); ?></p>
                                        <p class="mb-1"><i class="far fa-clock me-2"></i>Time: <?php echo $start_time->format('h:i A') . ' - ' . $end_time->format('h:i A'); ?></p>
                                        <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i>Location: <?php echo htmlspecialchars($row['location']); ?></p>
                                    </div>
                                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-info-circle me-2"></i>Please bring necessary items and arrive on time.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                        echo '<div class="alert alert-info">No upcoming events found.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 