<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LandScheduleModel extends Model
{
    protected $table = "landschedule";
    //

    public function getDetails(string $app_no) {
        $details = DB::table($this->table)
        ->where('appno', $app_no)
        ->get();

        $result = [];

        foreach ($details as $row) {
            $dist_code = $row->distcode;
            $subdiv_code = $row->subcode;
            $cir_code = $row->circode;
            $mouza_pargona_code = $row->mouzacode;
            $lot_no = $row->lotno;
            $vill_townprt_code = $row->villcode;
            $locationId = $dist_code . $subdiv_code . $cir_code . $mouza_pargona_code . $lot_no . $vill_townprt_code;

            $dist_name = "";
            $subdiv_name = "";
            $cir_name = "";
            $mouza_pargona_name = "";
            $lot_name = "";
            $vill_townprt_name = "";

            $land_class_code = $row->landclass;
            $land_class_name = "";




            $result[] = $row;
        }

        return $result;

        
    }
}
