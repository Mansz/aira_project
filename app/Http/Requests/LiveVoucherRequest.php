<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LiveVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming we have auth middleware in place
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('live_vouchers')->ignore($this->voucher),
            ],
            'discount_type' => ['required', 'in:percentage,amount'],
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->input('discount_type') === 'percentage' && $value > 100) {
                        $fail('Persentase diskon tidak boleh lebih dari 100%.');
                    }
                },
            ],
            'live_stream_id' => [
                'required',
                'exists:live_streams,id',
                function ($attribute, $value, $fail) {
                    $liveStream = \App\Models\LiveStream::find($value);
                    if ($liveStream && !in_array($liveStream->status, ['scheduled', 'active'])) {
                        $fail('Live stream yang dipilih harus berstatus scheduled atau active.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'start_time' => ['required', 'date', 'after_or_equal:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode voucher harus diisi.',
            'code.unique' => 'Kode voucher sudah digunakan.',
            'discount_type.required' => 'Tipe diskon harus dipilih.',
            'discount_type.in' => 'Tipe diskon tidak valid.',
            'discount_value.required' => 'Nilai diskon harus diisi.',
            'discount_value.numeric' => 'Nilai diskon harus berupa angka.',
            'discount_value.min' => 'Nilai diskon tidak boleh negatif.',
            'live_stream_id.required' => 'Live stream harus dipilih.',
            'live_stream_id.exists' => 'Live stream yang dipilih tidak valid.',
            'start_time.required' => 'Waktu mulai harus diisi.',
            'start_time.date' => 'Format waktu mulai tidak valid.',
            'start_time.after_or_equal' => 'Waktu mulai harus sama dengan atau setelah waktu sekarang.',
            'end_time.required' => 'Waktu berakhir harus diisi.',
            'end_time.date' => 'Format waktu berakhir tidak valid.',
            'end_time.after' => 'Waktu berakhir harus setelah waktu mulai.',
            'description.max' => 'Deskripsi tidak boleh lebih dari 255 karakter.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }
    }
}
