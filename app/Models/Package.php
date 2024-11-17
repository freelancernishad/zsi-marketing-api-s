<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['name', 'description', 'price', 'duration_days', 'features'];

    // Accessor to get features as an array
    public function getFeaturesAttribute($value)
    {
        return json_decode($value, true);
    }

    // Mutator to set features as JSON
    public function setFeaturesAttribute($value)
    {
        $this->attributes['features'] = json_encode($value);
    }
}
