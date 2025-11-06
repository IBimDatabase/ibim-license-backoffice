<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\LicenseKeyHelper;

class LicenseAudit extends Model
{
    protected $table = 'license_audit';
    protected $fillable = ['license_key', 'mac_address', 'user_id'];
    protected $appends = ['hashed_license_key'];

    public $timestamps = false;

    public static function insertRecord($data)
    {
        $licenseAudit = new LicenseAudit();

        $licenseAudit->license_id = (key_exists('license_id', $data)) ? $data['license_id']: NULL;
        $licenseAudit->entry_type = (key_exists('entry_type', $data)) ? $data['entry_type']: NULL;
        $licenseAudit->license_key = (key_exists('license_key', $data)) ? $data['license_key']: NULL;
        $licenseAudit->mac_address = (key_exists('mac_address', $data)) ? $data['mac_address']: NULL;
        $licenseAudit->license_audit_uuid = (key_exists('license_audit_uuid', $data)) ? $data['license_audit_uuid']: NULL;
        $licenseAudit->previous_license_code = (key_exists('previous_license_code', $data)) ? $data['previous_license_code']: NULL;
        $licenseAudit->current_license_code = (key_exists('current_license_code', $data)) ? $data['current_license_code']: NULL;
        $licenseAudit->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: NULL;
        $licenseAudit->previous_expiry_date = (key_exists('previous_expiry_date', $data)) ? $data['previous_expiry_date']: NULL;
        $licenseAudit->current_expiry_date = (key_exists('current_expiry_date', $data)) ? $data['current_expiry_date']: NULL;
        $licenseAudit->system_info = (key_exists('system_info', $data)) ? $data['system_info']: NULL;
        $licenseAudit->user_id = (key_exists('user_id', $data)) ? $data['user_id']: NULL;
        $licenseAudit->created_at = date('Y-m-d H:i:s');

        if ($licenseAudit->save())
            return $licenseAudit;
        else
            return false;
    }

    public static function updateRecord($data, $id)
    {
        $licenseAudit = LicenseAudit::where('license_uuid', $id)->first();

        $licenseAudit->license_id = (key_exists('license_id', $data)) ? $data['license_id']: $licenseAudit->license_id;
        $licenseAudit->entry_type = (key_exists('entry_type', $data)) ? $data['entry_type']: $licenseAudit->entry_type;
        $licenseAudit->license_key = (key_exists('license_key', $data)) ? $data['license_key']: $licenseAudit->license_key;
        $licenseAudit->mac_address = (key_exists('mac_address', $data)) ? $data['mac_address']: $licenseAudit->mac_address;
        $licenseAudit->license_audit_uuid = (key_exists('license_audit_uuid', $data)) ? $data['license_audit_uuid']: $licenseAudit->license_audit_uuid;
        $licenseAudit->previous_license_code = (key_exists('previous_license_code', $data)) ? $data['previous_license_code']: $licenseAudit->previous_license_code;
        $licenseAudit->current_license_code = (key_exists('current_license_code', $data)) ? $data['current_license_code']: $licenseAudit->current_license_code;
        $licenseAudit->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: $licenseAudit->expiry_duration;
        $licenseAudit->previous_expiry_date = (key_exists('previous_expiry_date', $data)) ? $data['previous_expiry_date']: $licenseAudit->previous_expiry_date;
        $licenseAudit->current_expiry_date = (key_exists('current_expiry_date', $data)) ? $data['current_expiry_date']: $licenseAudit->current_expiry_date;
        $licenseAudit->system_info = (key_exists('system_info', $data)) ? $data['system_info']: $licenseAudit->system_info;
        $licenseAudit->user_id = (key_exists('user_id', $data)) ? $data['user_id']: $licenseAudit->user_id;

        if ($licenseAudit->save())
            return $licenseAudit;
        else
            return false;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getHashedLicenseKeyAttribute() {
        return LicenseKeyHelper::licenseKeyHash($this->license_key);
    }
}
