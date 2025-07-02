<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('kelola absensi');
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
            'check_in_date' => 'required|date',
            'check_in_time' => 'required|date_format:H:i:s',
            'check_out_date' => 'nullable|date',
            'check_out_time' => 'nullable|date_format:H:i:s',
            'status' => 'required|in:checked_in,checked_out',
            'notes' => 'nullable|string|max:255',
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
            'employee_id.required' => 'Karyawan harus diisi!',
            'employee_id.exists' => 'Karyawan tidak ditemukan!',
            'check_in_date.required' => 'Tanggal masuk harus diisi!',
            'check_in_date.date' => 'Tanggal masuk harus berupa tanggal!',
            'check_in_time.required' => 'Jam masuk harus diisi!',
            'check_out_date.required' => 'Tanggal keluar harus diisi ketika status "Sudah Pulang"!',
            'check_out_date.date' => 'Tanggal keluar harus berupa tanggal!',
            'check_out_time.required' => 'Jam keluar harus diisi ketika status "Sudah Pulang"!',
            'status.required' => 'Status harus diisi!',
            'status.in' => 'Status tidak valid!',
            'notes.string' => 'Catatan harus berupa teks!',
            'notes.max' => 'Catatan tidak boleh lebih dari 255 karakter!'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if attendance already exists for this user on this date
            if ($this->employee_id && $this->check_in_date) {
                $existingAttendance = Attendance::where('employee_id', $this->employee_id)
                    ->whereDate('check_in', $this->check_in_date)
                    ->first();

                if ($existingAttendance) {
                    $validator->errors()->add('employee_id', 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada');
                }
            }

            // Validate check_out is after check_in
            if ($this->check_in_date && $this->check_in_time && $this->check_out_date && $this->check_out_time) {
                $checkIn = \Carbon\Carbon::parse($this->check_in_date . ' ' . $this->check_in_time);
                $checkOut = \Carbon\Carbon::parse($this->check_out_date . ' ' . $this->check_out_time);

                if ($checkOut->lt($checkIn)) {
                    $validator->errors()->add('check_out_time', 'Jam keluar harus setelah jam masuk');
                }
            }
        });
    }
}
