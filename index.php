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

// Fetch announcements
$announcements = [];
$sql = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $result->free();
}

// Fetch upcoming events
$events = [];
$sql = "SELECT * FROM events WHERE status = 'approved' AND event_date >= CURDATE() ORDER BY event_date ASC, start_time ASC LIMIT 3";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $result->free();
}
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
    <style>
        .announcement-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .announcement-card:hover {
            transform: translateY(-5px);
        }
        .event-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .urgency-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
        }
        .announcement-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .event-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>
    <!-- Top Navigation (always visible, replaces sidebar) -->
    <nav class="navbar navbar-expand-lg main-nav mb-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="#">
                RED CROSS <span class="d-none d-md-inline ms-2">USTP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse mt-2 mt-lg-0" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
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
        <div class="welcome-banner mb-4">
            <h2 class="fw-bold mb-2">Welcome to the Philippine Red Cross USTP</h2>
            <p class="mb-0">Humanity. Neutrality. Impartiality. Independence. Voluntary Service. Unity. Universality.</p>
        </div>
        <!-- About Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-danger text-white fw-bold">ABOUT USTP RED CROSS COUNCIL</div>
            <div class="card-body">
                <p>
                    The <strong>USTP Red Cross Council</strong> is a recognized student organization at the University of Science and Technology of Southern Philippines (USTP), dedicated to promoting the values and humanitarian mission of the Philippine Red Cross within the university community.
                </p>
                <p>
                    Our council is committed to fostering a culture of volunteerism, compassion, and service. We organize blood donation drives, disaster preparedness seminars, health and safety trainings, and various outreach programs. Through these activities, we aim to empower students and staff to become active contributors to the well-being of the community.
                </p>
                <p>
                    Guided by the seven fundamental principles of the Red Cross—Humanity, Impartiality, Neutrality, Independence, Voluntary Service, Unity, and Universality—we strive to make a positive impact both on campus and beyond.
                </p>
                <p class="mb-0">
                    <strong>Join us</strong> in making a difference!
                </p>
            </div>
        </div>
       
        <!-- Announcements and Upcoming Events Side by Side -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <!-- Upcoming Events Section -->
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-danger text-white fw-bold">UPCOMING EVENTS</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if (count($events) > 0): ?>
                                <?php foreach ($events as $event): ?>
                                    <div class="col-12">
                                        <div class="event-card p-3 border rounded" data-bs-toggle="modal" data-bs-target="#eventModal<?php echo $event['id']; ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($event['title']); ?></h6>
                                                    <p class="event-time mb-0">
                                                        <i class="far fa-calendar-alt me-1"></i>
                                                        <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                                        <br>
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($event['start_time'])); ?> - 
                                                        <?php echo date('h:i A', strtotime($event['end_time'])); ?>
                                                    </p>
                                                </div>
                                                <span class="badge bg-danger">Upcoming</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Event Modal -->
                                    <div class="modal fade" id="eventModal<?php echo $event['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-calendar-alt me-2"></i>
                                                        <?php echo htmlspecialchars($event['title']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <p class="mb-2">
                                                            <i class="far fa-calendar-alt me-2"></i>
                                                            <strong>Date:</strong> <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                                                        </p>
                                                        <p class="mb-2">
                                                            <i class="far fa-clock me-2"></i>
                                                            <strong>Time:</strong> <?php echo date('h:i A', strtotime($event['start_time'])); ?> - 
                                                            <?php echo date('h:i A', strtotime($event['end_time'])); ?>
                                                        </p>
                                                        <p class="mb-2">
                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                            <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="border-top pt-3">
                                                        <h6>Description:</h6>
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                        <p class="mb-0">No upcoming events found.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Announcements Section -->
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-danger text-white fw-bold">ANNOUNCEMENTS</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if (count($announcements) > 0): ?>
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="col-12">
                                        <div class="announcement-card p-3 border rounded" data-bs-toggle="modal" data-bs-target="#announcementModal<?php echo $announcement['id']; ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                                    <p class="announcement-date mb-0">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?php echo date('F d, Y h:i A', strtotime($announcement['created_at'])); ?>
                                                    </p>
                                                </div>
                                                <span class="badge urgency-badge 
                                                    <?php
                                                        if ($announcement['urgency'] == 'high') echo 'bg-danger';
                                                        elseif ($announcement['urgency'] == 'medium') echo 'bg-warning text-dark';
                                                        else echo 'bg-secondary';
                                                    ?>">
                                                    <?php echo ucfirst($announcement['urgency']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Announcement Modal -->
                                    <div class="modal fade" id="announcementModal<?php echo $announcement['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-bullhorn me-2"></i>
                                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <span class="badge 
                                                            <?php
                                                                if ($announcement['urgency'] == 'high') echo 'bg-danger';
                                                                elseif ($announcement['urgency'] == 'medium') echo 'bg-warning text-dark';
                                                                else echo 'bg-secondary';
                                                            ?>">
                                                            <?php echo ucfirst($announcement['urgency']); ?>
                                                        </span>
                                                        <span class="text-muted ms-2">
                                                            <i class="far fa-clock me-1"></i>
                                                            <?php echo date('F d, Y h:i A', strtotime($announcement['created_at'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="border-top pt-3">
                                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-bullhorn fa-2x mb-2"></i>
                                        <p class="mb-0">No announcements found.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <div class="footer">
        &copy; 2025 BSIT2A. All rights reserved.<br>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
