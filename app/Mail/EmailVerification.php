<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\UserModel;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    public function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->view('auth/verification')->with(['email_token' => $this->user->email_token, 'email' => $this->user->email]);
    }
}
