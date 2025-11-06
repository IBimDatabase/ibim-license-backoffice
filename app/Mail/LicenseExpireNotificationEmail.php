<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LicenseExpireNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    protected $expiryFilePath;
    protected $purchasedFilePath;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @param string|null $expiryFilePath
     * @param string|null $purchasedFilePath
     * @return void
     */
    public function __construct($data, $expiryFilePath = null, $purchasedFilePath = null)
    {
        $this->data = $data;
        $this->expiryFilePath = $expiryFilePath;
        $this->purchasedFilePath = $purchasedFilePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = (isset($this->data['from_month']) ? $this->data['from_month'] : '') . ' ' .
            (isset($this->data['from_year']) ? $this->data['from_year'] : '') .
            ' Report and License Expiry Notification';
        // . '-' . @$this->data['to_month'] . ' ' . @$this->data['to_year'];

        $email = $this->view('mails.expire-notification', ['data' => $this->data])
            ->subject($subject);

        // Conditionally attach the files if they exist
        if ($this->expiryFilePath) {
            $email->attach($this->expiryFilePath, [
                'as' => 'ExpiryLicense.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        if ($this->purchasedFilePath) {
            $email->attach($this->purchasedFilePath, [
                'as' => 'PurchasedLicense.xlsx',
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return $email;
    }
}
