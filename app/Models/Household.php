<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Household extends Model
{
    protected $fillable = ['name', 'country_code', 'timezone'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(Contract::class);
    }
}
