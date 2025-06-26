<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('simpan absensi');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'check_in_date' => 'required|date',
            'check_in_time' => 'required',
            'check_out_date' => 'nullable|date',
            'check_out_time' => 'nullable',
            'break_start_date' => 'nullable|date',
            'break_start_time' => 'nullable',
            'break_end_date' => 'nullable|date',
            'break_end_time' => 'nullable',
            'status' => 'required|in:checked_in,checked_out,on_break',
            'notes' => 'nullable|string'
        ];

        // If status is checked_out, make check_out fields required
        if ($this->input('status') === 'checked_out') {
            $rules['check_out_date'] = 'required|date';
            $rules['check_out_time'] = 'required';
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Karyawan harus diisi!',
            'user_id.exists' => 'Karyawan tidak ditemukan!',
            'check_in_date.required' => 'Tanggal masuk harus diisi!',
            'check_in_date.date' => 'Tanggal masuk harus berupa tanggal!',
            'check_in_time.required' => 'Jam masuk harus diisi!',
            'check_out_date.required' => 'Tanggal keluar harus diisi ketika status "Sudah Pulang"!',
            'check_out_date.date' => 'Tanggal keluar harus berupa tanggal!',
            'check_out_time.required' => 'Jam keluar harus diisi ketika status "Sudah Pulang"!',
            'break_start_date.date' => 'Tanggal mulai istirahat harus berupa tanggal!',
            'break_start_time.date_format' => 'Format jam mulai istirahat tidak valid!',
            'break_end_date.date' => 'Tanggal selesai istirahat harus berupa tanggal!',
            'break_end_time.date_format' => 'Format jam selesai istirahat tidak valid!',
            'status.required' => 'Status harus diisi!',
            'status.in' => 'Status tidak valid!',
            'notes.string' => 'Catatan harus berupa teks!'
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
            if ($this->user_id && $this->check_in_date) {
                $existingAttendance = \App\Models\Attendance::where('user_id', $this->user_id)
                    ->whereDate('check_in', $this->check_in_date)
                    ->first();

                if ($existingAttendance) {
                    $validator->errors()->add('user_id', 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada');
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

            // Validate break times are within work hours
            if ($this->break_start_date && $this->break_start_time && $this->check_in_date && $this->check_in_time) {
                $checkIn = \Carbon\Carbon::parse($this->check_in_date . ' ' . $this->check_in_time);
                $breakStart = \Carbon\Carbon::parse($this->break_start_date . ' ' . $this->break_start_time);

                if ($breakStart->lt($checkIn)) {
                    $validator->errors()->add('break_start_time', 'Waktu istirahat harus dalam jam kerja');
                }
            }

            if ($this->break_end_date && $this->break_end_time && $this->check_in_date && $this->check_in_time) {
                $checkIn = \Carbon\Carbon::parse($this->check_in_date . ' ' . $this->check_in_time);
                $breakEnd = \Carbon\Carbon::parse($this->break_end_date . ' ' . $this->break_end_time);

                if ($breakEnd->lt($checkIn)) {
                    $validator->errors()->add('break_end_time', 'Waktu selesai istirahat harus dalam jam kerja');
                }
            }

            // Validate break end is after break start
            if ($this->break_start_date && $this->break_start_time && $this->break_end_date && $this->break_end_time) {
                $breakStart = \Carbon\Carbon::parse($this->break_start_date . ' ' . $this->break_start_time);
                $breakEnd = \Carbon\Carbon::parse($this->break_end_date . ' ' . $this->break_end_time);

                if ($breakEnd->lt($breakStart)) {
                    $validator->errors()->add('break_end_time', 'Waktu selesai istirahat harus setelah waktu mulai istirahat');
                }
            }
        });
    }
}
