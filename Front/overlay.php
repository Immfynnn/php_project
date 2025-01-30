<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* Overlay Styles */
        .overlay {
            display: flex; /* Hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction:row;
            opacity: 1;
            animation: fadeIn 0.5s forwards;
        }

        .overlay-content {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        .overlay h3 {
            margin-bottom: 20px;
            color: #28a745;
            font-size: 24px;
        }

        .overlay i {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .overlay button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .overlay button:hover {
            background-color: #218838;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

    </style>
</head>
<body>
    
</body>
</html>