<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Send an email using the specified SMTP configuration.
     *
     * @param string $email The recipient's email address.
     * @param string $subject The email subject.
     * @param string $view The Blade template for the email.
     * @param array $data The data to pass to the Blade template.
     * @param string|null $fromEmail The sender's email address (optional).
     * @param string|null $fromName The sender's name (optional).
     */
    public static function sendMail($email, $subject, $view, $data, $fromEmail = null, $fromName = null)
    {
        // Set default from email and name if not provided
        $fromEmail = $fromEmail ?? config('mail.from.address');
        $fromName = $fromName ?? config('mail.from.name');

        // Send the email using the 'smtp1' mailer
        Mail::mailer('smtp1')->send($view, $data, function ($message) use ($email, $subject, $fromEmail, $fromName) {
            $message->to($email) // Set the recipient
                    ->subject($subject) // Set the subject
                    ->from($fromEmail, $fromName); // Set the from email and name
        });
    }
}
