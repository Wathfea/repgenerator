<?php

namespace Pentacom\Repgenerator;

use Illuminate\Foundation\Http\FormRequest;

class RepgeneratorGenerationRequest extends FormRequest
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
            'columns' => ['required', 'array'],
            'columns.*.name' => ['required', 'string'],
            'columns.*.type' => ['required', 'string', 'in:' . implode(',',MigrationGeneratorService::getColumnTypes())],
            'columns.*.length' => ['nullable', 'integer'],
            'columns.*.aic' => ['nullable', 'boolean'],
            'columns.*.nullable' => ['nullable', 'boolean'],
            'columns.*.cascades' => ['nullable', 'boolean'],
            'columns.*.comment' => ['nullable', 'string'],
            'columns.*.precision' => ['nullable', 'integer'],
            'columns.*.scale' => ['nullable', 'integer'],
            'columns.*.unsigned' => ['nullable', 'boolean'],
            'columns.*.values' => ['nullable', 'string'],
            'columns.*.default' => ['nullable', 'string'],
            'columns.*.index' => ['nullable', 'array'],
        ];
    }
}
