<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationEmail;
use App\Services\UserAuthService;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

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
        $this->validate($request, ['email' => 'required|email', 'password' => 'required|min:6',]);

        $email = $request->input('email');

        if ($request->has(['username'])) {
            $username = $request->input('username');
        }
        else {
            list($username) = explode('@', $email);
        }

        try {
            $user = $this->userAuthService->register([
                'username' => $username,
                'email' => $email,
                'phone_number' => $request->input('phone_number'),
                'name' => $request->has(['name']) ? $request->input('name') : $username
            ]);

            if (!$user || !$user->exists){
                throw new \Exception("shopbe_failure_created_user");
            }
        } catch (\Exception $e){
            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'shopbe_successfully_created_user',
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
        $ok = $this->userAuthService->verifyEmail($token);

        if (!$ok){
            return response()->json([
                'success' => TRUE,
                'message' => 'shopbe_user_failure_verified',
            ]);
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'shopbe_user_verified',
        ]);
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
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if(!($user->exists() && $user->verified == 1)){
            return response()->json(
                [
                    'success' => FALSE,
                    'message' => 'shopbe_must_verify_email',
                    'data' => [
                        'must'  => 'VERIFY_EMAIL'
                    ]
                ],
                401
            );
        }

        $jwtAttempt = $request->only(['email', 'password']);

        try {
            $count = (DB::table('shopbe_password_reset')
                ->where('email', '=', $request->email))
                ->where('created_at', '>', Carbon::now()->subMinute(5)->format("Y-m-d H:i:s"))
                ->first();

            if(!!$count){
                if(Hash::check($request->password, $count->token)){
                    $token = Hash::make(Str::random());

                    $user = User::where('email', $request->input('email'))->first();
                    $user->login_token = $token;
                    $user->save();

                    return response()->json([
                        'success' => TRUE,
                        'message' => 'shopbe_must_update_password',
                        'data' => [
                            'must' => 'SHOPBE_UPDATE_PASSWORD',
                            'token' => $token
                        ]
                    ]);
                }

                return response()->json([
                    'success' => TRUE,
                    'message' => 'shopbe_wrong_password'
                ]);
            }
            else{
                if (!$token = $this->jwt->attempt($jwtAttempt)) {
                    return response()->json(['user_not_found'], 404);
                }

                $user = User::where('email', $request->input('email'))->first();
                $user->auth_token = $token;
                $user->save();
            }
        } catch (JWTException $e) {
            return response()->json([
                'success' => TRUE,
                'failed_to_create_token'
            ], 500);
        }

        return response()->json(compact('token'));
    }

    /***
     * logout
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|void
     * @since: 2022/07/25 21:58
     */
    public function logout(Request $request)
    {
        // Nulling all tokens and invalidate auth_token with JWT.
        $token = substr($request->header('Authorization'), 7);

        $user = User::where('auth_token', $token)->first();
        if ($user === NULL) {
            return response()->json([
                'success' => FALSE,
                'message' => "shopbe_user_not_found"
            ], 404);
        }

        $user->auth_token = NULL;

        if ($token != NULL) {
            $this->jwt->setToken($token)->invalidate();
            $user->save();

            return response()->json([
                'success' => TRUE,
                'message' => "shopbe_user_logout_successfully"
            ]);
        }
    }
}
