<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class SetQuestionsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
        'questions'                => ['required', 'array', 'min:1'],
        'questions.*.question_text'=> ['required', 'string'],
        'questions.*.answer_type'  => ['required', 'in:yes_no,multiple_choice,free_text'],
        'questions.*.options_json' => ['sometimes', 'nullable', 'array'],
        'questions.*.is_required'  => ['sometimes', 'boolean'],
    ];
    }
}
