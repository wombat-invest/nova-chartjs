<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes;

use Coroowicaksono\ChartJsIntegration\Api\Dtos\TotalRecordsDto;
use Illuminate\Database\Eloquent\Builder;

class WeekQueryType extends QueryType
{
    protected function generateQuery(string $modelClass, TotalRecordsDto $totalRecordsDto, string $seriesSql): Builder
    {
        $modelInstance = new $modelClass();
        $connectionName = $modelInstance->getConnection()->getDriverName();

        if ($connectionName == 'pgsql') {
            $query = $modelClass::selectRaw(
                "to_char(DATE_TRUNC('week', " . $totalRecordsDto->colXaxis . "), 'YYYYWW') AS cat,
                to_char(DATE_TRUNC('week', " . $totalRecordsDto->colXaxis . "), 'YYYYWW') AS catorder,
                sum(" . $totalRecordsDto->sum . ") counted" . $seriesSql
            );
        } else {
            $query = $modelClass::selectRaw(
                'YEARWEEK(' . $totalRecordsDto->colXaxis . ', ' . $totalRecordsDto->startWeek . ') AS cat,
                YEARWEEK(' . $totalRecordsDto->colXaxis . ', ' . $totalRecordsDto->startWeek . ') AS catorder,
                sum(' . $totalRecordsDto->sum . ') counted' . $seriesSql
            );
        }

        if ($totalRecordsDto->join) {
            $query->join(
                $totalRecordsDto->join['joinTable'],
                $totalRecordsDto->join['joinColumnFirst'],
                $totalRecordsDto->join['joinEqual'],
                $totalRecordsDto->join['joinColumnSecond']
            );
        }

        if (is_numeric($totalRecordsDto->advanceFilterSelected)) {
            $query->where($totalRecordsDto->colXaxis, '>=', now()->subDays($totalRecordsDto->advanceFilterSelected));
        } elseif ($totalRecordsDto->advanceFilterSelected == 'YTD') {
            $query->whereBetween($totalRecordsDto->colXaxis, [now()->firstOfYear()->startOfDay(), now()]);
        } elseif ($totalRecordsDto->advanceFilterSelected == 'QTD') {
            $query->whereBetween($totalRecordsDto->colXaxis, [now()->firstOfQuarter()->startOfDay(), now()]);
        } elseif ($totalRecordsDto->advanceFilterSelected == 'MTD') {
            $query->whereBetween($totalRecordsDto->colXaxis, [now()->firstOfMonth()->startOfDay(), now()]);
        } elseif ($totalRecordsDto->latestData != '*') {
            $query->where(
                $totalRecordsDto->colXaxis,
                '>=',
                now()->startOfWeek()->subWeek($totalRecordsDto->latestData)
            );
        }

        return $query->groupBy('catorder', 'cat')->orderBy('catorder', 'asc');
    }
}
