<!DOCTYPE html>
<html>
<head>
    <title>Flight Booking Confirmation</title>
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
        <h2>Flight Booking Confirmation</h2>
        <p>Dear {{ $first_name }} {{ $last_name }},</p>
        <p>Your flight booking details are as follows:</p>
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
        <p>Thank you for choosing our service!</p>

        <div class="footer">
            <p>Our team will contact you within 24 hours.</p>
            <p>Thank you for your patience.</p>
            <p>Best regards,</p>
            <p><strong>Rahmania Travels</strong></p>
            <p>Contact Us:</p>
            <p>Email: <a href="mailto:rahmaniatravel@yahoo.com">rahmaniatravel@yahoo.com</a></p>
            <p>Phone: <a href="tel:718-205-3270">718-205-3270</a></p>
        </div>
    </div>
</body>
</html>
