<?php
// Include the database configuration
include '../config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signup.php");
    exit();
}

// Get the service ID from the URL
if (!isset($_GET['s_id'])) {
    // If no service ID is provided, redirect back to the reservation page
    header("Location: reservation.php");
    exit();
}

$service_id = intval($_GET['s_id']); // Get and sanitize the service ID
$user_id = intval($_SESSION['uid']); // Get the user ID from the session

// Query to get the service details and payment status
$sql = "SELECT s.*, p.p_status FROM services s
        LEFT JOIN payment p ON s.s_id = p.s_id AND p.uid = ?
        WHERE s.s_id = ? AND s.uid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $service_id, $user_id); // Bind user ID and service ID
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the service details along with payment status
    $service = $result->fetch_assoc();
} else {
    // If no service is found, redirect or show an error message
    echo "No service found!";
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file was uploaded
    if (isset($_FILES['s_requirements']) && $_FILES['s_requirements']['error'] == 0) {
        // Define the directory to save the uploaded file
        $upload_dir = 'uploads/';
        
        // Get the file details
        $file_name = $_FILES['s_requirements']['name'];
        $file_tmp = $_FILES['s_requirements']['tmp_name'];
        
        // Move the uploaded file to the target directory
        $file_path = $upload_dir . basename($file_name);
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Update the requirements in the database
            $update_sql = "UPDATE services SET s_requirements = ? WHERE s_id = ? AND uid = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sii", $file_path, $service_id, $user_id);
            
            if ($update_stmt->execute()) {
                // Success message
                echo "<script>alert('Requirements updated successfully');</script>";
                // Refresh the page to load updated data
                header("Refresh:0");
            } else {
                echo "Error updating requirements: " . $conn->error;
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "No file uploaded or file upload error.";
    }
}
// Function to get the color based on status
function getStatusColor($status) {
    switch ($status) {
        case 'Pending':
            return 'orange';
        case 'Paid':
            return 'green';
        case 'To Pay':
            return 'red';
        case 'Canceled':
            return 'red';
        case 'Processing':
            return 'darkyellow';
        case 'Approved':
            return 'green';
        case 'Ongoing':
                return 'green';
        case 'Completed':
            return 'blue';
        default:
            return 'black'; // Default color for unknown statuses
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .cont-details {
            width: 80%;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .header h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .details label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .details input {
            width: 95%;
            padding: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 21px;
            color: #333;
            background-color: #f9f9f9;
        }

        .details span {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        .btn-back {
            padding:20px;
            background:skyblue;
            width:95%;
            Color:white;
            font-weight:bold;
            text-align:center;
            border-radius:5px;

        }

        /* Loading Screen Styles */
        #loading-screen {
            position: fixed; /* Fixed position to cover the entire viewport */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white */
            display: flex;
            justify-content: center;
            flex-direction:column;
            align-items: center;
            z-index: 1000; /* Ensure it's above other elements */
            visibility: hidden; /* Initially hidden */
            opacity: 0; /* Initially fully transparent */
            transition: visibility 0s 0.5s, opacity 0.5s linear; /* Fade out after 5s */
        }

        #loading-screen.visible {
            visibility: visible; /* Show it */
            opacity: 1; /* Fully opaque */
            transition: visibility 0s, opacity 0.5s linear; /* Fade in immediately */
        }

        /* Spinner Styles */
        .spinner {
            border: 8px solid #f3f3f3; /* Light gray */
            border-top: 8px solid #3498db; /* Blue */
            border-radius: 50%; /* Circle */
            width: 50px; /* Size of the spinner */
            height: 50px; /* Size of the spinner */
            animation: spin 1s linear infinite; /* Spin animation */
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

    </style>

</head>
<body>


    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="spinner"></div><br><!-- Spinner element -->
        <h2 style="margin-left: 10px;">Loading, please wait...</h2>
    </div>
    
    <div class="cont-details">
        <div class="header">
            <h1><?php echo htmlspecialchars($service['service_type']); ?></h1> <!-- Display the service type in the header -->
        </div>

        <!-- Form for updating requirements -->
        <form method="POST" enctype="multipart/form-data">
        <div class="details">
                <label>Description</label>
                <input type="text" value="<?php echo htmlspecialchars($service['s_description']); ?>" readonly>

                <label>Date</label>
                <input type="text" value="<?php echo htmlspecialchars($service['s_date']); ?>" readonly>

                <label>Time Slot</label>
                <input type="text" value="<?php echo htmlspecialchars($service['time_slot']); ?>" readonly>

                <!-- Conditionally display file input or the uploaded file path -->
                <label>Requirements</label>
                <?php if (in_array($service['s_status'], ['Approved', 'Ongoing', 'Completed'])): ?>
                    <input type="text" value="<?php echo htmlspecialchars($service['s_requirements']); ?>" readonly>
                <?php else: ?>
                    <input type="file" name="s_requirements" required>
                <?php endif; ?>

                <label>Fee</label>
                <input type="text" value="<?php echo htmlspecialchars($service['s_fee']); ?>" readonly>

                <label>Amount Paid</label>
                <input type="text" value="<?php echo htmlspecialchars($service['s_ammount']); ?>" readonly>

                <label>Payment Type</label>
                <input type="text" value="<?php echo htmlspecialchars($service['payment_type']); ?>" readonly>

                <label>Payment Status</label>
                <input type="text" value="<?php echo htmlspecialchars($service['p_status'] ?? 'Pending'); ?>" readonly style="color: <?php echo getStatusColor($service['p_status'] ?? 'No Payment'); ?>;">

                <label>Status</label>
                <input type="text" value="<?php echo htmlspecialchars($service['s_status']); ?>" readonly style="color: <?php echo getStatusColor($service['s_status']); ?>;">

                <!-- Conditionally display the Submit button -->
                <?php if (in_array($service['s_status'], ['Pending', 'Processing'])): ?>
                    <button type="submit" name="submit">Submit</button>
                <?php endif; ?>
                
                <!-- Conditional button to go to api-net.php -->
                <?php if ($service['s_status'] == 'To Pay'): ?>
                    <a href="api-net.php" class="btn-back" name="submit" style="background: #007bff; text-align: center;" id="goToPayment">Go to Payment</a>
                <?php endif; ?>
                
                <a href="reservation-process.php" class="btn-back">Back</a>
            </div>
        </form>
    </div>

    <script>
        function showLoadingScreen() {
            const loadingScreen = document.getElementById('loading-screen');
            loadingScreen.classList.add('visible'); // Show the loading screen
        }

        // JavaScript to show loading screen when the form is submitted
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent immediate form submission
                showLoadingScreen();
                
                // Submit the form after 3 seconds
                setTimeout(function() {
                    form.submit(); // Submit the form after 3 seconds
                }, 3000); // 3000 milliseconds = 3 seconds
            });

            // Add click event for the "Go to Payment" button
            const paymentButton = document.getElementById('goToPayment');
            if (paymentButton) {
                paymentButton.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent immediate redirection
                    showLoadingScreen();
                    
                    // Redirect after 3 seconds
                    setTimeout(function() {
                        window.location.href = paymentButton.href; // Redirect after 3 seconds
                    }, 3000); // 3000 milliseconds = 3 seconds
                });
            }
        });
    </script>
</body>

</html>

<?php
$conn->close(); // Close the database connection
?>
