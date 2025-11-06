<?php

namespace App\Imports;

use App\Models\LicenseType;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class LicenseTypesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure, WithCalculatedFormulas
{
    use Importable, SkipsFailures;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function rules(): array
    {
        return [
            'name' => 'required|unique:App\Models\LicenseType,name|max:150|regex:/^[^\W]/',
            'code' => 'required|unique:App\Models\LicenseType,code|max:150|regex:/^[^\W]/',
            'expiry_period' => 'required|string|max:150',
            'expiry_duration' => 'nullable|required_if:expiry_period,!=,date|numeric|min:1',
            'expiry_duration_date' => 'nullable|required_if:expiry_period,=,date|date|after:today|date_format:d-m-Y',
            'status' => 'required|in:"ACTIVE", "INACTIVE"|max:50'
        ];
    }

    public function collection(Collection $rows)
    {
        foreach($rows as $row) {
            if($row->filter()->isNotEmpty()){                
                LicenseType::insertRecord([
                    'name' => @$row['name'],
                    'code' => @$row['code'],
                    'duration_type' => @$row['duration_type'],
                    'expiry_duration' => @$row['expiry_duration'],
                    'status' =>  @$row['status'],
                    'created_by' => auth()->user()->id,
                ]);
            }
   	    }	
    }
}
