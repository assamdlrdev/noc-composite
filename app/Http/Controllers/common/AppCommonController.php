<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\Controller;
use App\Models\LandScheduleModel;
use App\Models\BuyerModel;
use App\Models\SellerModel;
use App\Models\LspApplicant;
use App\Models\LandSale;
use App\Models\NocTrackApplication;
use App\Models\services\CommonModel;
use App\Services\ApplicationWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AppCommonController extends Controller
{

    public function landScheduleDetails(Request $request)
    {

        $app_no = $request->app_no;

        // DB::beginTransaction();

        $commonModel = new CommonModel();

        $details = $commonModel->getLandScheduleDetails($app_no);

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
    #[OA\Post(
        path: '/application/view',
        summary: 'View application details',
        description: 'Fetches application details using the application number. The API retrieves applicant information, associated land sale details, and the latest tracking status of the application.',
        tags: ['Applications Details'],
        security: [
            ['bearerAuth' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'appno',
                in: 'query',
                description: 'Unique application number used to retrieve the application details.',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Applications Details Retrieved Successfully',
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]
    public function viewApplication(Request $request)
    {
        $request->validate([
            'appno' => ['required', 'string'],
        ]);

        try {
            $appno = $request->appno;

            $data = LspApplicant::query()
                ->join(
                    (new LandSale)->getTable(),
                    'lspapplicant.appno',
                    '=',
                    'landsale.appno'
                )
                ->leftJoinSub(
                    NocTrackApplication::query()
                        ->select(
                            'appno',
                            'action_taken',
                            'user_desig_code'
                        )
                        ->where('appno', $appno)
                        ->orderByDesc('id')
                        ->limit(1),
                    'track',
                    function ($join) {
                        $join->on(
                            'track.appno',
                            '=',
                            'lspapplicant.appno'
                        );
                    }
                )
                ->where('lspapplicant.appno', $appno)
                ->select(
                    'lspapplicant.*',
                    'track.action_taken as action_code',
                    'track.user_desig_code as user_desig'
                )
                ->get();

            if ($data->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No application found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Application found',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    #[OA\Post(
        path: '/applications/workflow-status',
        summary: 'View application workflow status.',
        description: 'Fetches status/workflow details using the application number. The API retrieves all the details related to the workflow.',
        tags: ['Applications Details'],
        security: [
            ['bearerAuth' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'appno',
                in: 'query',
                description: 'Unique application number used to retrieve the application details.',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Applications Details Retrieved Successfully',
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]
    public function status(Request $request): JsonResponse
    {
        $request->validate([
            'appno' => ['required', 'string', 'max:100'],
        ]);
        $appno = $request->input('appno');
        $result = $this->workflowService->getWorkflowStatus($appno);

        if (($result['exists'] ?? false) === false) {
            return response()->json(errorResponse($result['message'], null), 404);
        }

        return response()->json(successResponse('Workflow status retrieved successfully.', $result), 200);
    }
}
