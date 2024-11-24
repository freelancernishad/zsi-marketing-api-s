<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'assigned_to',
        'event_start_time',
        'event_end_time',
        'invitee_first_name',
        'invitee_last_name',
        'invitee_full_name',
        'invitee_email',
        'answers',  // Allow answers as fillable
        'user_id',
        'admin_id',
    ];

    protected $casts = [
        'answers' => 'array',  // Automatically cast the answers field to an array
    ];

     /**
     * Get the user who created the schedule.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who manages the schedule (optional).
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
