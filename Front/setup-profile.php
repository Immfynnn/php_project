<?php
// Start the session
session_start();

// Include the database connection
require_once '../config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $age = (int) $_POST['age'];
    $contactnum = htmlspecialchars($_POST['contactnum']);
    $address = htmlspecialchars($_POST['address']);

    // File upload logic
    if (isset($_FILES['userimg']) && $_FILES['userimg']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['userimg']['tmp_name'];
        $fileName = $_FILES['userimg']['name'];
        $fileSize = $_FILES['userimg']['size'];
        $fileType = $_FILES['userimg']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Directory where uploaded images will be saved
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . md5(time() . $fileName) . '.' . $fileExtension;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // File successfully uploaded
                $userimg = $dest_path;
            } else {
                // Error uploading the file
                $userimg = null;
            }
        }
    } else {
        // Keep the old image if no new file is uploaded
        $userimg = isset($_SESSION['userimg']) ? $_SESSION['userimg'] : null;
    }

    // Update the user information in the database
      $uid = $_SESSION['uid'];
      $sql = "UPDATE users SET firstname = ?, lastname = ?, age = ?, contactnum = ?, address = ?, userimg = ?, profile_completed = 1 WHERE uid = ?";
      
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('sssissi', $firstname, $lastname, $age, $contactnum, $address, $userimg, $uid);
      
      if ($stmt->execute()) {
          // Update session variables
          $_SESSION['firstname'] = $firstname;
          $_SESSION['lastname'] = $lastname;
          $_SESSION['age'] = $age;
          $_SESSION['contactnum'] = $contactnum;
          $_SESSION['address'] = $address;
          $_SESSION['userimg'] = $userimg;
          $_SESSION['profile_completed'] = 1; // Mark profile as completed
      
          // Redirect to a success page or reload the form with a success message
          
          echo '<script>
                        alert("Profile Setup Sucessfully, Start Your Reservation Now!");
                        window.location.href = "home.php";
                      </script>';

          exit();
      } else {
          echo "Error: " . $stmt->error;
      }
      

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Profile</title>
    <link rel="stylesheet" href="css/setup-style.css">
    
    <style>
        /* CSS for setup profile page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .setup-container {
            width: 100%;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .setup-container h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .setup-form {
            display: flex;
            flex-direction: column;
        }

        .setup-form label {
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .setup-form input, 
        .setup-form select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .submit-btn {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>Set Up Your Profile</h1>
        <form action="setup-profile.php" method="POST" class="setup-form" enctype="multipart/form-data">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : ''; ?>" required>

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo isset($_SESSION['lastname']) ? htmlspecialchars($_SESSION['lastname']) : ''; ?>" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" value="<?php echo isset($_SESSION['age']) ? htmlspecialchars($_SESSION['age']) : ''; ?>" required>

            <label for="contactnum">Contact Number:</label>
            <input type="text" id="contactnum" name="contactnum" value="<?php echo isset($_SESSION['contactnum']) ? htmlspecialchars($_SESSION['contactnum']) : ''; ?>" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo isset($_SESSION['address']) ? htmlspecialchars($_SESSION['address']) : ''; ?>" required>

            <label for="userimg">Upload Profile Picture:</label>
            <input type="file" id="userimg" name="userimg" accept="image/*">

            <button type="submit" class="submit-btn">Update Profile</button>
        </form>
    </div>
</body>
</html>
