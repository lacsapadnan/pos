<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('update absensi');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
     */
    public function messages(): array
    {
        return [
            'check_in_date.required' => 'Tanggal masuk harus diisi!',
            'check_in_date.date' => 'Tanggal masuk harus berupa tanggal!',
            'check_in_time.required' => 'Jam masuk harus diisi!',
            'check_in_time.date_format' => 'Format jam masuk tidak valid!',
            'check_out_date.date' => 'Tanggal keluar harus berupa tanggal!',
            'check_out_time.date_format' => 'Format jam keluar tidak valid!',
            'status.required' => 'Status harus diisi!',
            'status.in' => 'Status tidak valid!',
            'notes.string' => 'Catatan harus berupa teks!',
            'notes.max' => 'Catatan tidak boleh lebih dari 255 karakter!'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate check out is after check in
            if ($this->check_in_date && $this->check_in_time && $this->check_out_date && $this->check_out_time) {
                $checkIn = \Carbon\Carbon::parse($this->check_in_date . ' ' . $this->check_in_time);
                $checkOut = \Carbon\Carbon::parse($this->check_out_date . ' ' . $this->check_out_time);

                if ($checkOut->lt($checkIn)) {
                    $validator->errors()->add('check_out_time', 'Waktu keluar harus setelah waktu masuk');
                }
            }
        });
    }
}
