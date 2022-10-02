<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Services\UserAuthService;
use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    protected $userAuthService;

    public function __construct(UserAuthService $userAuthService)
    {
        $this->userAuthService = $userAuthService;
    }

    /***
     * register
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @since: 2022/07/25 23:13
     */
    public function register(Request $request)
    {
        $this->validate($request, ['phone_number' => 'required', 'password' => 'required|min:6']);
        // $email = $request->input('email');
        $phone = $request->input('phone_number');
        
        if(!(str_starts_with($phone, '01') && strlen($phone) == 11 || ((str_starts_with($phone, '09') || str_starts_with($phone, '09')) && strlen($phone) == 10))){

        }

        try {
            $user = $this->userAuthService->doRegister([
                'password' => $request->input('password'),
                'phone_number' => $request->input('phone_number')
                // 'name' => $request->has(['name']) ? $request->input('name') : $username
            ]);
        } catch (\Exception $e){
            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => Message::MSG_SHOPBE_CREATE_SUCCESS,
            'data' => $user
        ]);
    }

    /***
     * verifyEmail
     *
     * @param $token
     *
     * @return \Illuminate\Http\JsonResponse|void
     * @since: 2022/07/25 23:16
     */
    public function verifyEmail(string $token)
    {
        try {
            $ok = $this->userAuthService->doVerifyEmail($token);
            if($ok){
                return response()->json([
                    'success' => TRUE,
                    'message' => Message::MSG_SHOPBE_VERIFIED_SUCCESS,
                ]);
            }
            return response()->json([
                'success' => FALSE,
                'message' => Message::MSG_SHOPBE_VERIFIED_FAIL,
            ]);
        }
        catch (\Exception $e){
            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /***
     * login
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @since: 2022/07/25 21:57
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'required',
            'password' => 'required'
        ]);

        try {
            list($loginType, $token) = $this->userAuthService->doLogin($request->input('phone_number'), $request->>input('password'));

            if($loginType === UserAuthService::TYPE_LOGIN_TEMP){
                return response()->json([
                    'success' => TRUE,
                    'message' => 'shopbe_must_update_password',
                    'data' => [
                        'must' => 'SHOPBE_UPDATE_PASSWORD',
                        'token' => $token
                    ]
                ]);
            }
            else {
                return response()->json([
                    'success' => TRUE,
                    'message' => 'shopbe_login_success',
                    'data' => [
                        'token' => $token
                    ]
                ]);
            }
        }
        catch (\Exception $e){
            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage()
            ]);
        }
    }

    /***
     * logout
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @since: 2022/08/02 21:57
     */
    public function logout(Request $request)
    {
        try {
            $token = substr($request->header('Authorization'), 7);
            $this->userAuthService->doLogout($token);
        }
        catch (\Exception $e){
            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => "shopbe_user_logout_success"
        ]);
    }
}
