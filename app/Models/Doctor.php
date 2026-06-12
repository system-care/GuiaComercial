<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zap\Models\Concerns\HasSchedules;

class Doctor extends Model
{
    use HasSchedules;

    protected $fillable = ['name', 'specialty'];
}
