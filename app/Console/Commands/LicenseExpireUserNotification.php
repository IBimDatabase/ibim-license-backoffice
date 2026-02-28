<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Helpers\AppHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\LicenseExpireUserNotificationEmailJob;

class LicenseExpireUserNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license-expire-user-notification {period?}';

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
        $period = $this->argument('period');
        $periods = empty($period) ? [7, 1] : [(int) $period];

        foreach ($periods as $daysBeforeExpiry) {
            $fromDateExpiry = Carbon::now()->addDays($daysBeforeExpiry)->startOfDay();
            $toDateExpiry = Carbon::now()->addDays($daysBeforeExpiry)->endOfDay();

            $converted_to_from_date = null;
            $converted_to_to_date = null;

            if (!empty($fromDateExpiry)) {
                $convert_date = date("Y-m-d 00:00:00", strtotime($fromDateExpiry));
                $date = AppHelper::convertTimezone($convert_date, null, "UTC");
                $converted_to_from_date = $date;
            }
            if (!empty($toDateExpiry)) {
                $convert_date = date("Y-m-d 23:59:59", strtotime($toDateExpiry));
                $date = AppHelper::convertTimezone($convert_date, null, "UTC");
                $converted_to_to_date = $date;
            }

            $base_query = DB::table('product_license_keys as plk')
                ->leftJoin('orders as o', 'plk.order_id', 'o.id')
                ->whereNull('o.deleted_at');

            $expiryQuery = $base_query->where(function ($q) use ($converted_to_from_date, $converted_to_to_date) {
                $q->whereBetween('plk.expiry_date', [$converted_to_from_date, $converted_to_to_date])
                    ->where('plk.status', '!=', 'EXPIRED');
            });

            $expiredLicenses = $expiryQuery->whereNull('plk.deleted_at')->get()->toArray();

            if (!empty($expiredLicenses)) {
                $data = [];
                $data['expiry']['expiry_from_date'] = $fromDateExpiry->format('Y-m-d');
                $data['expiry']['expiry_to_date'] = $toDateExpiry->format('Y-m-d');
                $data['expiry']['days_before_expiry'] = $daysBeforeExpiry;
                $data['from_month'] = $fromDateExpiry->format('F');
                $data['from_year'] = $fromDateExpiry->format('Y');
                $data['to_month'] = $toDateExpiry->format('F');
                $data['to_year'] = $toDateExpiry->format('Y');

                LicenseExpireUserNotificationEmailJob::dispatch($data);
            }
        }
    
        return 0;
    }
    
}
