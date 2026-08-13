<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerModel extends Model
{
    protected $table = "seller";


    public static function getSellerDetailsDB($app_no)
    {
        $details = DB::table('seller')
            ->where('appno', $app_no)
            ->get();

        return $details;
    }
}