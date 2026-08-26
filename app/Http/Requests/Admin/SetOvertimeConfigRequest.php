<?php

namespace App\Http\Requests\Admin;

use App\Models\EmployeeOvertimeConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SetOvertimeConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $options = implode(',', EmployeeOvertimeConfig::MULTIPLIER_OPTIONS);

        return [
            'allowed_multipliers'   => ['required', 'array', 'min:1'],
            'allowed_multipliers.*' => ['numeric', "in:{$options}"],
            'default_multiplier'    => ['required', 'numeric', "in:{$options}"],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $allowed = array_map('floatval', (array) $this->input('allowed_multipliers', []));
            $default = $this->input('default_multiplier') !== null ? (float) $this->input('default_multiplier') : null;

            if ($default !== null && ! empty($allowed) && ! in_array($default, $allowed, false)) {
                $validator->errors()->add(
                    'default_multiplier',
                    'The default multiplier must be one of the checked allowed multipliers.',
                );
            }
        });
    }
}
