<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    protected $fillable = ['user_id', 'package_id', 'started_at', 'ends_at'];

    /**
     * Relationship: A UserPackage belongs to a User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A UserPackage belongs to a Package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Relationship: A UserPackage has many UserPackageAddons.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addons()
    {
        return $this->hasMany(UserPackageAddon::class);
    }

    /**
     * Relationship: A UserPackage has many Addons through UserPackageAddon.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function addonsDetails()
    {
        return $this->hasManyThrough(PackageAddon::class, UserPackageAddon::class, 'user_package_id', 'id', 'id', 'addon_id');
    }

    /**
     * Relationship: A UserPackage has many payments through UserPackageAddon.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, UserPackageAddon::class, 'user_package_id', 'id', 'id', 'payment_id');
    }
}
