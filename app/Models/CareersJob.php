<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class CareersJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_title',
        'job_details',
        'responsibilities',
        'vacancies',
        'job_type',
        'expiry_date',
        'category',
        'employment_type',
        'experience_level',
        'salary_type',
        'salary',
        'office_time',
        'show_on_career_page',
        'requested_origin',
    ];

    /**
     * Boot method to attach model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set the requested_origin field on create
        static::creating(function ($job) {
            $job->requested_origin = Request::ip(); // Captures the IP address of the request
        });
    }
}
