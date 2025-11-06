<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseRenewalLog extends Model
{
    use SoftDeletes;

    protected $table = 'license_renewal_log';
    protected $fillable = ['license_id', 'license_renewal_uuid', 'license_key', 'previous_license_code', 'current_license_code', 'expiry_duration', 'previous_expiry_date', 'current_expiry_date', 'renewed_by', 'created_at', 'updated_at'];

    public static function insertRecord($data) 
    {
        $licenseRenewalLog = new LicenseRenewalLog();

        $licenseRenewalLog->license_id = (key_exists('license_id', $data)) ? $data['license_id']: NULL;
        $licenseRenewalLog->license_renewal_uuid = (key_exists('license_renewal_uuid', $data)) ? $data['license_renewal_uuid']: NULL;
        $licenseRenewalLog->license_key = (key_exists('license_key', $data)) ? $data['license_key']: NULL;
        $licenseRenewalLog->previous_license_code = (key_exists('previous_license_code', $data)) ? $data['previous_license_code']: NULL;
        $licenseRenewalLog->current_license_code = (key_exists('current_license_code', $data)) ? $data['current_license_code']: NULL;
        $licenseRenewalLog->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: NULL;
        $licenseRenewalLog->previous_expiry_date = (key_exists('previous_expiry_date', $data)) ? $data['previous_expiry_date']: NULL;
        $licenseRenewalLog->current_expiry_date = (key_exists('current_expiry_date', $data)) ? $data['current_expiry_date']: NULL;
        $licenseRenewalLog->renewed_by = (key_exists('renewed_by', $data)) ? $data['renewed_by']: NULL;

        if ($licenseRenewalLog->save())
            return $licenseRenewalLog;
        else   
            return false;
    }

    public static function updateRecord($data, $id) 
    {
        $licenseRenewalLog = LicenseProduct::where('license_uuid', $id)->first();

        $licenseRenewalLog->license_id = (key_exists('license_id', $data)) ? $data['license_id']: $licenseRenewalLog->license_id;
        $licenseRenewalLog->license_renewal_uuid = (key_exists('license_renewal_uuid', $data)) ? $data['license_renewal_uuid']: $licenseRenewalLog->license_renewal_uuid;
        $licenseRenewalLog->license_key = (key_exists('license_key', $data)) ? $data['license_key']: $licenseRenewalLog->license_key;
        $licenseRenewalLog->previous_license_code = (key_exists('previous_license_code', $data)) ? $data['previous_license_code']: $licenseRenewalLog->previous_license_code;
        $licenseRenewalLog->current_license_code = (key_exists('current_license_code', $data)) ? $data['current_license_code']: $licenseRenewalLog->current_license_code;
        $licenseRenewalLog->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: $licenseRenewalLog->expiry_duration;
        $licenseRenewalLog->previous_expiry_date = (key_exists('previous_expiry_date', $data)) ? $data['previous_expiry_date']: $licenseRenewalLog->previous_expiry_date;
        $licenseRenewalLog->current_expiry_date = (key_exists('current_expiry_date', $data)) ? $data['current_expiry_date']: $licenseRenewalLog->current_expiry_date;
        $licenseRenewalLog->renewed_by = (key_exists('renewed_by', $data)) ? $data['renewed_by']: $licenseRenewalLog->renewed_by;

        if ($licenseRenewalLog->save())
            return $licenseRenewalLog;
        else   
            return false;
    }
}
