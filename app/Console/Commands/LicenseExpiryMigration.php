<?php

namespace App\Console\Commands;

use App\Models\ProductLicenseKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LicenseExpiryMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:expiry-migration';

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
        $licenses = DB::table('product_license_keys as plk')
            ->join('products as p', 'plk.product_id', 'p.id')
            ->leftJoin('packages as pa', 'plk.package_id', 'pa.id')
            ->leftJoin('license_types as lt', 'lt.code', 'plk.license_type')
            ->select('plk.*', 'p.product_code', 'pa.package_code', 'lt.duration_type', 'lt.expiry_duration')
            ->whereNull('plk.deleted_at')
            ->whereIn('plk.status', ['PURCHASED', 'AVAILABLE'])
            ->where(function ($query) {
                $query->whereNull('plk.expiry_date')
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereRaw("
                        CASE
                            WHEN lt.expiry_duration LIKE '%Year(s)%' THEN 
                                TIMESTAMPDIFF(YEAR, plk.created_at, plk.expiry_date) > CAST(REGEXP_SUBSTR(lt.expiry_duration, '[0-9]+') AS SIGNED)
                            WHEN lt.expiry_duration LIKE '%Month(s)%' THEN 
                                TIMESTAMPDIFF(MONTH, plk.created_at, plk.expiry_date) > CAST(REGEXP_SUBSTR(lt.expiry_duration, '[0-9]+') AS SIGNED)
                            WHEN lt.expiry_duration LIKE '%Day(s)%' THEN 
                                DATEDIFF(plk.expiry_date, plk.created_at) > CAST(REGEXP_SUBSTR(lt.expiry_duration, '[0-9]+') AS SIGNED)
                            ELSE 1
                        END
                    ");
                    });
            })
            // ;dd($licenses->toRawSql());
            ->get();

        $totalRecords = $licenses->count();
        $this->output->progressStart($totalRecords);

        $batchUpdates = [];

        foreach ($licenses as $license) {
            $temp = [];
            $created_at = $license->created_at;
            $duration = $license->expiry_duration;
            $calculated_expiry_date = null;

            $expiry_duration = str_replace(['(', ')'], ['', ''], $duration);
            $calculated_expiry_date = date('Y-m-d H:i:s', strtotime($expiry_duration, strtotime($created_at)));

            if (!empty($license->package_id)) {
                $licenseKeyActivated = ProductLicenseKeys::where('package_id', $license->package_id)
                    ->where('order_id', $license->order_id)
                    ->where('license_key', $license->license_key)
                    ->whereNotNull('expiry_date')
                    ->orderBy('expiry_date', 'asc')
                    ->first();

                if (!empty($licenseKeyActivated)) {
                    if (strtotime($licenseKeyActivated->expiry_date) <= strtotime($calculated_expiry_date)) {
                        $temp['already_activated'] = $licenseKeyActivated->expiry_date;
                        $calculated_expiry_date = $licenseKeyActivated->expiry_date;
                    }
                }
            }

            $temp['id'] = $license->id;
            $temp['created_at'] = $license->created_at;
            $temp['duration'] = $duration;
            $temp['expiry_date_new'] = $calculated_expiry_date;
            $temp['expiry_date_old'] = @$license->expiry_date;

            $batchUpdates[] = [
                'id' => $license->id,
                'expiry_date' => $calculated_expiry_date,
                'updated_at' => date('Y-m-d H:i:s'),
                'expiry_date_new' => $calculated_expiry_date,
                'expiry_date_old' => @$license->expiry_date,
                'temp' => @$temp,
            ];

            $this->output->progressAdvance();
        }

        // Batch update all records at once
        if (!empty($batchUpdates)) {
            foreach ($batchUpdates as $update) {
                DB::table('product_license_keys')
                    ->where('id', $update['id'])
                    ->update([
                        'expiry_date' => $update['expiry_date'],
                        'status' => $update['expiry_date'] < date('Y-m-d H:i:s') ? 'EXPIRED' : DB::raw('status'),
                        'updated_at' => $update['updated_at'],
                    ]);
            }
        }
        $this->output->progressFinish();

        $package_min_max_expiry_date = DB::table('product_license_keys as plk')
            ->join('products as p', 'plk.product_id', '=', 'p.id')
            ->leftJoin('packages as pa', 'plk.package_id', '=', 'pa.id')
            ->leftJoin('license_types as lt', 'lt.code', '=', 'plk.license_type')
            ->select(
                // 'plk.package_id',
                // 'plk.order_id',
                'plk.license_key',
                DB::raw('MIN(CASE WHEN plk.expiry_date IS NOT NULL THEN plk.expiry_date ELSE plk.created_at END) as min_expiry_date'),
                DB::raw('MAX(plk.expiry_date) as max_expiry_date')
            )
            ->whereNull('plk.deleted_at')
            ->whereIn('plk.status', ['PURCHASED', 'AVAILABLE'])
            // ->where('plk.package_id', "82")
            ->whereNotNull('plk.package_id')
            // ->groupBy('plk.package_id', 'plk.order_id')
            // ->groupBy('plk.order_id')
            ->groupBy('plk.license_key')
            ->get();
        // ->toRawSql();
        // dd($package_min_max_expiry_date);
        // Log::info(json_encode($package_min_max_expiry_date));
        // die;

        $total_records = $package_min_max_expiry_date->count();
        $this->output->progressStart($total_records);
        $record = [];

        if (!empty($package_min_max_expiry_date)) {
            foreach ($package_min_max_expiry_date as $value) {
                $temp = [];
                if (!empty($value->license_key)) {
                    $temp['id'] =  $value->license_key;
                    $temp['min_expiry_date'] =  $value->min_expiry_date;
                    $temp['max_expiry_date'] =  $value->max_expiry_date;
                    $record[] = $temp;
                    DB::table('product_license_keys')
                        // ->where('package_id', $value->package_id)
                        // ->where('order_id', $value->order_id)
                        ->where('license_key', $value->license_key)
                        ->where('expiry_date', '>', $value->min_expiry_date)
                        ->update([
                            'expiry_date' => $value->min_expiry_date,
                            'status' => $value->min_expiry_date < date('Y-m-d H:i:s') ? 'EXPIRED' : DB::raw('status'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
                $this->output->progressAdvance();
            }
        }

        // Log::info(json_encode($record));
        // Log::info(json_encode($batchUpdates));


        return 0;
    }
}
