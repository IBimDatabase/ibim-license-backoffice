<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LicenseExpireUserNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @param string|null $expiryFilePath
     * @param string|null $purchasedFilePath
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Reminder: Your Subscription Will Expire Soon!';
        
        $email = $this->view('mails.expire-user-notification', ['data' => $this->data])
            ->subject($subject);

        return $email;
    }
}
