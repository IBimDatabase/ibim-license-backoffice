<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;

class LicenseType extends Model
{
    use SoftDeletes;

    protected $table = 'license_types';
    protected $fillable = [
        'name', 'code', 'expiry_duration', 'status', 'wp_attribute_term_id', 'created_by', 'updated_by'
    ]; 

    public static function insertRecord($data)
    {
        $licenseType = new LicenseType();

        $licenseType->name = (key_exists('name', $data)) ? $data['name']: NULL;
        $licenseType->code = (key_exists('code', $data)) ? $data['code']: NULL;
        $licenseType->duration_type = (key_exists('duration_type', $data)) ? $data['duration_type']: NULL;
        $licenseType->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: NULL;
        $licenseType->description = (key_exists('description', $data)) ? $data['description']: NULL;
        $licenseType->status = (key_exists('status', $data)) ? $data['status']: NULL;
        $licenseType->wp_attribute_term_id = (key_exists('wp_attribute_term_id', $data)) ? $data['wp_attribute_term_id']: NULL;
        $licenseType->created_by = (key_exists('created_by', $data)) ? $data['created_by']: NULL;

        try {
            $licenseType->save();
            return $licenseType->where('id', $licenseType->id)->first();
        }
        catch (Exception $e) {
            return $e->getMessage(); 
        }  
    }

    public static function updateRecord($data, $id)
    {
        $licenseType = LicenseType::find($id);

        $licenseType->name = (key_exists('name', $data)) ? $data['name']: $licenseType->name;
        $licenseType->code = (key_exists('code', $data)) ? $data['code']: $licenseType->code;
        $licenseType->duration_type = (key_exists('duration_type', $data)) ? $data['duration_type']: $licenseType->duration_type;
        $licenseType->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: $licenseType->expiry_duration;
        $licenseType->description = (key_exists('description', $data)) ? $data['description']: $licenseType->description;
        $licenseType->status = (key_exists('status', $data)) ? $data['status']: $licenseType->status;
        $licenseType->wp_attribute_term_id = (key_exists('wp_attribute_term_id', $data)) ? $data['wp_attribute_term_id']: $licenseType->wp_attribute_term_id;
        $licenseType->updated_by = (key_exists('created_by', $data)) ? $data['created_by']: $licenseType->updated_by;

        try {
            $licenseType->save();
            return $licenseType->where('id', $licenseType->id)->first();
        }
        catch (Exception $e) {
            return $e->getMessage(); 
        }
    }

    public static function updateAndDeleteRecord($data, $id)
    {
        $licenseType = LicenseType::find($id);

        $licenseType->name = (key_exists('name', $data)) ? $data['name']: $licenseType->name;
        $licenseType->code = (key_exists('code', $data)) ? $data['code']: $licenseType->code;
        $licenseType->duration_type = (key_exists('duration_type', $data)) ? $data['duration_type']: $licenseType->duration_type;
        $licenseType->expiry_duration = (key_exists('expiry_duration', $data)) ? $data['expiry_duration']: $licenseType->expiry_duration;
        $licenseType->description = (key_exists('description', $data)) ? $data['description']: $licenseType->description;
        $licenseType->status = (key_exists('status', $data)) ? $data['status']: $licenseType->status;
        $licenseType->wp_attribute_term_id = (key_exists('wp_attribute_term_id', $data)) ? $data['wp_attribute_term_id']: $licenseType->wp_attribute_term_id;
        $licenseType->updated_by = (key_exists('created_by', $data)) ? $data['created_by']: $licenseType->updated_by;

        if ($licenseType->save()) 
        {
            $licenseType->delete();
            return $licenseType;
        }
        else
        {
            return false;
        }
    }
}
