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
        if (isset($data['multiDestinations']) && is_array($data['multiDestinations'])) {
            foreach ($data['multiDestinations'] as $index => $destination) {
                $multiDestinations[$index] = [
                    'departure' => $destination['departure'],
                    'destination' => $destination['destination'],
                    'date' => $destination['date'],
                ];
            }
        }

        // Map trip_type to its corresponding value
        $tripTypeMap = [
            1 => 'One-way',
            2 => 'Round-trip',
            3 => 'Multi-destination',
        ];
        $tripType = $tripTypeMap[$data['trip_type']] ?? 'Unknown';

        // Prepare data for the email
        $emailData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'from_email' => 'rahmaniatravel@zsi.ai',
            'from_name' => 'Rahmania Travel',
            'airline' => $data['airline'] ?? 'Not specified', // Handle if airline is not provided
            'trip_type' => $tripType,
            'flight_class' => $data['flight_class'],
            'adults' => $data['adults'],
            'children' => $data['children'],
            'phone' => $data['phone'] ?? 'Not provided', // Handle if phone is not provided
            'address' => $data['address'] ?? 'Not provided', // Handle if address is not provided
            'city' => $data['city'] ?? 'Not provided', // Handle if city is not provided
            'zip' => $data['zip'] ?? 'Not provided', // Handle if zip is not provided
            'country' => $data['country'] ?? 'Not provided', // Handle if country is not provided
            'multiDestinations' => array_values($multiDestinations), // Ensure multiDestinations is an array
        ];

        // Send email to the customer
        MailService::sendMail(
            $data['email'],
            'Flight Booking Confirmation',
            'emails.bookings.flight_booking',
            $emailData,
            $emailData['from_email'],
            $emailData['from_name'],
        );

        // Send email to the admin
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
