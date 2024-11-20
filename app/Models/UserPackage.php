<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    protected $fillable = ['user_id', 'package_id', 'started_at', 'ends_at'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }


    // Add the relationship to UserPackageAddon (one UserPackage can have many Addons)
    public function addons()
    {
        return $this->hasMany(UserPackageAddon::class);
    }
}
