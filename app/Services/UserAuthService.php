<?php
/**
 * Created by PhpStorm.
 * User: Hung <hunglt@hanbiro.vn>
 * Date: 2022-08-01
 * Time: 22:17
 */

namespace App\Services;

use App\Jobs\SendVerificationEmail;
use App\Repositories\UserPasswordResetRepository;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Boolean;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

class UserAuthService
{
    protected $jwt;
    protected $userRepository;
    protected $userPasswordResetRepository;

    public function __construct(JWTAuth $jwt, UserRepository $userRepository, UserPasswordResetRepository $userPasswordResetRepository)
    {
        $this->jwt = $jwt;
        $this->userRepository = $userRepository;
        $this->userPasswordResetRepository = $userPasswordResetRepository;
    }

    /***
     * register
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     * @since: 2022/07/25 23:13
     */
    public function register(array $args): User
    {
        $user = new User;
        $user->username = $args['username'];
        $user->name = $args['name'];
        $user->email = $args['email'];
        $user->password = Hash::make($args['password']);
        $user->phone_number = $args['phone_number'];
        $user->email_token = base64_encode('TOKEN:' . $args['email']);

        try {
            if (!$user->save()) {
                throw new \Exception('shopbe_failure_created_user');
            }
        } catch (QueryException $exception) {
            throw new \Exception("shopbe_duplicated_email");
        }

        // send email to confirm here.
        dispatch(new SendVerificationEmail($user));

        return $user;
    }

    /***
     * verifyEmail
     *
     * @param $token
     *
     * @return bool
     * @since: 2022/07/25 23:16
     */
    public function verifyEmail(string $token): bool
    {
        $user = User::where('email_token', $token)->firstOrFail();
        $user->verified = TRUE;
        $user->email_token = NULL;

        return $user->save();
    }

    private function loginOrLoginTemp($login, $loginTemp){
        if ()P
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
    public function login(string $email, string $password)
    {
        $user = $this->userRepository->findWhere(['email' => $email])->first();
        if(!($user->exists() && $user->verified == 1)){
            throw new \Exception('shopbe_must_verify_email', 401);
        }

        $jwtAttempt = compact('email', 'password');

        try {
            $reset = $this->userPasswordResetRepository->findWhere([
                ['email', '=', $email],
                ['created_at', '>', Carbon::now()->subMinute(5)->format("Y-m-d H:i:s")]
            ])->first();

            if(!!$reset){
                if(Hash::check($password, $reset->token)){
                    $token = Hash::make(Str::random());
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

                $user = User::where('email', $email)->first();
                $user->auth_token = $token;
                $user->save();
            }
        } catch (JWTException $e) {
            throw new \Exception('failed_to_create_token');
        }

        return $token;
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
