<?php

namespace Pentacom\Repgenerator\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Pentacom\Repgenerator\Domain\Pattern\Enums\ColumnType;

class GenerationRequest extends FormRequest
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
            'columns.*.type' => ['required', 'string', Rule::in(array_column(ColumnType::cases(), 'value'))],
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
            'columns.*.menu_group_id' => ['nullable', 'integer', 'exists:crud_menu_groups,id'],
            'columns.*.new_menu_group_name' => ['nullable', 'string'],
            'columns.*.new_menu_group_icon' => ['nullable', 'string'],
        ];
    }
}
