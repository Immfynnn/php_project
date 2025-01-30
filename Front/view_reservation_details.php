<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = intval($_SESSION['admin_id']);
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

$sqlServiceDetails = "SELECT reservation.*, users.username, users.userimg, payment.pay_id, payment.p_status, payment.total_amount 
                      FROM reservation
                      JOIN users ON reservation.uid = users.uid
                      LEFT JOIN payment ON reservation.s_id = payment.s_id
                      WHERE reservation.s_id = ?";

$stmtServiceDetails = $conn->prepare($sqlServiceDetails);
$stmtServiceDetails->bind_param('i', $service_id);
$stmtServiceDetails->execute();
$resultServiceDetails = $stmtServiceDetails->get_result();
$serviceDetails = $resultServiceDetails->fetch_assoc();
$stmtServiceDetails->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Reservation</title>
    <link rel="stylesheet" href="css/temp05.css">
   <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" type="image/png" href="css/img/LOGO.png">
</head>
<body>


    <section id="sidebar">
        <div class="center-a">
        <a href="" class="logs">
        <img src="css/img/LOGO.png" alt="Logo" style="width: 50px; border-radius:50px;" id="logs">
        <span class="text" id="title-txt">AdminHub</span>
        </a>
        </div>
        <ul class="side-menu top">
            <li>
                <a href="admin_dashboard.php">
                 <i class='bx bxs-dashboard'></i>
                 <span class="text">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin-post.php">
                <i class='bx bx-news'></i>
                 <span class="text">Post</span>
                </a>
            </li>
             <!--Messages Notification Count -->
             <?php
             // Example: Query to fetch the number of unread messages
               $unreadCount = 0; // Default value
               $sqlUnreadMessages = "SELECT COUNT(*) AS unread_count FROM messages1 WHERE recipient_aid = ? AND read_status1 = 0";
               $stmtUnread = $conn->prepare($sqlUnreadMessages);
               $stmtUnread->bind_param('i', $user_id);
               if ($stmtUnread->execute()) {
                   $resultUnread = $stmtUnread->get_result();
                   $unreadRow = $resultUnread->fetch_assoc();
                   $unreadCount = $unreadRow['unread_count'];
               }
              $stmtUnread->close();
            ?>
            
            <li>
                <a href="admin-messages.php">
                    <i class='bx bxs-chat'></i>
                    <span class="text">Messages</span>
                    <?php if ($unreadCount > 0): ?>
                        <span id="count"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const messagesLink = document.querySelector('a[href="admin-messages.php"]');
                messagesLink.addEventListener('click', function() {
                    const countSpan = document.getElementById('count');
                    if (countSpan) {
                        countSpan.textContent = ''; // Clear the count
                    }
                });
            });
            </script>
            <li>
                <a href="admin-announcement.php">
                <i class='bx bxs-megaphone'></i>
                 <span class="text">Announcement</span>
                </a>
            </li>
            <li>
                <a href="admin-schedule.php">
                <i class='bx bxs-calendar'></i>
                 <span class="text">Schedule</span>
                </a>
            </li>
            <li  class="active">
                <a href="admin-reservation.php">
                <i class='bx bxs-briefcase-alt-2'></i>
                 <span class="text">Reservation</span>
                </a>
            </li>
            <li>
                <a href="admin-parishioner.php">
                <i class='bx bxs-user'></i>
                 <span class="text">Parishioner</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="admin-settings.php">
                <i class='bx bxs-cog'></i>
                 <span class="text">Settings</span>
                </a>
            </li>
            <li>
                <a href="#" class="logout" id="logout-link">
                <i class='bx bx-log-out'></i>
                 <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#" style="opacity:0;">
                <div class="form-input">
                    <input type="search" name="" id="" placeholder="Search...">
                    <button type="submit" class="search-btn">
                    <i class='bx bx-search'></i>
                    </button>
                </div>
            </form>
            <div class="clock">
                <p class="greetings"></p>
              <h4 id="date-time"></h4>
             </div>
             <a href="admin-notification.php" class="notification">
    <i class='bx bxs-bell'></i>
    <span class="num" id="notification-count">0</span>
