<?php

namespace App\Jobs;

use App\Models\UserModel;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;

class SendVerificationEmail extends Job
{
    protected $user;
    public $tries = 5;

    public function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $email = new EmailVerification($this->user);
        Mail::to($this->user->email)->send($email);
    }
}
