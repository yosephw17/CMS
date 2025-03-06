<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Choose Courses</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        h5 {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            color: #333;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:disabled {
            background-color: #e9ecef;
            color: #666;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 12px;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-primary:active {
            background-color: #004080;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .container {
                padding: 1rem;
            }

            h5 {
                font-size: 1rem;
            }

            input[type="text"],
            select {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .btn-primary {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h5>Please choose courses, at least three.</h5>

        <!-- Instructor's name (read-only) -->
        <label for="name">Name</label>
        <input id="name" type="text" class="form-control" value="{{ $instructor->name }}" disabled>

        <form action="{{ route('assignments.storeChoices') }}" method="POST">
            @csrf
            <input type="hidden" name="instructor_id" value="{{ $instructor->id }}">
            <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">

            @for ($i = 1; $i <= 5; $i++)
                <label for="choice{{ $i }}">Choice {{ $i }}</label>
                <select id="choice{{ $i }}" name="choices[{{ $i }}]" class="form-select">
                    <option disabled selected>Select a course</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            @endfor

            <button type="submit" class="btn btn-primary mt-3">Submit Choices</button>
        </form>
    </div>
</body>

</html>
