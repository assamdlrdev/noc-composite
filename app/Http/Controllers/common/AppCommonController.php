<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use App\Models\LandScheduleModel;
use App\Models\BuyerModel;
use App\Models\SellerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppCommonController extends Controller
{
    //

    public function landScheduleDetails(Request $request)
    {

        $app_no = $request->app_no;

        // DB::beginTransaction();

        $getLandScheduleData = new LandScheduleModel();

        $details = $getLandScheduleData->getDetails($app_no);

        return response()->json(successResponse('Successfully Retrieved Data!', $details), 200);
    }


    // get buyer information 
    public function getBuyerDetails(Request $request)
    {
        $request->validate([
            'app_no' => 'required|string'
        ]);

        try {

            $app_no = $request->app_no;

            $details = BuyerModel::getBuyerInfoDB($app_no);

            if (empty($details)) {
                return response()->json(
                    errorResponse('No buyer details found!', ''),
                    200
                );
            }

            return response()->json(
                successResponse('Successfully Retrieved Data!', $details),
                200
            );
        } catch (\Exception $e) {

            return response()->json(
                errorResponse('Something went wrong!', $e->getMessage()),
                500
            );
        }
    }
      // get buyer information 
      public function getSellerDetails(Request $request)
      {
          $request->validate([
              'app_no' => 'required|string'
          ]);
  
          try {
  
              $app_no = $request->app_no;
  
              $details = SellerModel::getSellerDetailsDB($app_no);
  
              if (empty($details)) {
                  return response()->json(
                      errorResponse('No buyer details found!', ''),
                      200
                  );
              }
  
              return response()->json(
                  successResponse('Successfully Retrieved Data!', $details),
                  200
              );
          } catch (\Exception $e) {
  
              return response()->json(
                  errorResponse('Something went wrong!', $e->getMessage()),
                  500
              );
          }
      }
}
