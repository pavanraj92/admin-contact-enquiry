<?php

namespace admin\enquiries\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_reply' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'admin_reply.required'    => 'Reply message is required.',
            'admin_reply.min'         => 'Reply message must be at least 5 characters.',
        ];
    }
}