</a>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateNotificationCount() {
        $.ajax({
            url: 'fetch-notification-count.php', // Path to the backend script
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#notification-count').text(response.unread_count); // Update the count
                } else {
                    console.error(response.message);
                }
            },
            error: function(error) {
                console.error('Error fetching notification count:', error);
            }
        });
    }

    // Fetch notification count every 10 seconds
    setInterval(updateNotificationCount, 10000);
    // Fetch immediately when the page loads
    updateNotificationCount();
</script>
           
            <?php
            // Fetch admin details
            $adminDetails = null;
            $sqlAdminDetails = "SELECT admin_image, admin_name, admin_username, admin_contact_no, admin_email, admin_gender FROM admins WHERE admin_id = ?";
            $stmtAdmin = $conn->prepare($sqlAdminDetails);
            $stmtAdmin->bind_param('i', $user_id);
            if ($stmtAdmin->execute()) {
                $resultAdmin = $stmtAdmin->get_result();
                $adminDetails = $resultAdmin->fetch_assoc();
            }
            $stmtAdmin->close();
            
            // Set default profile image if none exists
            $profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : 'css/img/default-profile.png';
            
            // Display the profile image in the nav section
            ?>
            <a href="#" class="profile" id="profile-link">
                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="outline:solid 1px #000;">
            </a>

        </nav>


        <main>
    <div class="head-title">
        <div class="left">
            <h1>Reservation</h1>
            <ul class="breadcrumb">
                <li><a href="#">Dashboard</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a>Reservation</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li><a class="active" href="#">View Details</a></li>
            </ul>
        </div>
    </div>

    <div class="view-details" style="width:95%;">
        <div class="header" style="padding:20px; padding-bottom:30px; display:flex; flex-direction:row; justify-content:space-between;">
            <h3>Reservation Details</h3>
            <?php if (!empty($serviceDetails['pay_id'])): ?>
                <div>
                    <a href="view-payment.php?pay_id=<?php echo htmlspecialchars($serviceDetails['pay_id']); ?>" 
                       style="padding:10px; padding-left:20px; padding-right:20px; background:#3C91E6; color:#fff; border-radius:5px;">
                        View Payment
                    </a>
                    <a href="admin-pending-reservation.php" style="padding:10px; padding-left:20px; padding-right:20px; margin-left:5px; background:#DB504A; color:#fff; border-radius:5px;">Back</a>
                </div>
            <?php else: ?>
                <p>Payment details not available</p>
            <?php endif; ?>
        </div>

        <?php if ($serviceDetails): ?>
            <div>
                <div class="detail-row"><label><strong>User:</strong></label>
                    <p class="input-like"><?php echo htmlspecialchars($serviceDetails['username']); ?></p>
                </div>
                <div class="detail-row"><label><strong>Reservation Type:</strong></label>
                    <p class="input-like"><?php echo htmlspecialchars($serviceDetails['service_type']); ?></p>
                </div>
             
             
        <!-- START -->
               
<?php if ($serviceDetails['service_type'] == 'Wedding'): ?>

<div class="detail-row"><label><strong>Groom:</strong></label>
                   <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
               </div>

               <div class="detail-row"><label><strong>Bride:</strong></label>
                   <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description1'])); ?></p>
               </div>
               
               <div class="detail-row"><label><strong>Date:</strong></label>
                   <p class="input-like"><?php echo date('F j, Y', strtotime($serviceDetails['set_date'])); ?></p>
               </div>
               

       <!--To remove INPUT -->
                   <div class="detail-row"><label><strong>Time Slot:</strong></label>
                   <p class="input-like"><?php echo htmlspecialchars($serviceDetails['time_slot']); ?></p>
               </div>


               <!-- Display Valid ID -->
