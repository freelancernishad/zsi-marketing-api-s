<!DOCTYPE html>
<html>
<head>
    <title>New Hotel Booking Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #0046ad;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Hotel Booking Notification</h2>
        <p>Dear Admin,</p>
        <p>A new hotel booking has been made. Below are the details of the booking:</p>

        <h3>Booking Information:</h3>
        <ul>
            <li><strong>Recipient Email:</strong> {{ $email }}</li>
            <li><strong>From Email:</strong> {{ $from_email }}</li>
            <li><strong>From Name:</strong> {{ $from_name }}</li>
        </ul>

        <h3>Booking Details:</h3>
        <ul>
            <li><strong>Location:</strong> {{ $location }}</li>
            <li><strong>Hotel Name:</strong> {{ implode(', ', $hotel_name) }}</li>
            <li><strong>Adults:</strong> {{ $adults }}</li>
            <li><strong>Children:</strong> {{ $children }}</li>
            <li><strong>Rooms:</strong> {{ $rooms }}</li>
            <li><strong>Car Rental:</strong> {{ $addCar }}</li>
        </ul>

        <h3>Guest Information:</h3>
        <ul>
            <li><strong>Name:</strong> {{ $first_name }} {{ $last_name }}</li>
            <li><strong>Email:</strong> {{ $email }}</li>
            <li><strong>Phone:</strong> {{ $phone }}</li>
            <li><strong>Address:</strong> {{ $address }}, {{ $city }}, {{ $zip }}, {{ $country }}</li>
        </ul>

        <p>Please review the booking details and take necessary actions.</p>
        <p>Thank you!</p>
        <div class="footer">
            <p>This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
