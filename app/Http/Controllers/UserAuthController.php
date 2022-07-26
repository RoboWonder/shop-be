<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationEmail;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class UserAuthController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
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

        $user = new User;
        $user->username = $username;
        $user->email = $email;
        $user->password = app('hash')->make($request->input('password'));
        $user->phone_number = $request->input('phone_number');
        $user->email_token = base64_encode('TOKEN:' . $email);

        if ($request->has(['name'])) {
            $user->name = $request->input('name');
        }
        else {
            $user->name = $username;
        }

        try {
            if (!$user->save()) {
                return response()->json(['message' => 'Failure created user', 'status' => FALSE, 'data' => NULL], 200);
            }
        } catch (QueryException $exception) {
            return response()->json(['message' => 'Duplicate email', 'status' => FALSE, 'data' => NULL], 200);
        }

        // send email to confirm here.
        dispatch(new SendVerificationEmail($user));

        return response()->json(['message' => 'Successfully created user', 'status' => TRUE, 'data' => $user], 200);
    }

    /***
     * verifyEmail
     *
     * @param $token
     *
     * @return \Illuminate\Http\JsonResponse|void
     * @since: 2022/07/25 23:16
     */
    public function verifyEmail($token)
    {
        $user = User::where('email_token', $token)->firstOrFail();

        $user->isVerified = TRUE;

        if ($user->save()) {
            return response()->json(['message' => 'successfully verified', 'isVerified' => $user->isVerified,], 200);
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
        if ($withEmail = $request->filled(['email', 'password'])) {
            $this->validate($request, ['email' => 'required|email|max:255', 'password' => 'required']);
            $jwtAttempt = $request->only(['email', 'password']);
        }
        else {
            $this->validate($request, ['username' => 'required|string|regex:/\w*$/|max:255|unique:shopbe_users,username', 'password' => 'required']);
            $jwtAttempt = $request->only(['username', 'password']);
        }

        try {
            if ($withEmail) {
                if (!$token = $this->jwt->attempt($jwtAttempt)) {
                    return response()->json(['user_not_found'], 404);
                }
            }
            /*else{
                $user = User::where('username', $request->input('username'))->first();
                if (!$token = $this->jwt->fromUser($user)) {
                    return response()->json(['user_not_found'], 404);
                }
            }*/
        } catch (JWTException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }

        $user = User::where('email', $request->input('email'))->first();
        $user->auth_token = $token;
        $user->save();

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
            return response()->json(['message' => "user_not_found"], 404);
        }

        $user->auth_token = NULL;

        if ($token != NULL) {
            $this->jwt->setToken($token)->invalidate();
            $user->save();

            return response()->json(['message' => "user_logout_successfully"], 200);
        }
    }
}
