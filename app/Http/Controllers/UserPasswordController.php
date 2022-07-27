<?php

namespace App\Http\Controllers;

use App\Jobs\SendPasswordEmail;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserPasswordController extends UserAuthController
{
    /***
     * request a temporary password
     *
     * @param Request $request
     *
     * @return false|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     *
     * @since: 2022/07/26 23:30
     */
    public function forgot(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        $credentials = $request->only(['email']);

        $user = User::whereEmail($credentials['email'])->first();
        if (!$user) {
            return FALSE;
        }

        $tmpPassword = Str::random(8);

        $hash = Hash::make($tmpPassword);

        DB::table('shopbe_password_reset')->whereEmail($credentials['email'])->delete();

        DB::table('shopbe_password_reset')->insert([
            'email' => $credentials['email'],
            'token' => $hash,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        dispatch(new SendPasswordEmail($user, $tmpPassword));

        return response()->json([
            'success' => TRUE,
            'message' => 'shopbe_sent_email',
            'data'  => [ // for testing
                'pw' => $tmpPassword,
                'hash'  => $hash
            ]
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
                'message' => 'shopbe_wrong_information',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where(['email' => $request->email, 'login_token' => $request->token]);

        $user->update(['password' => Hash::make($request->password), 'login_token' => NULL]);

        DB::table('shopbe_password_reset')->whereEmail($request->email)->delete();

        return new JsonResponse([
            'success' => TRUE,
            'message' => "shopbe_password_updated"
        ], 200);
    }
}

?>
