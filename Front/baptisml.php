<?php
// Start the session
session_start();

// Include database connection
include 'sql/config.php'; // Adjust the path as necessary

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    // Redirect to login page if not logged in
    header("Location: signin.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $user_id = $_SESSION['uid']; // Get user ID from session
    $service_type = $_POST['service_type'];
    $description = $_POST['description'];
    $schedule = $_POST['schedule'];
    $time_slot = $_POST['time-slot'];
    $fee = $_POST['fee'];
    $amount = $_POST['ammount'];
    $payment_type = $_POST['payment_type']; // Correct spelling here

    // Set the fee and amount in the session
    $_SESSION['fee'] = $fee;
    $_SESSION['amount'] = $amount;

    // Handle file upload
    $target_dir = "uploads/";
    $file_names = [];
    $allowed_file_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    foreach ($_FILES['requirements']['name'] as $key => $name) {
        $file_type = $_FILES['requirements']['type'][$key];
        $target_file = $target_dir . basename($name);

        if (in_array($file_type, $allowed_file_types)) {
            if (move_uploaded_file($_FILES['requirements']['tmp_name'][$key], $target_file)) {
                $file_names[] = $name;
            } else {
                echo "Error uploading the file: " . htmlspecialchars($name);
            }
        } else {
            echo "Invalid file type for file: " . htmlspecialchars($name);
        }
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO services (uid, service_type, s_description, s_date, time_slot, s_requirements, s_fee, s_ammount, payment_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $requirements_json = json_encode($file_names); // Convert array to JSON string
    $stmt->bind_param("issssssss", $user_id, $service_type, $description, $schedule, $time_slot,$requirements_json, $fee, $amount, $payment_type);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Store the last inserted service ID in the session
        $_SESSION['s_id'] = $stmt->insert_id;

        // Redirect to payment page (api-net.php)
        header("Location: api-net.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation | Baptism</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            min-height: 100vh;
        }

        .cont-burial {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .b-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .b-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .b-header a {
            text-decoration: none;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .b-header a:hover {
            background-color: #0056b3;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .input-b {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="date"],
        input[type="time"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        input[type="text"][readonly],
        input[type="file"][readonly],
        select[readonly] {
            background-color: #e9ecef; /* Light gray background */
            cursor: not-allowed; /* Show a not-allowed cursor */
        }

        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }
        .radio-cnt-1 {
            background: rgba(0 ,0,0,.1);
            padding: 20px;
            margin-bottom:5px;
            border-radius:5px;
            opacity: 1;
        }
        .radio-cnt-1 .header-radio .right {
            display:flex;
            flex-direction:row;
            justify-content:center;
            align-items:center;
        }
        .radio-cnt-1 .header-radio .right h5 {
            margin-right:10px;
        }
        .radio-cnt-1 .header-radio{
            display:flex;
            flex-direction:row;
            justify-content:space-between;
            margin-bottom:5px;
            outline:solid 1px rgba(0,0,0,.1);
            padding:20px;
            border-radius:5px;
        }
        /* Increase the size of radio buttons */
input[type="radio"] {
    width: 1.5em; /* Adjust as needed */
    height: 1.5em; /* Adjust as needed */
    accent-color: #007BFF; /* Optional: change the color */
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
   <script>
        // JavaScript to show loading screen when the form is submitted
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent immediate form submission
                const loadingScreen = document.getElementById('loading-screen');
                loadingScreen.classList.add('visible'); // Show the loading screen

                // Wait for 5 seconds before submitting the form
                setTimeout(function() {
                    form.submit(); // Submit the form after 5 seconds
                }, 5000); // 5000 milliseconds = 5 seconds
            });
        });

        // JavaScript function to update the time slots based on the selected radio button
        document.addEventListener("DOMContentLoaded", function() {
            const regularSlotOptions = ["Regular 10:30PM - 12:00PM"];
            const specialSlotOptions = ["Special 8:00AM - 10:00AM", "Special 10:00AM - 12:00PM", "Special 1:00PM - 3:00PM", "Special 3:00PM - 5:00PM"];
            
            const timeSlotDropdown = document.getElementById('time-slot');
            const regularRadio = document.querySelector('input[name="selector"][value="regular"]');
            const specialRadio = document.querySelector('input[name="selector"][value="special"]');
            const amountInput = document.getElementById('ammount'); // Get the amount input

            // Function to update time slot dropdown
            function updateTimeSlots(options) {
                timeSlotDropdown.innerHTML = ""; // Clear existing options
                options.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = slot;
                    timeSlotDropdown.appendChild(option);
                });
            }

            // Function to update the amount based on selected radio button
            function updateAmount() {
                if (regularRadio.checked) {
                    amountInput.value = 'PHP 200.00'; // Set amount for Regular
                } else if (specialRadio.checked) {
                    amountInput.value = 'PHP 2500.00'; // Set amount for Special
                }
            }

            // Set up event listeners for the radio buttons
            regularRadio.addEventListener('change', () => {
                updateTimeSlots(regularSlotOptions);
                updateAmount(); // Update amount on change
            });
            specialRadio.addEventListener('change', () => {
                updateTimeSlots(specialSlotOptions);
                updateAmount(); // Update amount on change
            });

            // Initialize with Regular time slots and amount by default
            if (regularRadio.checked) {
                updateTimeSlots(regularSlotOptions);
                updateAmount(); // Set initial amount
            }
        });
    </script>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="spinner"></div><br><!-- Spinner element -->
        <h2 style="margin-left: 10px;">Loading, please wait...</h2>
    </div>
    <div class="cont-burial">
        <div class="b-header">
            <h1>Baptism</h1>
            <a href="reservation.php">Back</a>
        </div>
        <form action="burial.php" method="POST" enctype="multipart/form-data">
            <div class="input-b">
                <input type="text" value="<?php echo isset($service_type) ? htmlspecialchars($service_type) : 'Baptism'; ?>" name="service_type" style="display:none;">
            </div>

            <div class="input-b">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" value="<?php echo isset($description) ? htmlspecialchars($description) : ''; ?>" required>
            </div>

            <div class="input-b">
                <label for="schedule">Schedule:</label>
                <input type="date" id="schedule" name="schedule" value="<?php echo isset($schedule) ? htmlspecialchars($schedule) : ''; ?>" required>
            </div>

            <div class="input-b">

            <div class="radio-cnt-1">
                <label class="header-radio">
                    <h3>Regular</h3>
                    <div class="right">
                        <h5>PHP200.00</h5>
                        <input type="radio" name="selector" value="regular" required >
                    </div>
                </label>
            </div>
            <div class="radio-cnt-1">
                <label class="header-radio">
                    <h3>Special</h3>
                    <div class="right">
                        <h5>PHP2500.00</h5>
                        <input type="radio" name="selector" value="special" required>
                    </div>
                </label>
            </div>

            <!-- Dynamic Time Slot Dropdown -->
            <div class="input-b">
                <label for="time-slot">Time Slot:</label>
                <select name="time-slot" id="time-slot" required>
                    <option value="">Select Regular or Special</option>
                </select>
            </div>
            </div>
            


            <div class="input-b">
                <label for="requirements">Requirements:</label>
                <p style="font-size:12px;">Baptismal Certificate Male/Female</p>
                <br>
                <input type="file" id="requirements" name="requirements[]" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" multiple required>
            </div>

            <div class="input-b">
                <label for="fee">Fee:</label>
                <input type="text" id="fee" name="fee" value="<?php echo isset($fee) ? htmlspecialchars($fee) : 'PHP 5.00'; ?>" readonly>
            </div>
            <div class="input-b">
                <label for="ammount">Amount:</label>
                <input type="text" id="ammount" name="ammount" value="<?php echo isset($amount) ? htmlspecialchars($amount) : 'PHP 0.00'; ?>" readonly>
            </div>
            <div class="input-b">
                <label for="payment_type">Payment Type:</label>
                <select name="payment_type" id="payment_type">
                    <option value="Gcash">Gcash</option>
                </select>
            </div>

            <button type="submit">Next</button>
        </form>

    </div>
</body>
</html>
