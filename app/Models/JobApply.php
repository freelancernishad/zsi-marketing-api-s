<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobApply extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'cover_letter',
        'resume',


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
        'application_id',
        'careers_job_id',
        'status',
    ];

    /**
     * Boot method to auto-fill requested_origin.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jobApply) {
            $jobApply->requested_origin = Request::ip();
            $jobApply->application_id = 'ZSI-' . strtoupper(Str::random(8));
        });
    }



    public function saveResume($file)
    {
        $filePath = uploadFileToS3($file, 'job/apply/resume'); // Define the S3 directory
        $this->resume = $filePath;
        $this->save();

        return $filePath;
    }


}
