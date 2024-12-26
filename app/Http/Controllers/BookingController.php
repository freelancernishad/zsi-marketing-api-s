<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MailService;

class BookingController extends Controller
{
    public function bookHotel(Request $request)
    {
        $data = $request->all();

        // Prepare data for the email
        $emailData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'city' => $data['city'],
            'zip' => $data['zip'],
            'country' => $data['country'],
            'from_email' => 'rahmaniatravel@zsi.ai',
            'from_name' => 'Rahmania Travel',
            'location' => $data['location'],
            'hotel_name' => $data['hotel_name'],
            'dates' => $data['dates'],
            'adults' => $data['adults'],
            'children' => $data['children'],
            'rooms' => $data['rooms'],
            'addCar' => $data['addCar'],
        ];

        // Send email
        MailService::sendMail(
            $data['email'],
            'Hotel Booking Confirmation',
            'emails.bookings.hotel_booking',
            $emailData,
            $emailData['from_email'],
            $emailData['from_name'],
        );

        // Send email
        MailService::sendMail(
            'freelancernishad123@gmail.com',
            'New Hotel Booking Notification',
            'emails.bookings.admin_hotel_booking',
            $emailData,
            $emailData['from_email'],
            $emailData['from_name'],
        );

        return response()->json(['message' => 'Hotel booking email sent successfully!']);
    }

    public function bookFlight(Request $request)
    {
        $data = $request->all();

        // Prepare multiDestinations data
        $multiDestinations = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'multiDestinations') !== false) {
                $parts = explode('.', $key);
                $index = str_replace('multiDestinations[', '', $parts[0]);
                $index = str_replace(']', '', $index);
                $field = $parts[1];
                $multiDestinations[$index][$field] = $value;
            }
        }

        // Prepare data for the email
        $emailData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'from_email' => 'rahmaniatravel@zsi.ai',
            'from_name' => 'Rahmania Travel',
            'airline' => $data['airline'],
            'trip_type' => $data['trip_type'],
            'flight_class' => $data['flight_class'],
            'adults' => $data['adults'],
            'children' => $data['children'],
            'multiDestinations' => array_values($multiDestinations),
        ];

        // Send email
        MailService::sendMail(
            $data['email'],
            'Flight Booking Confirmation',
            'emails.bookings.flight_booking',
            $emailData,
            $emailData['from_email'],
            $emailData['from_name'],
        );

        // Send email
        MailService::sendMail(
            'freelancernishad123@gmail.com',
            'New Flight Booking Notification',
            'emails.bookings.admin_flight_booking',
            $emailData,
            $emailData['from_email'],
            $emailData['from_name'],
        );

        return response()->json(['message' => 'Flight booking email sent successfully!']);
    }
}
