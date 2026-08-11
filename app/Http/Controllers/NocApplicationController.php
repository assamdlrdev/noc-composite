<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

class NocApplicationController extends Controller
{
    #[OA\Post(
        path: '/noc/applications',
        summary: 'Get NOC applications for a posting',
        tags: ['NOC Applications'],
        parameters: [
            new OA\Parameter(
                name: 'posting_uuid',
                in: 'query',
                description: 'UUID of the posting',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'filter',
                in: 'query',
                description: 'Filter applications by status',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: [
                        'inbox',
                        'pending',
                        'approved',
                        'returned',
                        'rejected',
                        'query_sent',
                        'query_responded'
                    ]
                )
            ),

            new OA\Parameter(
                name: 'search',
                in: 'query',
                description: 'Search applications by application number',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'page',
                in: 'query',
                description: 'Page number',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    default: 1
                )
            ),

            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                description: 'Number of records per page',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 100,
                    default: 10
                )
            ),

            new OA\Parameter(
                name: 'action_filter',
                in: 'query',
                description: 'Filter by action code',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'distcode',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'subcode',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'circode',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'mouzacode',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'lotno',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            ),

            new OA\Parameter(
                name: 'villcode',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of NOC applications'
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
    public function index(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Validate Request
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'posting_uuid' => [
                    'required',
                    'string'
                ],

                'filter' => [
                    'nullable',
                    'string'
                ],

                'search' => [
                    'nullable',
                    'string'
                ],

                'page' => [
                    'nullable',
                    'integer',
                    'min:1'
                ],

                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100'
                ],

                'role_filter' => [
                    'nullable',
                    'string'
                ],

                'action_filter' => [
                    'nullable',
                    'string'
                ],

                'distcode' => [
                    'nullable',
                    'string'
                ],

                'subcode' => [
                    'nullable',
                    'string'
                ],

                'circode' => [
                    'nullable',
                    'string'
                ],

                'mouzacode' => [
                    'nullable',
                    'string'
                ],

                'lotno' => [
                    'nullable',
                    'string'
                ],

                'villcode' => [
                    'nullable',
                    'string'
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Request Values
            |--------------------------------------------------------------------------
            */

            $postingUuid = trim(
                $validated['posting_uuid']
            );

            $filter = $validated['filter'] ?? 'inbox';

            $search = trim(
                $validated['search'] ?? ''
            );

            $page = (int) (
                $validated['page'] ?? 1
            );

            $perPage = (int) (
                $validated['per_page'] ?? 10
            );

            /*
            |--------------------------------------------------------------------------
            | Status Filter Map
            |--------------------------------------------------------------------------
            */

            $filterStatusMap = [
                'pending' => [
                    'New',
                    'Under Process',
                ],

                'approved' => [
                    'Issued',
                ],

                'returned' => [
                    'Sent Back',
                ],

                'rejected' => [
                    'Rejected',
                ],

                'query_sent' => [
                    'Queried',
                ],

                'query_responded' => [
                    'Completed',
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | LM Report Count
            |--------------------------------------------------------------------------
            */

            $ntrSub = DB::table('lmreport')
                ->select(
                    'appno',
                    DB::raw('COUNT(*) as cnt')
                )
                ->groupBy('appno');

            /*
            |--------------------------------------------------------------------------
            | Base Query
            |--------------------------------------------------------------------------
            */

            $base = DB::table('landsale as ls')

                ->join(
                    'noc_track_application as nta',
                    'nta.appno',
                    '=',
                    'ls.appno'
                )

                ->leftJoinSub(
                    $ntrSub,
                    'ntr',
                    'ntr.appno',
                    '=',
                    'ls.appno'
                )

                ->selectRaw("
                    ls.slno,
                    ls.appno,
                    ls.appdate,
                    ls.coforward,
                    ls.cofordate,
                    ls.boallowed,
                    ls.forremarks,
                    ls.lmretdate,
                    ls.lmretrem,
                    ls.compserv,
                    ls.lmcode,
                    ls.lmname,
                    ls.lmforrem,

                    nta.posting_code,
                    nta.action_taken AS action_code,
                    nta.curr_status AS application_status,

                    nta.distcode,
                    nta.subcode,
                    nta.circode,
                    nta.mouzacode,
                    nta.lotno,
                    nta.villcode,

                    COALESCE(ntr.cnt, 0) AS ntr
                ")

                ->where(
                    'ls.compserv',
                    'Y'
                )

                ->where(
                    'ls.epay',
                    'Y'
                )

                ->where(
                    'nta.posting_code',
                    $postingUuid
                );

            /*
            |--------------------------------------------------------------------------
            | Count Query
            |--------------------------------------------------------------------------
            */

            $countQuery = DB::table('landsale as ls')

                ->join(
                    'noc_track_application as nta',
                    'nta.appno',
                    '=',
                    'ls.appno'
                )

                ->where(
                    'ls.compserv',
                    'Y'
                )

                ->where(
                    'ls.epay',
                    'Y'
                )

                ->where(
                    'nta.posting_code',
                    $postingUuid
                );

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            if ($search !== '') {

                $base->where(
                    'ls.appno',
                    'like',
                    '%' . $search . '%'
                );

                $countQuery->where(
                    'ls.appno',
                    'like',
                    '%' . $search . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Status Filter
            |--------------------------------------------------------------------------
            */

            if (
                $filter !== 'inbox' &&
                isset($filterStatusMap[$filter])
            ) {

                $base->whereIn(
                    'nta.curr_status',
                    $filterStatusMap[$filter]
                );

                $countQuery->whereIn(
                    'nta.curr_status',
                    $filterStatusMap[$filter]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Action Filter
            |--------------------------------------------------------------------------
            */

            if (!empty($validated['action_filter'])) {

                $actionFilter = trim(
                    $validated['action_filter']
                );

                $base->where(
                    'nta.action_taken',
                    $actionFilter
                );

                $countQuery->where(
                    'nta.action_taken',
                    $actionFilter
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Role / Action Filter
            |--------------------------------------------------------------------------
            */

            if (!empty($validated['role_filter'])) {

                $roleFilter = trim(
                    $validated['role_filter']
                );

                $receivedActionsByRole = config(
                    'constants.RECEIVED_ACTIONS_BY_URCODE',
                    []
                );

                $roleActions = $receivedActionsByRole[$roleFilter] ?? [];

                if (
                    !empty($validated['action_filter']) &&
                    in_array(
                        $validated['action_filter'],
                        $roleActions,
                        true
                    )
                ) {

                    $base->where(
                        'nta.action_taken',
                        $validated['action_filter']
                    );

                    $countQuery->where(
                        'nta.action_taken',
                        $validated['action_filter']
                    );

                } elseif (!empty($roleActions)) {

                    $base->whereIn(
                        'nta.action_taken',
                        $roleActions
                    );

                    $countQuery->whereIn(
                        'nta.action_taken',
                        $roleActions
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Location Filters
            |--------------------------------------------------------------------------
            */

            $locationFilters = [
                'distcode' => 'distcode',
                'subcode' => 'subcode',
                'circode' => 'circode',
                'mouzacode' => 'mouzacode',
                'lotno' => 'lotno',
                'villcode' => 'villcode',
            ];

            foreach (
                $locationFilters as $requestKey => $column
            ) {

                if (
                    !empty(
                    $validated[$requestKey]
                )
                ) {

                    $value = trim(
                        $validated[$requestKey]
                    );

                    $base->where(
                        'nta.' . $column,
                        $value
                    );

                    $countQuery->where(
                        'nta.' . $column,
                        $value
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Status Counts
            |--------------------------------------------------------------------------
            */

            $statusCounts = (clone $countQuery)

                ->select(
                    'nta.curr_status'
                )

                ->selectRaw(
                    'COUNT(*) as total'
                )

                ->groupBy(
                    'nta.curr_status'
                )

                ->pluck(
                    'total',
                    'curr_status'
                )

                ->toArray();

            /*
            |--------------------------------------------------------------------------
            | Counts
            |--------------------------------------------------------------------------
            */

            $counts = [
                'inbox' => (clone $countQuery)->count(),
                'pending' => 0,
                'approved' => 0,
                'returned' => 0,
                'rejected' => 0,
                'query_sent' => 0,
                'query_responded' => 0,
            ];

            foreach (
                $filterStatusMap as $filterKey => $statuses
            ) {

                foreach ($statuses as $status) {

                    $counts[$filterKey] +=
                        $statusCounts[$status] ?? 0;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Total Records
            |--------------------------------------------------------------------------
            */

            $total = (clone $base)->count();

            /*
            |--------------------------------------------------------------------------
            | Fetch Records
            |--------------------------------------------------------------------------
            */

            $rows = $base

                ->orderBy(
                    'ls.appdate',
                    'desc'
                )

                ->offset(
                    ($page - 1) * $perPage
                )

                ->limit(
                    $perPage
                )

                ->get();

            /*
            |--------------------------------------------------------------------------
            | Response Mapping
            |--------------------------------------------------------------------------
            */

            $nocActions = config(
                'constants.NOC_USER_ACTIONS',
                []
            );

            $rows = $rows->map(
                function ($row) use ($nocActions) {

                    /*
                    |--------------------------------------------------------------------------
                    | Human-readable Action
                    |--------------------------------------------------------------------------
                    */

                    $row->status = $row->action_code
                        ? (
                            $nocActions[
                                $row->action_code
                            ] ?? $row->action_code
                        )
                        : '';

                    /*
                    |--------------------------------------------------------------------------
                    | Application Status
                    |--------------------------------------------------------------------------
                    */

                    $row->application_status =
                        $row->application_status ?? '';

                    /*
                    |--------------------------------------------------------------------------
                    | Encrypted Application Number
                    |--------------------------------------------------------------------------
                    */

                    $row->appno_encrypted = urlencode(
                        Crypt::encryptString(
                            $row->appno
                        )
                    );

                    return $row;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Pagination Meta
            |--------------------------------------------------------------------------
            */

            $meta = [
                'total' => $total,

                'page' => $page,

                'per_page' => $perPage,

                'total_pages' => max(
                    1,
                    (int) ceil(
                        $total / $perPage
                    )
                ),
            ];

            /*
            |--------------------------------------------------------------------------
            | Final Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => 1,

                'message' => $rows->isEmpty()
                    ? 'No records found'
                    : 'Successful',

                'data' => $rows,

                'meta' => $meta,

                'counts' => $counts,
            ]);

        } catch (
            \Illuminate\Validation\ValidationException $e
        ) {

            throw $e;

        } catch (\Exception $e) {

            Log::error(
                'NOC application API error',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            return response()->json([
                'status' => 0,

                'message' => 'Database error',

                'data' => [],
            ], 500);
        }
    }
}