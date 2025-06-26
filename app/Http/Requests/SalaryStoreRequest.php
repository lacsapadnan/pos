<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Salary;

class SalaryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('simpan gaji');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'daily_salary' => 'nullable|numeric|min:0|max:999999999.99',
            'monthly_salary' => 'nullable|numeric|min:0|max:999999999.99',
            'other_deductions' => 'nullable|numeric|min:0|max:999999999.99',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan harus dipilih',
            'employee_id.exists' => 'Karyawan tidak valid',
            'warehouse_id.required' => 'Cabang harus dipilih',
            'warehouse_id.exists' => 'Cabang tidak valid',
            'period_start.required' => 'Tanggal mulai periode harus diisi',
            'period_start.date' => 'Format tanggal mulai tidak valid',
            'period_end.required' => 'Tanggal akhir periode harus diisi',
            'period_end.date' => 'Format tanggal akhir tidak valid',
            'period_end.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai',
            'daily_salary.numeric' => 'Gaji harian harus berupa angka',
            'daily_salary.min' => 'Gaji harian tidak boleh kurang dari 0',
            'monthly_salary.numeric' => 'Gaji bulanan harus berupa angka',
            'monthly_salary.min' => 'Gaji bulanan tidak boleh kurang dari 0',
            'other_deductions.numeric' => 'Potongan lain harus berupa angka',
            'other_deductions.min' => 'Potongan lain tidak boleh kurang dari 0',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation: at least one salary type must be provided
            if (empty($this->daily_salary) && empty($this->monthly_salary)) {
                $validator->errors()->add('salary', 'Minimal satu jenis gaji (harian atau bulanan) harus diisi');
            }

            // Check for duplicate salary period
            $existingSalary = Salary::where('employee_id', $this->employee_id)
                ->where(function ($query) {
                    $query->whereBetween('period_start', [$this->period_start, $this->period_end])
                        ->orWhereBetween('period_end', [$this->period_start, $this->period_end])
                        ->orWhere(function ($q) {
                            $q->where('period_start', '<=', $this->period_start)
                                ->where('period_end', '>=', $this->period_end);
                        });
                })
                ->exists();

            if ($existingSalary) {
                $validator->errors()->add('period', 'Sudah ada data gaji untuk karyawan ini dalam periode yang sama atau tumpang tindih');
            }
        });
    }
}
