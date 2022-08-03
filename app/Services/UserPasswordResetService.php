<?php
/**
 * Created by PhpStorm.
 * User: Hung <hunglt@hanbiro.vn>
 * Date: 2022-08-01
 * Time: 22:17
 */

namespace App\Services;

use App\Jobs\SendPasswordEmail;
use App\Models\UserPasswordResetModel;
use App\Repositories\UserPasswordResetRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\JWTAuth;

class UserPasswordResetService
{
    private $jwt;
    private $userRepository;
    private $userPasswordResetRepository;

    public function __construct(UserRepository $userRepository, UserPasswordResetRepository $userPasswordResetRepository, JWTAuth $jwt)
    {
        $this->userRepository = $userRepository;
        $this->userPasswordResetRepository = $userPasswordResetRepository;
        $this->jwt = $jwt;
    }

    /***
     * do forgot password.
     *
     * @param string $email
     *
     * @return bool
     * @since: 2022/08/02 22:52
     */
    public function doForgot(string $email): bool
    {
        $user = $this->userRepository->findWhere(['email' => $email])->first();

        if (!$user || !$user->exists) {
            return FALSE;
        }

        $tmpPassword = Str::random(8);
        $hash = Hash::make($tmpPassword);

        $tmpResults = $this->userPasswordResetRepository->findWhere(['email' => $email]);
        if ($tmpResults->isNotEmpty()) {
            $tmpResults->each->delete();
        }

        $newReset = new UserPasswordResetModel();
        $newReset->fill(['email' => $email, 'token' => $hash]);

        if (!$newReset->save()) {
            return FALSE;
        }

        $this->jwt->setToken($user->auth_token)->invalidate();

        dispatch(new SendPasswordEmail($user, $tmpPassword));

        return TRUE;
    }

    /***
     * create a new password.
     *
     * @param string $token
     * @param string $email
     * @param string $password
     *
     * @return bool
     * @since: 2022/08/02 23:12
     * @author: Hung <hung@hanbiro.com>
     */
    public function doReset(string $token, string $email, string $password)
    {
        $user = $this->userRepository->findWhere(['email' => $email, 'login_token' => $token])->first();
        if (!!$user && $user->exists) {
            $user->forceFill(['password' => Hash::make($password), 'login_token' => NULL]);
            if ($user->save()) {
                $pwdReset = $this->userPasswordResetRepository->findWhere(['email' => $email])->first();
                if (!!$pwdReset && $pwdReset->exists) {
                    $pwdReset->delete();
                }

                return TRUE;
            }
        }

        return FALSE;
    }
}
