<?php

namespace App\Exports;

use App\Models\JobApply;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JobApplyExport implements FromCollection, WithHeadings, WithStrictNullComparison, WithMapping, WithStyles
{
    /**
     * Return the collection of data to be exported.
     */
    public function collection()
    {
        return JobApply::all();
    }

    /**
     * Define the headings for the Excel file.
     */
    public function headings(): array
    {
        return [
            'ID', 'Full Name', 'Email', 'Phone', 'Cover Letter', 'Resume',
            'Job Title', 'Job Details', 'Responsibilities', 'Vacancies',
            'Job Type', 'Expiry Date', 'Category', 'Employment Type',
            'Experience Level', 'Salary Type', 'Salary', 'Office Time',
            'Show on Career Page', 'Requested Origin', 'Application ID',
            'Careers Job ID', 'Status', 'Created At', 'Updated At'
        ];
    }

    /**
     * Format each row of data (with custom formatting).
     */
    public function map($jobApply): array
    {
        return [
            $jobApply->id,
            $jobApply->full_name,
            $jobApply->email,
            $jobApply->phone,
            $jobApply->cover_letter,
            $this->formatResume($jobApply->resume),
            $jobApply->job_title,
            $jobApply->job_details,
            $jobApply->responsibilities,
            $jobApply->vacancies,
            $jobApply->job_type,
            $this->formatDate($jobApply->expiry_date),
            $jobApply->category,
            $jobApply->employment_type,
            $jobApply->experience_level,
            $jobApply->salary_type,
            $jobApply->salary,
            $jobApply->office_time,
            $jobApply->show_on_career_page ? 'Yes' : 'No',
            $jobApply->requested_origin,
            $jobApply->application_id,
            $jobApply->careers_job_id,
            ucfirst($jobApply->status),
            $this->formatDate($jobApply->created_at),
            $this->formatDate($jobApply->updated_at),
        ];
    }

    /**
     * Format the resume field (e.g., link to the file or display name).
     */
    private function formatResume($resume)
    {
        return $resume ? asset('storage/' . $resume) : 'No Resume';
    }

    /**
     * Format the date in a custom format.
     */
    private function formatDate($date)
    {
        // Ensure that the date is a Carbon instance
        if ($date instanceof \Carbon\Carbon) {
            return $date->format('Y-m-d H:i:s');
        }

        // If the date is a string, we convert it to a Carbon instance
        return \Carbon\Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    /**
     * Apply custom styles to the sheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]], // Bold the first row (headings)
            'A1:Z1' => ['alignment' => ['horizontal' => 'center']], // Center-align all headings
            'A1:Z1' => ['font' => ['size' => 12]], // Font size for the headers
        ];
    }
}
