<!DOCTYPE html>
<html>
<head>
    <title>New Flight Booking Notification</title>
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
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Flight Booking Notification</h2>
        <p>Dear Admin,</p>
        <p>A new flight booking has been made. Here are the details:</p>

        <h3>Booking Information:</h3>
        <ul>
            <li><strong>Recipient Email:</strong> {{ $email }}</li>
            <li><strong>From Email:</strong> {{ $from_email }}</li>
            <li><strong>From Name:</strong> {{ $from_name }}</li>
        </ul>

        <h3>Flight Booking Data:</h3>
        <ul>
            <li><strong>Airline:</strong> {{ $airline }}</li>
            <li><strong>Trip Type:</strong> {{ $trip_type }}</li>
            <li><strong>Flight Class:</strong> {{ $flight_class }}</li>
            <li><strong>Adults:</strong> {{ $adults }}</li>
            <li><strong>Children:</strong> {{ $children }}</li>
            <li><strong>Destinations:</strong>
                <ul>
                    @foreach ($multiDestinations as $destination)
                        <li>{{ $destination['departure'] }} to {{ $destination['destination'] }} on {{ $destination['date'] }}</li>
                    @endforeach
                </ul>
            </li>
        </ul>

        <p>Please review the booking details and take necessary actions.</p>
        <p>Thank you!</p>

        <div class="footer">
            <p>Best regards,</p>
            <p><strong>Rahmania Travels</strong></p>
            <p>Contact Us:</p>
            <p>Email: <a href="mailto:rahmaniatravel@yahoo.com">rahmaniatravel@yahoo.com</a></p>
            <p>Phone: <a href="tel:718-205-3270">718-205-3270</a></p>
        </div>
    </div>
</body>
</html>
