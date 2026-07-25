<?php

namespace App\Http\Requests\Expert;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarRequest extends FormRequest
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
            'schedule'                          => ['required', 'array', 'min:1'],
            'schedule.*.day_of_week'            => ['required', 'integer', 'between:0,6'],
            'schedule.*.start_time'             => ['required', 'date_format:H:i'],
            'schedule.*.end_time'               => ['required', 'date_format:H:i', 'after:schedule.*.start_time'],
            'schedule.*.slot_duration_minutes'  => ['required', 'integer', 'min:5'],
            'schedule.*.is_active'              => ['sometimes', 'boolean'],
        ];
    }
    public function messages(): array
    {
        return [
            'schedule.*.end_time.after' => 'وقت النهاية يجب أن يكون بعد وقت البداية.',
        ];
    }
}
