<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .success-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .success-container h1 {
            color: #28a745;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .success-container p {
            color: #333;
            font-size: 1.1rem;
        }

        .success-container a {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .success-container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="success-container">
        <h1>Success!</h1>
        <p>{{ session('success') }}</p>
        <a>Thank You for your response</a>
    </div>
</body>

</html>
