<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\QueryTypes;

use Coroowicaksono\ChartJsIntegration\Api\Dtos\TotalRecordsDto;
use Illuminate\Database\Eloquent\Builder;

abstract class QueryType
{
    abstract protected function generateQuery(
        string $modelClass,
        TotalRecordsDto $totalRecordsDto,
        string $seriesSql
    ): Builder;

    final public function generate(string $modelClass, TotalRecordsDto $totalRecordsDto, string $seriesSql): Builder
    {
        $query = $this->generateQuery($modelClass, $totalRecordsDto, $seriesSql);

        if ($totalRecordsDto->queryFilter) {
            foreach ($$totalRecordsDto->queryFilter as $queryFilter) {
                if (isset($queryFilter['value']) && !is_array($queryFilter['value'])) {
                    if (isset($queryFilter['operator'])) {
                        $query->where($queryFilter['key'], $queryFilter['operator'], $queryFilter['value']);
                    } else {
                        $query->where($queryFilter['key'], $queryFilter['value']);
                    }
                } else {
                    if ($queryFilter['operator'] == 'IS NULL') {
                        $query->whereNull($queryFilter['key']);
                    } elseif ($queryFilter['operator'] == 'IS NOT NULL') {
                        $query->whereNotNull($queryFilter['key']);
                    } elseif ($queryFilter['operator'] == 'IN') {
                        $query->whereIn($queryFilter['key'], $queryFilter['value']);
                    } elseif ($queryFilter['operator'] == 'NOT IN') {
                        $query->whereIn($queryFilter['key'], $queryFilter['value']);
                    } elseif ($queryFilter['operator'] == 'BETWEEN') {
                        $query->whereBetween($queryFilter['key'], $queryFilter['value']);
                    } elseif ($queryFilter['operator'] == 'NOT BETWEEN') {
                        $query->whereNotBetween($queryFilter['key'], $queryFilter['value']);
                    }
                }
            }
        }

        return $query;
    }
}
