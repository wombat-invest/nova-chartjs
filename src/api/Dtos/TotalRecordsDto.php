<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Dtos;

use Coroowicaksono\ChartJsIntegration\Api\Enums\UnitOfMeasurementEnum;

class TotalRecordsDto extends BaseDto
{
    public string $model;
    public int $expires;
    public string|null $colXaxis;
    public array|null $join;
    public array|null $series;
    public array|null $queryFilter;
    public bool $showTotal;
    public string $totalLabel;
    public string $type;
    public bool|int|string $advanceFilterSelected;
    public int|string $latestData;
    public UnitOfMeasurementEnum $uom;
    public string $startWeek;
    public int $sum;
}
