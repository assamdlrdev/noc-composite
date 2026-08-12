<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use App\Models\LandScheduleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppCommonController extends Controller
{
    //

    public function landScheduleDetails(Request $request) {

        $app_no = $request->app_no;

        // DB::beginTransaction();

        $getLandScheduleData = new LandScheduleModel();

        $details = $getLandScheduleData->getDetails($app_no);

        return response()->json(successResponse('Successfully Retrieved Data!', $details), 200);

    }
}
