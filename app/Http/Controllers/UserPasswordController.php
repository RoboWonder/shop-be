<?php

namespace App\Http\Controllers;

use App\Jobs\SendPasswordEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPasswordController extends UserAuthController
{
    /***
     * make a temporary password string.
     *
     * @param int $length
     *
     * @return string
     * @throws \Exception
     * @since: 2022/07/26 23:32
     */
    private function _randomPassword(int $length = 8) {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

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
        if (! $user) return false;

        function strRandom($length) {
            $string = '';

            while (($len = strlen($string)) < $length) {
                $size = $length - $len;

                $bytes = random_bytes($size);

                $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
            }

            return $string;
        }

        $tmpPassword = $this->_randomPassword();

        DB::table('shopbe_password_reset')->whereEmail($credentials['email'])->delete();

        DB::table('shopbe_password_reset')->insert([
            'email' => $credentials['email'],
            'token' => $tmpPassword,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        dispatch(new SendPasswordEmail($user, $tmpPassword));

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'We\'ve sent you a temporary password to your email'
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

    }
}

?>
