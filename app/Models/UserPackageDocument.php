<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserPackageDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userpackage_id',
        'uploaded_date',
        'file',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'uploaded_date' => 'date',
    ];

    /**
     * Relationship: A UserPackageDocument belongs to a UserPackage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class, 'userpackage_id');
    }

    /**
     * Upload a file and save the file path to the model.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $type
     * @return string
     */
    public function saveDocumentFile($file, $type)
    {
        // Define the S3 directory based on the type (document or report)
        $s3Directory = 'user_package_documents/' . $type;

        // Upload the file to S3
        $filePath = uploadFileToS3($file, $s3Directory);

        // Save the file path to the model
        $this->file = $filePath;
        $this->type = $type;
        $this->uploaded_date = now()->toDateString();
        $this->save();

        return $filePath;
    }

    /**
     * Delete the file from storage.
     *
     * @return void
     */
    public function deleteFileFromStorage()
    {
        if ($this->file && Storage::disk('s3')->exists($this->file)) {
            Storage::disk('s3')->delete($this->file);
        }
    }
}
