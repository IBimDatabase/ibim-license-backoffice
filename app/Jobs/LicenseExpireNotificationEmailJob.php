<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LicenseUserWiseExport;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\LicenseExpireNotificationEmail;
use App\Exports\LicenseUserWisePurchaseExport;

class LicenseExpireNotificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     *
     * @param $data
     * @param $filePath
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Define the file paths
        $expiryFilePath = 'exports/generated_license_expiry.xlsx';
        $purchasedFilePath = 'exports/generated_license_purchased.xlsx';

        if(!empty($this->data['expiry'])){
            $expiryData = new LicenseUserWiseExport($this->data['expiry']);
        }
        if(!empty($this->data['purchased'])){
            $purchasedData = new LicenseUserWisePurchaseExport($this->data['purchased']);
        }

        if (!empty(@$expiryData)) {
            Excel::store($expiryData, $expiryFilePath, 'local');
        } else {
            $expiryFilePath = null;
        }

        if (!empty(@$purchasedData)) {
            Excel::store($purchasedData, $purchasedFilePath, 'local');
        } else {
            $purchasedFilePath = null;
        }

        if (is_null($expiryFilePath) && is_null($purchasedFilePath)) {
            Log::info('No data available for export. Email not sent.');
            return;
        }

        $expiryFullPath = $expiryFilePath ? storage_path('app/' . $expiryFilePath) : null;
        $purchasedFullPath = $purchasedFilePath ? storage_path('app/' . $purchasedFilePath) : null;

        Mail::to(env('NOTIFICATION_EMAIL'))
            ->bcc(['mani@appyhub.com'])
            ->send(new LicenseExpireNotificationEmail($this->data, $expiryFullPath, $purchasedFullPath));

        if (!empty($expiryFullPath)) {
            Storage::disk('local')->delete($expiryFilePath);
        }
        if (!empty($purchasedFullPath)) {
            Storage::disk('local')->delete($purchasedFilePath);
        }
    }
}
