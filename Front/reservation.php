<?php
// Include the database configuration
include '../config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: signup.php");
    exit();
}

// Get the user ID from the session
$user_id = intval($_SESSION['uid']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
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

        .re-cont {
            width: 100%;
            max-width: 1200px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .re-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .re-header h1 {
            font-size: 2rem;
            color: #333;
        }

        .re-header a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .re-header a:hover {
            background-color: #0056b3;
        }

        ul {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            list-style-type: none;
        }

        li {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        li:hover {
            transform: translateY(-5px);
        }

        .serv-pic {
            height: 150px;
            background-size: cover;
            background-position: center;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        /* Background images for each service */
        #pic1 { background-image: url('css/img/burial-image.jpg'); }
        #pic2 { background-image: url('css/img/Baptism-image.jpg'); }
        #pic3 { background-image: url('css/img/wedding-image.jpg'); }
        #pic4 { background-image: url('css/img/communion-image.jpg'); }
        #pic5 { background-image: url('css/img/blessing.jpg'); }
        #pic6 { background-image: url('css/img/confirmation-image.jpg'); }
        #pic7 { background-image: url('css/img/blessing.jpg'); } /* Ensure this image path is correct */

        h4 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 15px;
        }

        a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <div class="re-cont">
        <div class="re-header">
            <h1>Reservation</h1>
            <a href="reservation-process.php">Back</a>
        </div>
        <ul>
            <li>
              <div class="serv-pic" id="pic1"></div>
              <h4>Burial</h4>
              <a href="burial.php">Reserve Now</a>
            </li>
            <li>
              <div class="serv-pic" id="pic2"></div>
              <h4>Baptism</h4>
              <a href="baptism.php">Reserve Now</a>
            </li>
            <li>
              <div class="serv-pic" id="pic3"></div>
              <h4>Wedding</h4>
              <a href="wedding.php">Reserve Now</a>
            </li>
            <li>
              <div class="serv-pic" id="pic4"></div>
              <h4>Communion</h4>
              <a href="communion.php">Reserve Now</a>
            </li>
            <li>
              <div class="serv-pic" id="pic5"></div>
              <h4>Blessing</h4>
              <a href="blessing.php">Reserve Now</a>
            </li>
            <li>
              <div class="serv-pic" id="pic6"></div>
              <h4>Confirmation</h4>
              <a href="confirmation.php">Reserve Now</a>
            </li>
            <li>
              <div class="serv-pic" id="pic7"></div>
              <h4>Anointing of the Sick</h4>
              <a href="annoint.php">Reserve Now</a>
            </li>
        </ul>
    </div>

</body>
</html>
