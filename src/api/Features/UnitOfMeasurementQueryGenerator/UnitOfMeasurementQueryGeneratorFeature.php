<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator;

use Coroowicaksono\ChartJsIntegration\Api\Dtos\TotalRecordsDto;
use Coroowicaksono\ChartJsIntegration\Api\Enums\UnitOfMeasurementEnum;
use Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes\DayQueryType;
use Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes\HourQueryType;
use Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes\MonthQueryType;
use Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes\QueryType;
use Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes\WeekQueryType;
use Illuminate\Database\Eloquent\Builder;

class UnitOfMeasurementQueryGeneratorFeature
{
    protected array $queryTypes = [
        UnitOfMeasurementEnum::HOUR->value => HourQueryType::class,
        UnitOfMeasurementEnum::DAY->value => DayQueryType::class,
        UnitOfMeasurementEnum::WEEK->value => WeekQueryType::class,
        UnitOfMeasurementEnum::MONTH->value => MonthQueryType::class,
    ];

    /**
     * Generates a query
     *
     * @param string $modelClass
     * @param TotalRecordsDto $totalRecordsDto
     *
     * @return Builder
     */
    public function generate(string $modelClass, TotalRecordsDto $totalRecordsDto): Builder
    {
        /** @var QueryType $queryType */
        $queryType = new $this->queryTypes[$totalRecordsDto->uom->value]();
        return $queryType->generate($modelClass, $totalRecordsDto, $this->generateSeriesSql($totalRecordsDto));
    }

    /**
     * Generates series sql from a total records dto
     *
     * @param TotalRecordsDto $totalRecordsDto
     *
     * @return string
     */
    protected function generateSeriesSql(TotalRecordsDto $totalRecordsDto): string
    {
        $seriesSql = '';

        if ($totalRecordsDto->series) {
            foreach ($totalRecordsDto->series as $seriesKey => $seriesData) {
                $filter = $seriesData->filter;
                $labelList[$seriesKey] = $seriesData->label;

                if (
                    empty($filter->value) &&
                    isset($filter->operator) &&
                    ($filter->operator == 'IS NULL' || $filter->operator == 'IS NOT NULL')
                ) {
                    $seriesSql .= ", SUM(" .
                        "CASE WHEN " . $filter->key . " " . $filter->operator .
                            "THEN " . $totalRecordsDto->sum . " ELSE 0 END" .
                    ") as '" . $labelList[$seriesKey] . "'";
                } elseif (empty($filter->value)) {
                    $seriesSql .= ", SUM(CASE WHEN ";
                    $countFilter = count($filter);

                    foreach ($filter as $keyFilter => $listFilter) {
                        $value = is_array($listFilter->value)
                            ? '(' . implode(',', $listFilter->value) . ')'
                            : "'$listFilter->value'";

                        $seriesSql .= " " . $listFilter->key . " " . ($listFilter->operator ?? "=") . " $value ";
                        $seriesSql .= $countFilter - 1 != $keyFilter ? " AND " : "";
                    }

                    $seriesSql .= "THEN " . $totalRecordsDto->sum . " else 0 end) as '" . $labelList[$seriesKey] . "'";
                } else {
                    $value = is_array($filter->value)
                            ? '(' . implode(',', $filter->value) . ')'
                            : "'$filter->value'";

                    $seriesSql .= ", SUM(CASE WHEN " . $filter->key . " " . ($filter->operator ?? "=") . " $value " .
                        "THEN " . $totalRecordsDto->sum . " ELSE 0 END) as '" . $labelList[$seriesKey] . "'";
                }
            }
        }

        return $seriesSql;
    }
}
