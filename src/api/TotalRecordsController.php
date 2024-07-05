<?php

namespace Coroowicaksono\ChartJsIntegration\Api;

use Coroowicaksono\ChartJsIntegration\Api\Features\DataSetGeneratorFeature;
use Coroowicaksono\ChartJsIntegration\Api\Features\UnitOfMeasurementQueryGenerator\UnitOfMeasurementQueryGeneratorFeature;
use Coroowicaksono\ChartJsIntegration\Api\Requests\TotalRecordsRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TotalRecordsController extends Controller
{
    use ValidatesRequests;

    /**
     * Handles a request
     *
     * @param TotalRecordsRequest $request
     *
     * @return JsonResponse
     */
    public function handle(TotalRecordsRequest $request)
    {
        $totalRecordsDto = $request->getDto();

        $modelClass = $totalRecordsDto->model;

        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getConnection()->getTablePrefix() . $modelInstance->getTable();
        $totalRecordsDto->colXaxis = $request->input('col_xaxis') ?? DB::raw($tableName . '.created_at');

        $cacheKey = hash('md4', $modelClass . $totalRecordsDto->expires);
        $data = Cache::get($cacheKey);

        if (!$data) {
            /** @var UnitOfMeasurementQueryGeneratorFeature $queryGenerator */
            $queryGenerator = app(UnitOfMeasurementQueryGeneratorFeature::class);
            $query = $queryGenerator->generate($modelClass, $totalRecordsDto);

            $data = $query->get();

            if ($totalRecordsDto->expires) {
                Cache::put($cacheKey, $data, Carbon::parse($totalRecordsDto->expires));
            }
        }

        /** @var DataSetGeneratorFeature $dataSetGeneratorFeature */
        $dataSetGeneratorFeature = app(DataSetGeneratorFeature::class);
        [$xAxis, $yAxis] = $dataSetGeneratorFeature->generate($data, $totalRecordsDto);

        return response()->json([
            'dataset' => [
                'xAxis'  => $xAxis,
                'yAxis'  => $yAxis
            ]
        ]);
    }
}
