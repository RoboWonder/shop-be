<?php

namespace App\Jobs;

use App\Mail\EmailPassword;

use Illuminate\Support\Facades\Mail;

class SendPasswordEmail extends Job
{
    public $tries = 5;

    protected $user;
    protected $pwd;

    public function __construct($user, $pwd)
    {
        $this->user = $user;
        $this->pwd = $pwd;
    }

    public function handle()
    {
        Mail::to($this->user->email)->send(new EmailPassword($this->user, $this->pwd));
    }
}
