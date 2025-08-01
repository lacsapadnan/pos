<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalarySettingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $salarySettingId = $this->route('salary_setting')?->id;

        return [
            'employee_id' => 'required|exists:employees,id|unique:salary_settings,employee_id,' . $salarySettingId,
            'warehouse_id' => 'required|exists:warehouses,id',
            'daily_salary' => 'required|numeric|min:0',
            'monthly_salary' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan harus dipilih',
            'employee_id.exists' => 'Karyawan tidak valid',
            'employee_id.unique' => 'Karyawan ini sudah memiliki pengaturan gaji',
            'warehouse_id.required' => 'Gudang harus dipilih',
            'warehouse_id.exists' => 'Gudang tidak valid',
            'daily_salary.required' => 'Gaji harian harus diisi',
            'daily_salary.numeric' => 'Gaji harian harus berupa angka',
            'daily_salary.min' => 'Gaji harian tidak boleh negatif',
            'monthly_salary.required' => 'Gaji bulanan harus diisi',
            'monthly_salary.numeric' => 'Gaji bulanan harus berupa angka',
            'monthly_salary.min' => 'Gaji bulanan tidak boleh negatif',
            'notes.string' => 'Catatan harus berupa teks',
        ];
    }
}
