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

// Pagination setup
$members_per_page = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $members_per_page;

// Get total members count
$total_members_result = $mysqli->query("SELECT COUNT(*) as count FROM members");
$total_members_row = $total_members_result->fetch_assoc();
$total_members = $total_members_row['count'];
$total_pages = ceil($total_members / $members_per_page);

// Fetch paginated members
$other_members = [];
$other_sql = "SELECT * FROM members LIMIT $members_per_page OFFSET $offset";
if ($other_result = $mysqli->query($other_sql)) {
    while ($row = $other_result->fetch_assoc()) {
        $other_members[] = $row;
    }
    $other_result->free();
}

// Fetch council members from database
$sql = "SELECT * FROM council_members ORDER BY FIELD(position, 'President', 'Vice President', 'Secretary')";
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
    <style>
    .masonry {
      column-count: 3;
      column-gap: 1.5rem;
    }
    @media (max-width: 991.98px) {
      .masonry { column-count: 2; }
    }
    @media (max-width: 767.98px) {
      .masonry { column-count: 1; }
    }
    .masonry .masonry-item {
      display: inline-block;
      width: 100%;
      margin-bottom: 1.5rem;
      /* Animation styles */
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 0.7s ease forwards;
    }
    .masonry .masonry-item:nth-child(1) { animation-delay: 0.1s; }
    .masonry .masonry-item:nth-child(2) { animation-delay: 0.2s; }
    .masonry .masonry-item:nth-child(3) { animation-delay: 0.3s; }
    .masonry .masonry-item:nth-child(4) { animation-delay: 0.4s; }
    .masonry .masonry-item:nth-child(5) { animation-delay: 0.5s; }
    .masonry .masonry-item:nth-child(6) { animation-delay: 0.6s; }
    .masonry .masonry-item:nth-child(7) { animation-delay: 0.7s; }
    .masonry .masonry-item:nth-child(8) { animation-delay: 0.8s; }
    .masonry .masonry-item:nth-child(9) { animation-delay: 0.9s; }
    .masonry .masonry-item:nth-child(10) { animation-delay: 1s; }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: none;
      }
    }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="announcements.php"><i class="fas fa-bullhorn"></i> Announcement</a></li>
                    <li class="nav-item"><a class="nav-link active" href="members.php"><i class="fas fa-users"></i> Members</a></li>
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
        <!-- Members Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-danger text-white fw-bold d-flex align-items-center">
                <i class="fas fa-users me-2"></i>COUNCIL OFFICERS
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="member-card p-3 border rounded h-100">
                            <div class="text-center mb-3">
                                <h5 class="mb-1"><?php echo htmlspecialchars($row['fullname']); ?></h5>
                                <span class="badge bg-danger mb-2"><?php echo htmlspecialchars($row['position']); ?></span>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($row['description']); ?></p>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#contactModal<?php echo $row['id']; ?>">
                                    <i class="fas fa-envelope me-1"></i>Contact
                                </button>
                            </div>
                        </div>
                    </div>
                   

                    <!-- Contact Modal for each member -->
                    <div class="modal fade" id="contactModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="contactModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title" id="contactModalLabel<?php echo $row['id']; ?>">
                                        <i class="fas fa-envelope me-2"></i>Contact Information
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-4">
                                        <h4 class="mb-1"><?php echo htmlspecialchars($row['fullname']); ?></h4>
                                        <span class="badge bg-danger mb-2"><?php echo htmlspecialchars($row['position']); ?></span>
                                    </div>
                                    <div class="contact-info">
                                        <div class="row mb-3">
                                            <div class="col-4 fw-bold">Student ID:</div>
                                            <div class="col-8"><?php echo htmlspecialchars($row['student_id']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-4 fw-bold">Email:</div>
                                            <div class="col-8"><?php echo htmlspecialchars($row['email']); ?></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4 fw-bold">Office Hours:</div>
                                            <div class="col-8">Monday - Friday, 8:00 AM - 5:00 PM</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                        }
                    } else {
                        echo '<div class="col-12"><div class="alert alert-info">No council members found.</div></div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Other Members Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-danger text-white fw-bold d-flex align-items-center">
                <i class="fas fa-users me-2"></i>OTHER MEMBERS
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-danger">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Department</th>
                                <th>Year Level</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($other_members) > 0): ?>
                                <?php foreach ($other_members as $m): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($m['id']); ?></td>
                                        <td><?php echo htmlspecialchars($m['name']); ?></td>
                                        <td><?php echo htmlspecialchars($m['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($m['department']); ?></td>
                                        <td><?php echo htmlspecialchars($m['year_level']); ?></td>
                                        <td><?php echo htmlspecialchars($m['status']); ?></td>
                                        <td><?php echo htmlspecialchars($m['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($m['updated_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No other members found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Controls -->
                <nav aria-label="Members pagination">
                    <ul class="pagination justify-content-center mt-3">
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 