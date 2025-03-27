<?php

namespace App\Core\Auth\Contracts;

interface CanResetPasswordContract
{
    /**
     * Get the email address that should be used for password reset links.
     *
     * @return string
     */
    public function getEmailForPasswordReset();

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token);
}
