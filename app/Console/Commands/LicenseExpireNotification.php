<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Helpers\AppHelper;
use Illuminate\Console\Command;
use App\Models\ProductLicenseKeys;
use Illuminate\Support\Facades\DB;
use App\Jobs\LicenseExpireNotificationEmailJob;

class LicenseExpireNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license-expire-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Calculate the date ranges for expiry and purchase
        $fromDateExpiry = Carbon::now()->startOfDay(); // Now
        $toDateExpiry = Carbon::now()->addDays(30)->endOfDay(); // +30 days from now
    
        $fromDatePurchase = Carbon::now()->subDays(30)->startOfDay(); // -30 days from now
        $toDatePurchase = Carbon::now()->endOfDay(); // Now

        $converted_to_from_date = null;
        $converted_to_to_date = null;
        $converted_to_purchase_from_date = null;
        $converted_to_purchase_to_date = null;

        if (!empty($fromDateExpiry)) {
            $convert_date = date("Y-m-d 00:00:00", strtotime($fromDateExpiry));
            $converted_to_from_date = AppHelper::convertTimezone($convert_date, null, "UTC");
        }
        if (!empty($toDateExpiry)) {
            $convert_date = date("Y-m-d 23:59:59", strtotime($toDateExpiry));
            $converted_to_to_date = AppHelper::convertTimezone($convert_date, null, "UTC");
        }

        if (!empty($fromDatePurchase)) {
            $convert_date = date("Y-m-d 00:00:00", strtotime($fromDatePurchase));
            $converted_to_purchase_from_date = AppHelper::convertTimezone($convert_date, null, "UTC");
        }
        if (!empty($toDatePurchase)) {
            $convert_date = date("Y-m-d 23:59:59", strtotime($toDatePurchase));
            $converted_to_purchase_to_date = AppHelper::convertTimezone($convert_date, null, "UTC");
        }
    
        // Base query for product license keys joined with orders
        $base_query = DB::table('product_license_keys as plk')
            ->leftJoin('orders as o', 'plk.order_id', 'o.id')
            ->whereNull('o.deleted_at');
    
        // Query to find licenses expiring within the next 30 days
        $expiryQuery = $base_query->where(function ($q) use ($converted_to_from_date, $converted_to_to_date) {
            $q->whereBetween('plk.expiry_date', [$converted_to_from_date, $converted_to_to_date])
                ->where('plk.status', '!=', 'EXPIRED');
        });
    
        // Query to find licenses purchased in the last 30 days
        $purchasedQuery = $base_query->where(function ($q) use ($converted_to_purchase_from_date, $converted_to_purchase_to_date) {
            $q->whereBetween('o.order_placed_at', [$converted_to_purchase_from_date, $converted_to_purchase_to_date]);
        });
    
        // Fetch expired and purchased licenses
        $expiredLicenses = $expiryQuery->whereNull('plk.deleted_at')->get()->toArray();
        $purchasedLicenses = $purchasedQuery->whereNull('plk.deleted_at')->get()->toArray();
    
        if (!empty($expiredLicenses) || !empty($purchasedLicenses)) {
            $data = [];
    
            if (!empty($expiredLicenses)) {
                $data['expiry']['expiry_from_date'] = $fromDateExpiry->format('Y-m-d');
                $data['expiry']['expiry_to_date'] = $toDateExpiry->format('Y-m-d');
            }
    
            if (!empty($purchasedLicenses)) {
                $data['purchased']['purchased_from_date'] = $fromDatePurchase->format('Y-m-d');
                $data['purchased']['purchased_to_date'] = $toDatePurchase->format('Y-m-d');
            }
    
            $data['from_month'] = $fromDateExpiry->format('F');
            $data['from_year'] = $fromDateExpiry->format('Y');
            $data['to_month'] = $toDateExpiry->format('F');
            $data['to_year'] = $toDateExpiry->format('Y');
            $data['past_from_month'] = $fromDatePurchase->format('F');
            $data['past_from_year'] = $fromDatePurchase->format('Y');
            $data['user_name'] = env('NOTIFICATION_NAME');

            LicenseExpireNotificationEmailJob::dispatch($data);
        }
    
        return 0;
    }
    
}