<div class="detail-row" style="display:flex;flex-direction:row; justify-content:space-between; rgba(0,0,0,.2)">
 
    <div class="valid-id-display">   <label><strong>Requirements:</strong></label>
    <p>Reserver's Valid ID:</p>
        <?php
        if (!empty($serviceDetails['valid_id'])) {
            $validIds = json_decode($serviceDetails['valid_id'], true);
            if (is_array($validIds)) {
                foreach ($validIds as $file) {
                    $file_path = "uploads/" . htmlspecialchars($file);
                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                    if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                    } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                        echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                    }
                }
            } else {
                echo '<p>No valid ID uploaded.</p>';
            }
        } else {
            echo '<p>No Valid ID uploaded.</p>';
        }
        ?>
    </div>
    <div class="requirements-display">
        <br>
    <p>Birth Ceritificate Male and Female:</p>
        <?php
        if (!empty($serviceDetails['s_requirements'])) {
            $requirements = json_decode($serviceDetails['s_requirements'], true);
            if (is_array($requirements)) {
                foreach ($requirements as $file) {
                    $file_path = "uploads/" . htmlspecialchars($file);
                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                    if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                    } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                        echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                    }
                }
            } else {
                echo '<p>No requirements found.</p>';
            }
        } else {
            echo '<p>No requirements uploaded.</p>';
        }
        ?>
    </div>

    <div class="requirements-display">
        <br>
        <p>Cenomar Male and Female:</p>
        <?php
        if (!empty($serviceDetails['s_requirements1'])) {
            $requirements = json_decode($serviceDetails['s_requirements1'], true);
            if (is_array($requirements)) {
                foreach ($requirements as $file) {
                    $file_path = "uploads/" . htmlspecialchars($file);
                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                    if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                    } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                        echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                    }
                }
            } else {
                echo '<p>No requirements found.</p>';
            }
        } else {
            echo '<p>No requirements uploaded.</p>';
        }
        ?>
    </div>
</div>
<!-- Correctly Display Other Requirements -->
<div class="detail-row"  style="display:flex;flex-direction:row;">

    <div class="requirements-display">
        <br>
    <p>Confirmation Male and Female:</p>
        <?php
        if (!empty($serviceDetails['s_requirements2'])) {
            $requirements = json_decode($serviceDetails['s_requirements2'], true);
            if (is_array($requirements)) {
                foreach ($requirements as $file) {
                    $file_path = "uploads/" . htmlspecialchars($file);
                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                    if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                    } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                        echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                    }
                }
            } else {
                echo '<p>No requirements found.</p>';
            }
        } else {
            echo '<p>No requirements uploaded.</p>';
        }
        ?>
    </div>
    <div class="requirements-display" style="margin-left:200px;">
        <br>
        <p>Marriage License Filed:</p>
        <?php
        if (!empty($serviceDetails['s_requirements3'])) {
            $requirements = json_decode($serviceDetails['s_requirements3'], true);
            if (is_array($requirements)) {
                foreach ($requirements as $file) {
                    $file_path = "uploads/" . htmlspecialchars($file);
                    $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                    if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                    } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                        echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                    }
                }
            } else {
                echo '<p>No requirements found.</p>';
            }
        } else {
            echo '<p>No requirements uploaded.</p>';
        }
        ?>
    </div>
</div>


               <!-- Total Amount, Payment Type, and Payment Status -->
               <div class="detail-row"><label><strong>Total Amount:</strong></label>
                   <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
               </div>

               <!-- Payment Type with Conditional Styling -->
               <div class="detail-row"><label><strong>Payment Type:</strong></label>
                   <p class="input-like" 
                      style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                       <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                   </p>
               </div>

               <!-- Payment Status with Conditional Styling -->
               <div class="detail-row"><label><strong>Payment Status:</strong></label>
                   <p class="input-like" 
                      style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                       <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                   </p>
               </div>

                   <!--END-->
<?php endif; ?>




    <!-- BAPTISM DETAILS-->

    

<?php if ($serviceDetails['service_type'] == 'Baptism'): ?>


