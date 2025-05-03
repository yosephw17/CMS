<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // app/Http/Requests/UpdateEvaluatorRequest.php
public function rules()
{
    return [
        'email' => 'required|email|unique:evaluators,email,'.$this->evaluator->id,
        'name' => 'required|string|max:255',
        'role' => 'required|in:student,instructor,dean'
    ];
}
}
