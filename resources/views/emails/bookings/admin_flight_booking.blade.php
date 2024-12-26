<!DOCTYPE html>
<html>
<head>
    <title>New Flight Booking Notification</title>
</head>
<body>
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
</body>
</html>