<div class="detail-row"><label><strong>Description:</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Date:</strong></label>
                <p class="input-like"><?php echo date('F j, Y', strtotime($serviceDetails['set_date'])); ?></p>
            </div>
            

    <!--To remove INPUT -->
                <div class="detail-row"><label><strong>Time Slot:</strong></label>
                <p class="input-like"><?php echo htmlspecialchars($serviceDetails['time_slot']); ?></p>
            </div>


            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Display Requirements -->
            <div class="detail-row"><label><strong>Requirements:</strong></label>
            <p>Live Birth</p>
                <div class="requirements-display">
                    <?php
                    if (!empty($serviceDetails['s_requirements'])) {
                        $requirements = json_decode($serviceDetails['s_requirements'], true);
                        if (is_array($requirements)) {
                            foreach ($requirements as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No requirements found.</p>';
                        }
                    } else {
                        echo '<p>No requirements uploaded.</p>';
                    }
                    ?>
                </div>
            </div>
             <!-- Total Amount, Payment Type, and Payment Status -->
             <div class="detail-row"><label><strong>Per Head:</strong></label>
                <p class="input-like" style="text-transform:uppercase;"><?php echo strtolower($serviceDetails['per_head']); ?></p>
            </div>

            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>
<!--END-->
<?php endif; ?>


                <!--Confirmation details-->


                <?php if ($serviceDetails['service_type'] == 'Confirmation'): ?>


<div class="detail-row"><label><strong>Name:</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Date:</strong></label>
                <p class="input-like"><?php echo date('F j, Y', strtotime($serviceDetails['set_date'])); ?></p>
            </div>
            

    <!--To remove INPUT -->
                <div class="detail-row"><label><strong>Type:</strong></label>
                <p class="input-like"><?php echo htmlspecialchars($serviceDetails['r_type']); ?></p>
            </div>


            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Display Requirements -->
            <div class="detail-row"><label><strong>Requirements:</strong></label>
                <div class="requirements-display">
                    <?php
                    if (!empty($serviceDetails['s_requirements'])) {
                        $requirements = json_decode($serviceDetails['s_requirements'], true);
                        if (is_array($requirements)) {
                            foreach ($requirements as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No requirements found.</p>';
                        }
                    } else {
                        echo '<p>No requirements uploaded.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>
<!--END-->
<?php endif; ?>


                <!--Blessing-->

                

<?php if ($serviceDetails['service_type'] == 'Blessing'): ?>


<div class="detail-row"><label><strong>Full Name:</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>

            

    <!--To remove INPUT -->
                <div class="detail-row"><label><strong>Type:</strong></label>
                <p class="input-like"><?php echo htmlspecialchars($serviceDetails['r_type']); ?></p>
            </div>

            <div class="detail-row"><label><strong>Date:</strong></label>
                <p class="input-like"><?php echo date('F j, Y', strtotime($serviceDetails['set_date'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Time Slot:</strong></label>
                <p class="input-like"><?php echo (htmlspecialchars($serviceDetails['time_slot'])); ?></p>
            </div>


            <div class="detail-row"><label><strong>Address:</strong></label>
            <p class="input-like"><?php echo htmlspecialchars($serviceDetails['s_address']); ?></p>
            </div>
            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>

        

            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>
<!--END-->
<?php endif; ?>


                <!--BURIAL DETAILS-->

                <?php if ($serviceDetails['service_type'] == 'Burial'): ?>


<div class="detail-row"><label><strong>Description:</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Date:</strong></label>
                <p class="input-like"><?php echo date('F j, Y', strtotime($serviceDetails['set_date'])); ?></p>
            </div>
            

    <!--To remove INPUT -->
            <?php if ($serviceDetails['service_type'] !== 'Baptism' && ($serviceDetails['service_type'] !== 'Confirmation' )): ?>
                <div class="detail-row"><label><strong>Time Slot:</strong></label>
                <p class="input-like"><?php echo htmlspecialchars($serviceDetails['time_slot']); ?></p>
            </div>
<div class="detail-row">
    <label><strong>Home Address of the Deceased:</strong></label>
    <p class="input-like"><?php echo htmlspecialchars($serviceDetails['s_address']); ?></p>
</div>
<?php endif; ?>


            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Display Requirements -->
            <div class="detail-row"><label><strong>Requirements:</strong></label>
                <div class="requirements-display">
                    <?php
                    if (!empty($serviceDetails['s_requirements'])) {
                        $requirements = json_decode($serviceDetails['s_requirements'], true);
                        if (is_array($requirements)) {
                            foreach ($requirements as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" style="max-width: 200px; max-height: 200px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No requirements found.</p>';
                        }
                    } else {
                        echo '<p>No requirements uploaded.</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>

<!--END-->
<?php endif; ?>
                

<!---MASS INTENTION DETAILS-->



<?php if ($serviceDetails['service_type'] == 'Mass Intention'): ?>


<div class="detail-row"><label><strong>Type of Mass Intention:</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Name:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description1'])); ?></p>
            </div>
            
            <div class="detail-row"><label><strong>Schedule:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['r_type'])); ?></p>
            </div>


            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>


            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>

<!--END-->
<?php endif; ?>

<!--Annointing of the sick details --->

<?php if ($serviceDetails['service_type'] == 'Annointing of the Sick'): ?>


<div class="detail-row"><label><strong>Full Name</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Schedule:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['set_date'])); ?></p>
            </div>
            
            <div class="detail-row"><label><strong>Time Slot:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['time_slot'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Address:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_address'])); ?></p>
            </div>



            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>


            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>

<!--END-->
<?php endif; ?>

<!--Holy Eucharist--->


<?php if ($serviceDetails['service_type'] == 'Holy Eucharist'): ?>


<div class="detail-row"><label><strong>Description</strong></label>
<p>Mass in School And Mass in Church</p>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description'])); ?></p>
            </div>
            
<div class="detail-row"><label><strong>No. of attendences:</strong></label>
                <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_description1'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Schedule:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['set_date'])); ?></p>
            </div>
            
            <div class="detail-row"><label><strong>Time Slot:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['time_slot'])); ?></p>
            </div>
            <div class="detail-row"><label><strong>Address:</strong></label>
            <p class="input-like"><?php echo nl2br(htmlspecialchars($serviceDetails['s_address'])); ?></p>
            </div>



            <!-- Display Valid ID -->
            <div class="detail-row">
                <label><strong>Reserver's Valid ID:</strong></label>
                <div class="valid-id-display">
                    <?php
                    if (!empty($serviceDetails['valid_id'])) {
                        $validIds = json_decode($serviceDetails['valid_id'], true);
                        if (is_array($validIds)) {
                            foreach ($validIds as $file) {
                                $file_path = "uploads/" . htmlspecialchars($file);
                                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);

                                if (in_array($file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '<img src="' . $file_path . '" alt="' . htmlspecialchars($file) . '" class="requirement-image" style="max-width: 200px; max-height: 200px; margin-right: 10px; border-radius:5px; cursor: pointer;" onclick="zoomImage(this.src)">';
                                } elseif (in_array($file_type, ['pdf', 'doc', 'docx'])) {
                                    echo '<a href="' . $file_path . '" target="_blank">' . htmlspecialchars($file) . '</a>';
                                }
                            }
                        } else {
                            echo '<p>No valid ID uploaded.</p>';
                        }
                    } else {
                        echo '<p>No Valid ID uploaded.</p>';
                    }
                    ?>
                </div>
            </div>


            <!-- Total Amount, Payment Type, and Payment Status -->
            <div class="detail-row"><label><strong>Total Amount:</strong></label>
                <p class="input-like" style="text-transform:uppercase;">PHP<?php echo strtolower($serviceDetails['amount']); ?></p>
            </div>

            <!-- Payment Type with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Type:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['payment_type']); ?>
                </p>
            </div>

            <!-- Payment Status with Conditional Styling -->
            <div class="detail-row"><label><strong>Payment Status:</strong></label>
                <p class="input-like" 
                   style="<?php echo ($serviceDetails['p_status'] == 'Paid') ? 'color:green; font-weight:bold;' : ''; ?>">
                    <?php echo htmlspecialchars($serviceDetails['p_status'] ?: 'Pending'); ?>
                </p>
            </div>

<!--END-->
<?php endif; ?>
                <!--end-->

                <!-- Service Status with Conditional Styling -->
                <div class="detail-row"><label><strong>Service Status:</strong></label>
                    <p class="input-like">
                        <span class="status <?php echo strtolower($serviceDetails['s_status']); ?>" 
                              style="<?php echo ($serviceDetails['s_status'] == 'Completed') ? 'color:blue; font-weight:bold;' : ''; ?>">
                            <?php echo htmlspecialchars($serviceDetails['s_status']); ?>
                        </span>
                    </p>
                </div>

<!-- Conditionally Display Payment Type (Priest) Only If Status is Ongoing or Completed -->
<?php if ($serviceDetails['s_status'] == 'Ongoing' || $serviceDetails['s_status'] == 'Completed'): ?>
    <div class="detail-row">
        <label><strong>Priest:</strong></label>
        <p class="input-like"><?php echo htmlspecialchars($serviceDetails['priest']); ?></p>
    </div>
<?php endif; ?>


                <!-- Conditionally Display the Form based on Service Status -->
<?php if ($serviceDetails['s_status'] !== 'Completed' && $serviceDetails['s_status'] !== 'Canceled'): ?>
    <form method="POST" action="admin_services_update.php">
        <?php if ($serviceDetails['s_status'] !== 'Ongoing'): ?>
            <div class="detail-row">
                <label><strong>Select Priest:</strong></label>
                <select name="priest" id="priest" class="input-like">
                    <option value="Rev. Fr. Reynilo I. Talangon">Rev. Fr. Reynilo I. Talangon (Parish Priest)</option>
                    <option value="Rev. Fr. Gilbert B. Ytang">Rev. Fr. Gilbert B. Ytang (Asst. Parish Priest)</option>
                    <option value="Rev. Fr. Mac Jason N. Bacalla">Rev. Fr. Mac Jason N. Bacalla (Asst. Parish Priest)</option>
                </select>
            </div>
        <?php endif; ?>

        <select name="s_status" id="s_status" class="input-like" style="width:100%;">
            <?php if ($serviceDetails['s_status'] !== 'Ongoing'): ?>
                <option value="Approved" <?php echo ($serviceDetails['s_status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="Canceled" <?php echo ($serviceDetails['s_status'] == 'Canceled') ? 'selected' : ''; ?>>Canceled</option>
            <?php endif; ?>
            <!-- Add other status options if needed -->
        </select>

        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>">
        <button type="submit" class="btn-update-stat">Update</button>
    </form>
<?php else: ?>
    <p>No reservation details found.</p>
<?php endif; ?>

            </div>
        <?php else: ?>
            <p>No reservation details found.</p>
        <?php endif; ?>
    </div>


    
</main>
<div id="success-overlay" class="overlay02">
    <div class="overlay-content" style="background:transparent;">
        <h2 style="color:#fff;">Successfully Updated!</h2>
    </div>
</div>
<style>
.overlay02 {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    animation: fadeOut 0.5s forwards;
}

.overlay-content {
    background: white;
    padding: 20px 40px;
    border-radius: 8px;
    text-align: center;
    font-size: 1.2em;
    font-weight: bold;
    color: #4CAF50;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}
</style>



    </section>
    <script>
document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const overlay = document.getElementById("success-overlay");

    if (urlParams.get("update") === "success") {
        // Show the overlay
        overlay.style.display = "flex";
        overlay.style.animation = "fadeIn 0.5s forwards";

        // Automatically hide after 2 seconds
        setTimeout(() => {
            overlay.style.animation = "fadeOut 0.5s forwards";
            setTimeout(() => {
                overlay.style.display = "none";
            }, 500); // Match the duration of fadeOut animation
        }, 2000);

        // Add a click event to redirect when clicking outside the overlay content
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) { // Ensure only clicks outside content trigger the redirect
                window.location.href = "admin-approved-reservation.php";
            }
        });
    }
});
</script>



    <div class="overlay" id="confirmation-dialog">
    <div class="dialog">
        <p>Are you sure you want to log out?</p>
        <br>
        <button id="confirm-logout">Yes</button>
        <button id="cancel-logout">No</button>
    </div>
