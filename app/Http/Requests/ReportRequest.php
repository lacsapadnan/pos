<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'from_date' => 'nullable|date|before_or_equal:to_date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'warehouse' => 'nullable|exists:warehouses,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'from_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'from_date.before_or_equal' => 'Tanggal mulai harus sebelum atau sama dengan tanggal akhir.',
            'to_date.date' => 'Tanggal akhir harus berupa tanggal yang valid.',
            'to_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
            'warehouse.exists' => 'Gudang yang dipilih tidak valid.',
            'user_id.exists' => 'User yang dipilih tidak valid.',
        ];
    }

    /**
     * Get the cleaned and formatted filters from the request.
     */
    public function getFilters(): array
    {
        return [
            'from_date' => $this->input('from_date'),
            'to_date' => $this->input('to_date'),
            'warehouse_id' => $this->input('warehouse'), // Note: input is 'warehouse' but we map to 'warehouse_id'
            'user_id' => $this->input('user_id'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default dates if not provided
        if (!$this->filled('from_date')) {
            $this->merge(['from_date' => now()->subDay()->format('Y-m-d')]);
        }

        if (!$this->filled('to_date')) {
            $this->merge(['to_date' => now()->format('Y-m-d')]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic can be added here
            if ($this->filled('from_date') && $this->filled('to_date')) {
                $fromDate = \Carbon\Carbon::parse($this->from_date);
                $toDate = \Carbon\Carbon::parse($this->to_date);

                // Check if date range is not too large (e.g., more than 1 year)
                if ($fromDate->diffInDays($toDate) > 365) {
                    $validator->errors()->add('date_range', 'Rentang tanggal tidak boleh lebih dari 1 tahun.');
                }
            }
        });
    }
}
