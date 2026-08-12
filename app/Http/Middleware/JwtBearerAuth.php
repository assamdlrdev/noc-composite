<?php

namespace App\Http\Middleware;

use App\Services\Api\V1\UpsApiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtBearerAuth
{
    public function __construct(private UpsApiService $upsService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $posting_code =  $request->posting_code;
        // dd($posting_code);
        if (!$token) {
            return response()->json(errorResponse('Authorization Bearer token is required.'), 401);
        }

        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            return response()->json(errorResponse('Invalid JWT token format.'), 401);
        }

        try {
            $responseMe = $this->upsService->getMe($token); 
            // dd($responseMe->json());
            if(!$responseMe->successful()) {
                return response()->json(successResponse('Unauthorized.'), 401);
            }
            
            $responseMyPostings = $this->upsService->getMyPostings($token);
            // dd($responseMyPostings->json());
            if (!$responseMyPostings->successful()) {
                return response()->json(errorResponse('Unauthorized.'), 401);
            }

            $is_correct_posting_code = false;

            foreach($responseMyPostings->json('data', []) as $posting) {
            // dd($posting);
                if ($posting['posting']['posting_code'] === $posting_code) {
                    $posting_id = $posting['posting']['id'];
                    session(['posting_id' => $posting_id]);
                    session(['posting_code' => $posting_code]);
                    session(['token' => $token]);
                    session(['role_code' => $posting['posting']['role']['role_code']]);
                    session(['keycloak_user_id'=> $responseMe->json()['keycloak_user_id']]);
                    session(['reference_location_uuid'=> $posting['posting']['reference_location_uuid']]);
                    session(['username'=> $responseMe->json()['full_name']]);
                    session(['mobile_no'=> $responseMe->json()['mobile_no']]);
                    session(['email'=> $responseMe->json()['email']]);
                    session(['actor'=> $responseMe->json()]);
                    session(['user_uuid'=> $responseMe->json()['user_uuid']]);
                    $is_correct_posting_code = true;
                    break;
                }
            }
            //    dd(session('actor'));
            if($is_correct_posting_code === false) {
                return response()->json(errorResponse('Unauthorized. No access to the specified posting.'), 401);
            }
            return $next($request);

           
            
        } catch (\Throwable $e) {
            return response()->json(successResponse('Unable to validate token at this time.'), 503);
        }
    }

}