</div>
<script src="javascript/script.js"></script>
<?php
// Fetch admin details
$adminDetails = null;
$sqlAdminDetails = "SELECT admin_image, admin_name, admin_username, admin_contact_no, admin_email, admin_gender FROM admins WHERE admin_id = ?";
$stmtAdmin = $conn->prepare($sqlAdminDetails);
$stmtAdmin->bind_param('i', $user_id);
if ($stmtAdmin->execute()) {
    $resultAdmin = $stmtAdmin->get_result();
    $adminDetails = $resultAdmin->fetch_assoc();
}
$stmtAdmin->close();

// Set default profile image if none exists
$profileImage = !empty($adminDetails['admin_image']) ? $adminDetails['admin_image'] : 'css/img/default-profile.png';
?>

<div class="overlay1" id="MyProfile">
    <br>
    <div class="dialog1">
        <h1>My Profile</h1>
        <br>
        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" style="width: 100px; height: 100px; border-radius: 50%;">
       <div class="div-pg">
        <div class="div-ls">
        <p>Name:</p>
        <p>Username:</p> 
        <p>Contact #:</p>
        <p>Email:</p>
        <p>Gender:</p>
        </div>
        <div class="div-ls1">
        <p><?php echo htmlspecialchars($adminDetails['admin_name']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_username']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_contact_no']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_email']); ?></p>
        <p><?php echo htmlspecialchars($adminDetails['admin_gender']); ?></p>
        </div>
        </div>
        <a href="admin-profile-update.php">Edit Profile</a>
        <button id="close-profile">Close</button>
    </div>
