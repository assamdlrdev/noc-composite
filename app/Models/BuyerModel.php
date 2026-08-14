<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BuyerModel extends Model
{
    protected $table = "buyer";

    public $timestamps = false;

    protected $guarded = [];

    public static function getBuyerInfoDB($app_no)
    {
        $details = DB::table('buyer')
            ->where('appno', $app_no)
            ->get();

        return $details;
    }
}
