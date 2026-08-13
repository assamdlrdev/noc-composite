<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\LocationService;
use Override;

class LandScheduleModel extends Model
{
    protected $table = "landschedule";

    public $timestamps = false;

    protected $guarded = [];
    //

    
}
