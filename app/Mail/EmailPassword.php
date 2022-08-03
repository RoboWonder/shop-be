<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $pwd;

    public function __construct($user, $pwd)
    {
        $this->user = $user;
        $this->pwd = $pwd;
    }

    public function build()
    {
        return $this->view('auth/password')->with(['pwd' => $this->pwd, 'email' => $this->user->email]);
    }
}
