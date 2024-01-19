<?php

namespace Coroowicaksono\ChartJsIntegration\Api;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller as BaseController;
use stdClass;

class Controller extends BaseController
{
    protected function filterByDate(
        Builder $query,
        string $filterSelected,
        string $xAxisColumn,
        string|int $latestData,
        Carbon $default
    ): void {
        if (is_numeric($filterSelected)) {
            $query->where($xAxisColumn, '>=', Carbon::now()->subDays($filterSelected));
        } elseif ($filterSelected == 'YTD') {
            $query->whereBetween($xAxisColumn, [Carbon::now()->firstOfYear()->startOfDay(), Carbon::now()]);
        } elseif ($filterSelected == 'QTD') {
            $query->whereBetween($xAxisColumn, [Carbon::now()->firstOfQuarter()->startOfDay(), Carbon::now()]);
        } elseif ($filterSelected == 'MTD') {
            $query->whereBetween($xAxisColumn, [Carbon::now()->firstOfMonth()->startOfDay(), Carbon::now()]);
        } elseif ($latestData != '*') {
            $query->where($xAxisColumn, '>=', $default);
        }
    }

    protected function filterByQuery(Builder $query, iterable $queryFilters): void
    {
        foreach ($queryFilters as $queryFilter) {
            if (isset($queryFilter['value']) && !is_array($queryFilter['value'])) {
                if (isset($queryFilter['operator'])) {
                    $query->where($queryFilter['key'], $queryFilter['operator'], $queryFilter['value']);
                } else {
                    $query->where($queryFilter['key'], $queryFilter['value']);
                }
            } else {
                match ($queryFilter) {
                    'IS NULL' => $query->whereNull($queryFilter['key']),
                    'IS NOT NULL' => $query->whereNotNull($queryFilter['key']),
                    'IN' => $query->whereIn($queryFilter['key'], $queryFilter['value']),
                    'NOT IN' => $query->whereIn($queryFilter['key'], $queryFilter['value']),
                    'BETWEEN' => $query->whereBetween($queryFilter['key'], $queryFilter['value']),
                    'NOT BETWEEN' => $query->whereNotBetween($queryFilter['key'], $queryFilter['value']),
                    default => null
                };
            }
        }
    }

    protected function getSeriesSql(iterable $series, string|int $sumValue): string
    {
        $sql = '';

        foreach ($series as $encodedData) {
            $data = json_decode($encodedData);
            $sql .= ", " . $this->getSumAndCaseStatement($data->filter, $sumValue, $data->label);
        }

        return $sql;
    }

    protected function getSumAndCaseStatement(stdClass|iterable $filter, string|int $sumValue, string $label): string
    {
        if ($filter instanceof stdClass) {
            if (in_array($filter->operator, ['IS NULL', 'IS NOT NULL'])) {
                return "SUM(CASE WHEN $filter->key $filter->operator THEN $sumValue ELSE 0 END) as '$label'";
            }

            $filterValue = $this->getFilterValue($filter);
            return "SUM(CASE WHEN $filter->key $filter->operator $filterValue THEN $sumValue ELSE 0 END) as '$label'";
        }

        $sql = "SUM(CASE WHEN ";
        $filterCount = count($filter);

        foreach ($filter as $k => $f) {
            $operator = $f->operator ?? "=";
            $value = $this->getFilterValue($f);

            $suffix = $filterCount - 1 != $k ? " AND " : "";

            $sql .= " $f->key $operator $value $suffix";
        }

        $sql .= "THEN $sumValue ELSE 0 END) as '$label'";

        return $sql;
    }

    protected function getFilterValue(stdClass $filter): string
    {
        return is_array($filter->value) ? '(' . implode(',', $filter->value) . ')' : "'$filter->value'";
    }
}
