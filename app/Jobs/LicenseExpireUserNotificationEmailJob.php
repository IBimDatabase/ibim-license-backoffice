<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Services\LicenseKeyService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\LicenseExpireUserNotificationEmail;

class LicenseExpireUserNotificationEmailJob implements ShouldQueue
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
        $email_send_array = [];
        $this->data['expiry']['type'] = 'PACKAGE';
        $package_list = LicenseKeyService::get_license_list($this->data['expiry'], null, true);
        $package_result = $package_list['licenses'];

        $this->data['expiry']['type'] = 'PRODUCT';
        $product_list = LicenseKeyService::get_license_list($this->data['expiry'], null, true);
        $product_result = $product_list['licenses'];

        if (!empty($package_result)) {

            foreach ($package_result as $list) {
                $temp = [];
                if (!empty(@$list['customer']['email'])) {
                    $temp['license_key'] = @$list['license_key'];
                    $temp['days_before_expiry'] = (int) (@$this->data['expiry']['days_before_expiry'] ?? 0);
                    $temp['user_name'] = @$list['customer']['first_name'] ? @$list['customer']['first_name'] : ((!empty(@$list['customer']['email']) && !empty(explode('@', @$list['customer']['email'])[0])) ? explode('@', @$list['customer']['email'])[0] : 'User');
                    $temp['email'] = @$list['customer']['email'];
                    $temp['phone'] = @$list['customer']['phone'];
                    $temp['license_type'] = @$list['license_type'] ? $this->textCapitilize(@$list['license_type']) : "-";
                    $temp['purchased_package'] =  @$list['package']['package_name'] ?? '-';
                    $temp['activated_on'] = @$list['purchased_date'] ? date('d-m-Y', strtotime(@$list['purchased_date'])) : '';
                    $temp['order_on'] = @$list['order']['order_placed_at'] ? date('d-m-Y', strtotime(@$list['order']['order_placed_at'])) : '';
                    $temp['expiry_date'] = @$list['expiry_date'] ? date('d-m-Y', strtotime(@$list['expiry_date'])) : '';
                    $temp['status'] = @$list['status'] ? $this->textCapitilize(@$list['status']) : '';
                    $email_send_array[] = $temp;
                }
            }
        }
        if (!empty($product_result)) {
            foreach ($product_result as  $list) {
                $temp = [];
                if (!empty(@$list['customer']['email'])) {
                    $temp['license_key'] = @$list['license_key']; 
                    $temp['days_before_expiry'] = (int) (@$this->data['expiry']['days_before_expiry'] ?? 0);
                    $temp['user_name'] = @$list['customer']['first_name'] ? @$list['customer']['first_name'] : ((!empty(@$list['customer']['email']) && !empty(explode('@', @$list['customer']['email'])[0])) ? explode('@', @$list['customer']['email'])[0] : 'User');
                    $temp['email'] = @$list['customer']['email'];
                    $temp['phone'] = @$list['customer']['phone'];
                    $temp['license_type'] = @$list['license_type'] ? $this->textCapitilize(@$list['license_type']) : "-";
                    $temp['purchased_product'] =  @$list['product']['product_name'] ?? '-';
                    $temp['activated_on'] = @$list['purchased_date'] ? date('d-m-Y', strtotime(@$list['purchased_date'])) : '';
                    $temp['order_on'] = @$list['order']['order_placed_at'] ? date('d-m-Y', strtotime(@$list['order']['order_placed_at'])) : '';
                    $temp['expiry_date'] = @$list['expiry_date'] ? date('d-m-Y', strtotime(@$list['expiry_date'])) : '';
                    $temp['status'] = @$list['status'] ? $this->textCapitilize(@$list['status']) : '';
                    $email_send_array[] = $temp;
                }
            }
        }

        if (!empty($email_send_array)) {
            $daysBeforeExpiry = (int) (@$this->data['expiry']['days_before_expiry'] ?? 0);
            foreach ($email_send_array as $value) {
                $redisKey = 'notified_license_' . @$value['license_key'] . '_' . $daysBeforeExpiry . '_days';
                if (!Redis::exists($redisKey)) {
                    Mail::to($value['email'])
                        // Mail::to('marimuthu@appyhub.com')
                        ->send(new LicenseExpireUserNotificationEmail($value));
                    Redis::set($redisKey, true);
                    Redis::expire($redisKey, 45 * 24 * 60 * 60); // Keep reminder-specific key long enough to avoid duplicate sends
                }
            }
        }
    }

    private function textCapitilize($str)
    {
        $array = explode('_', $str);

        $array = array_map(function ($value) {
            return ucfirst(strtolower($value));
        }, $array);

        return implode(' ', $array);
    }
}
