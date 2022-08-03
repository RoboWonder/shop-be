<?php

namespace App\Http\Controllers;

use App\Constants\Message;
use App\Services\UserPasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPasswordController extends UserAuthController
{
    protected $userPasswordResetService;

    public function __construct(UserPasswordResetService $userAuthService)
    {
        $this->userPasswordResetService = $userAuthService;
    }

    /***
     * request a temporary password
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @since: 2022/08/02 22:54
     */
    public function forgot(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $ok = $this->userPasswordResetService->doForgot($request->email);

        if ($ok) {
            return response()->json([
                'success' => TRUE,
                'message' => 'shopbe_sent_email'
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'shopbe_forgot_fail'
        ]);
    }

    /***
     * create a new password.
     *
     * @param Request $request
     *
     * @since: 2022/07/26 23:32
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'token' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => Message::ERR_SHOPBE_WRONG_INFORMATION,
                'errors' => $validator->errors()
            ], 422);
        }

        $ok = $this->userPasswordResetService->doReset($request->token, $request->email, $request->password);
        if (!$ok){
            return response()->json([
                'success' => FALSE,
                'message' => "shopbe_password_updated_fail"
            ], 200);
        }

        return response()->json([
            'success' => TRUE,
            'message' => "shopbe_password_updated_success"
        ], 200);
    }
}

?>
