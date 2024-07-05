<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes;

use Coroowicaksono\ChartJsIntegration\Api\Dtos\TotalRecordsDto;
use Illuminate\Database\Eloquent\Builder;

class HourQueryType extends QueryType
{
    protected function generateQuery(string $modelClass, TotalRecordsDto $totalRecordsDto, string $seriesSql): Builder
    {
        $query = $modelClass::selectRaw(
            'HOUR(' . $totalRecordsDto->colXaxis . ') AS cat
            HOUR(' . $totalRecordsDto->colXaxis . ') AS catorder,
            sum(' . $totalRecordsDto->sum . ') counted' . $seriesSql
        );

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
            $query->where($totalRecordsDto->colXaxis, '>=', now()->startOfDay());
        }

        return $query->groupBy('catorder', 'cat')->orderBy('catorder', 'asc');
    }
}
