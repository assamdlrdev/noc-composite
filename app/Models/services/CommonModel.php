<?php

namespace App\Models\services;

use App\Models\LandScheduleModel;
use App\Services\LocationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommonModel extends Model
{
    //
    private LocationService $locationService;

    public function __construct()
    {
        $this->locationService = new LocationService();
    }

    public function getLandScheduleDetails(string $app_no) {
        $details = DB::table($this->table)
        ->where('appno', $app_no)
        ->get();

        $result = [];

        if(!$details->isEmpty()) {
            foreach ($details as $row) {
                $dist_code = $row->distcode;
                $subdiv_code = $row->subcode;
                $cir_code = $row->circode;
                $mouza_pargona_code = $row->mouzacode;
                $lot_no = $row->lotno;
                $vill_townprt_code = $row->villcode;
                $locationId = $dist_code . $subdiv_code . $cir_code . $mouza_pargona_code . $lot_no . $vill_townprt_code;
                $row->location_id = $locationId;

                $village_uuid = "10000000004531";
                $locations = $this->locationService->getLocationFromVillageUuid($village_uuid);
                if(!$locations['success']) {
                    return [
                        'status' => 0,
                        'message' => $locations['message']
                    ];
                }
                $data = $locations['data'];

                $dist_name = $data['district_name_eng'];
                $subdiv_name = $data['subdivision_name_eng'];
                $cir_name = $data['circle_name_eng'];
                $mouza_pargona_name = $data['mouza_name_eng'];
                $lot_name = $data['lot_name_asm'];
                $vill_townprt_name = $data['name_eng'];

                $district_uuid = $data['district_uuid'];
                $subdivision_uuid = $data['subdivision_uuid'];
                $circle_uuid = $data['circle_uuid'];
                $mouza_uuid = $data['mouza_uuid'];
                $lot_uuid = $data['lot_uuid'];
                $village_uuid = $data['uuid'];

                

                $land_class_code = $row->landclass;
                $land_class_name = "";



                $result[] = $row;
            }
        }

        return $result;

        
    }
}