</div>


<script src="javascript/admin-profile.js"></script>

<!-- CLock  -->
<script>

window.addEventListener("load", () => {
  clock();
  function clock() {
    const today = new Date();

    // get time components
    const hours = today.getHours();
    const minutes = today.getMinutes();
    const seconds = today.getSeconds();

    //add '0' to hour, minute & second when they are less 10
    const hour = hours < 10 ? "0" + hours : hours;
    const minute = minutes < 10 ? "0" + minutes : minutes;
    const second = seconds < 10 ? "0" + seconds : seconds;

    //make clock a 12-hour time clock
    const hourTime = hour > 12 ? hour - 12 : hour;

    // if (hour === 0) {
    //   hour = 12;
    // }
    //assigning 'am' or 'pm' to indicate time of the day
    const ampm = hour < 12 ? "AM" : "PM";

    // get date components
    const month = today.getMonth();
    const year = today.getFullYear();
    const day = today.getDate();

    //declaring a list of all months in  a year
    const monthList = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December"
    ];

    //get current date and time
    const time = hourTime + ":" + minute + ":" + second + ampm;

    //combine current date and time
    const dateTime = time;

    //print current date and time to the DOM
    document.getElementById("date-time").innerHTML = dateTime;
    setTimeout(clock, 1000);
  }
});
</script>

<script>
// Function to display the zoomed image
function zoomImage(src) {
    const zoomedContainer = document.createElement('div');
    zoomedContainer.classList.add('zoomed');

    zoomedContainer.innerHTML = `<img src="${src}" alt="Zoomed Image">`;
    
    // Append the zoomed container to the body
    document.body.appendChild(zoomedContainer);
    
    // Trigger a reflow to apply the transition
    requestAnimationFrame(() => {
        zoomedContainer.classList.add('show');
    });

    // Add event listener to close the zoomed image when clicked
    zoomedContainer.addEventListener('click', function() {
        zoomedContainer.classList.remove('show'); // Start fade-out
        setTimeout(() => {
            document.body.removeChild(zoomedContainer); // Remove after fade-out
        }, 300); // Match this with your CSS transition duration
    });
}
</script>

</body>
</html>