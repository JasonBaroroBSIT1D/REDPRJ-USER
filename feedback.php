<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Database connection (update with your credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "red_cross_council";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Standardize service type values
    $service_type = trim($_POST['service_type']);
    $valid_service_types = [
        'Training Program',
        'Event Participation',
        'Volunteer Work',
        'Donation',
        'First Aid'
    ];
    
    // Validate service type
    if (!in_array($service_type, $valid_service_types)) {
        echo "<div class='alert alert-danger'>Invalid service type selected.</div>";
        exit;
    }

    $comments = trim($_POST['comments']);
    $rating = (int)$_POST['rating'];
    $submitter_name = trim($_SESSION["fullname"]);
    $department = trim($_POST['department']);
    $email = isset($_SESSION["email"]) ? trim($_SESSION["email"]) : '';

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Debug information
    error_log("Attempting to insert feedback with service type: " . $service_type);

    // First check if the service_type column exists
    $check_column = "SHOW COLUMNS FROM feedback LIKE 'service_type'";
    $result = $conn->query($check_column);
    if ($result->num_rows == 0) {
        // If column doesn't exist, add it
        $add_column = "ALTER TABLE feedback ADD COLUMN service_type VARCHAR(50) NOT NULL";
        if (!$conn->query($add_column)) {
            error_log("Error adding service_type column: " . $conn->error);
            echo "<div class='alert alert-danger'>Database structure error. Please contact administrator.</div>";
            exit;
        }
    }

    // Prepare the insert statement
    $stmt = $conn->prepare("INSERT INTO feedback (submitter_name, department, email, service_type, rating, comments) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo "<div class='alert alert-danger'>Database error. Please try again.</div>";
        exit;
    }
    
    // Bind parameters
    if (!$stmt->bind_param("ssssis", $submitter_name, $department, $email, $service_type, $rating, $comments)) {
        error_log("Binding parameters failed: " . $stmt->error);
        echo "<div class='alert alert-danger'>Error preparing data. Please try again.</div>";
        exit;
    }

    // Execute the statement
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo "<div class='alert alert-danger'>Error saving feedback. Please try again.</div>";
        exit;
    }

    echo "<div class='alert alert-success'>Thank you for your feedback!</div>";
    error_log("Feedback successfully inserted with service type: " . $service_type);

    $stmt->close();
    $conn->close();
}


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the 5 most recent feedback entries
$recent_feedback = [];
$sql = "SELECT submitter_name, comments, created_at, service_type, department, rating FROM feedback ORDER BY created_at DESC LIMIT 3";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $recent_feedback[] = $row;
    }
}
$conn->close();
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
        .rating {
            direction: rtl;
            unicode-bidi: bidi-override;
            display: inline-flex;
        }
        .rating input[type="radio"] {
            display: none;
        }
        .rating label {
            font-size: 2em;
            color: #ccc;
            cursor: pointer;
        }
        .rating input[type="radio"]:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffc107;
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="announcements.php"><i class="fas fa-bullhorn"></i> Announcement</a></li>
                    <li class="nav-item"><a class="nav-link" href="members.php"><i class="fas fa-users"></i> Members</a></li>
                    <li class="nav-item"><a class="nav-link active" href="feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
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
        <!-- Feedback Section -->
        <div class="row">
            <!-- Feedback Form Column -->
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="fas fa-comment-dots me-2"></i>SHARE YOUR FEEDBACK
                    </div>
                    <div class="card-body">
                        <form method="POST" action="feedback.php">
                            <div class="mb-4">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="" selected disabled>Select department</option>
                                    <option value="BSIT">BSIT</option>
                                    <option value="BTLED-AI">BTLED-AI</option>
                                    <option value="BTLED-HE">BTLED-HE</option>
                                    <option value="BTLED-ICT">BTLED-ICT</option>
                                    <option value="BFPT">BFPT</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="serviceType" class="form-label">Service Type</label>
                                <select class="form-select" id="serviceType" name="service_type" required>
                                    <option value="" selected disabled>Select service type</option>
                                    <option value="Training Program">Training Program</option>
                                    <option value="Event Participation">Event Participation</option>
                                    <option value="Volunteer Work">Volunteer Work</option>
                                    <option value="Donation">Donation</option>
                                    <option value="First Aid">First Aid</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="feedbackText" class="form-label">Your Message</label>
                                <textarea class="form-control" id="feedbackText" name="comments" rows="4" placeholder="Share your thoughts about the council..." required></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">How would you rate your experience?</label>
                                <div class="rating">
                                    <input type="radio" name="rating" value="5" id="star5" required><label for="star5">&#9733;</label>
                                    <input type="radio" name="rating" value="4" id="star4"><label for="star4">&#9733;</label>
                                    <input type="radio" name="rating" value="3" id="star3"><label for="star3">&#9733;</label>
                                    <input type="radio" name="rating" value="2" id="star2"><label for="star2">&#9733;</label>
                                    <input type="radio" name="rating" value="1" id="star1"><label for="star1">&#9733;</label>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Feedback Column -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white fw-bold">
                        <i class="fas fa-history me-2"></i>RECENT FEEDBACK
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_feedback)): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-comment-slash fa-3x mb-3"></i>
                                <p>No feedback available yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($recent_feedback as $feedback): ?>
                                <div class="col-12 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h5 class="card-title mb-0 text-danger">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <?= htmlspecialchars($feedback['service_type']) ?>
                                                </h5>
                                                <span class="badge bg-danger">
                                                    Rating: <?= htmlspecialchars($feedback['rating']) ?>/5
                                                </span>
                                            </div>
                                            <p class="card-text"><?= nl2br(htmlspecialchars($feedback['comments'])) ?></p>
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div class="text-muted small">
                                                    <i class="fas fa-user me-1"></i> <?= htmlspecialchars($feedback['submitter_name']) ?> |
                                                    <i class="fas fa-building me-1"></i> <?= htmlspecialchars($feedback['department']) ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <i class="fas fa-clock me-1"></i> <?= date('M d, Y H:i', strtotime($feedback['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 