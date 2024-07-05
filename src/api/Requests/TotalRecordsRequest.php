<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Requests;

use Coroowicaksono\ChartJsIntegration\Api\Dtos\TotalRecordsDto;
use Coroowicaksono\ChartJsIntegration\Api\Enums\UnitOfMeasurementEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TotalRecordsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'model' => ['bail', 'required', 'min:1', 'string'],
            'expires' => [],
            'col_xaxis' => ['alpha_num'],
            'join' => ['json'],
            'series' => ['array'],
            'series.*' => ['json'],
            'queryFilter' => ['array'],
            'options' => ['json'],
        ];
    }

    public function getDto(): TotalRecordsDto
    {
        $options = [];

        if ($this->options) {
            $options = json_decode($this->options, true);
            $validator = Validator::make($options, [
                'showTotal' => ['boolean'],
                'totalLabel' => ['string'],
                'type' => ['string'],
                'advanceFilterSelected' => [],
                'latestData' => [],
                'uom' => [Rule::enum(UnitOfMeasurementEnum::class)],
                'startWeek' => ['string'],
                'sum' => ['integer'],
            ]);

            if (!$validator->passes()) {
                $this->failedValidation($validator);
            }
        }

        return TotalRecordsDto::fromArray([
            'model' => urldecode($this->model),
            'expires' => (int) (bool) $this->expires,
            'colXaxis' => $this->col_xaxis,
            'join' => json_decode($this->join, true),
            'series' => collect($this->series)->map(fn ($d) => json_decode($d, false))->toArray(),
            'queryFilter' => $this->queryFilter,
            'showTotal' => $options['showTotal'] ?? true,
            'totalLabel' => $options['totalLabel'] ?? 'Total',
            'type' => $options['type'] ?? 'bar',
            'advanceFilterSelected' => $options['advanceFilterSelected'] ?? false,
            'latestData' => $options['latestData'] ?? 3,
            'uom' => $options['uom'] ? UnitOfMeasurementEnum::from($options['uom']) : UnitOfMeasurementEnum::MONTH,
            'startWeek' => $options['startWeek'] ?? '1',
            'sum' => $options['sum'] ?? 1
        ]);
    }
}
