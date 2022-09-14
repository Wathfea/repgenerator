<?php

namespace Pentacom\Repgenerator\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradientRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'hexFrom' => ['required', 'string'],
            'hexTo' => ['required', 'string'],
        ];
    }
}
