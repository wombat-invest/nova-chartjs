<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Features;

use Coroowicaksono\ChartJsIntegration\Api\Dtos\TotalRecordsDto;
use Illuminate\Support\Collection;

class DataSetGeneratorFeature
{
    public function generate(Collection $data, TotalRecordsDto $totalRecordsDto): array
    {
        $xAxis = [];
        $yAxis = [];
        $brandColor = config('nova.brand.colors.500', '14,165,233');
        $defaultColor = ["rgba($brandColor, 1)", config('main.colours')];

        $xAxis = collect($data)->map(function ($item, $key) use ($totalRecordsDto) {
            if ($totalRecordsDto->uom == 'week') {
                $splitCat = str_split($item->only(['cat'])['cat'], 4);
                $cat = "W" . $splitCat[1] . " " . $splitCat[0];
            } else {
                $cat = $item->only(['cat'])['cat'];
            }
            return $cat;
        });

        if ($totalRecordsDto->series) {
            $countKey = 0;

            foreach ($totalRecordsDto->series as $sKey => $dataSeries) {
                $yAxis[$sKey]['label'] = $dataSeries->label;

                if (isset($dataSeries->fill)) {
                    if ($dataSeries->fill == false) {
                        $yAxis[$sKey]['borderColor'] = $dataSeries->backgroundColor ?? $defaultColor[$sKey];
                        $yAxis[$sKey]['fill'] = false;
                    } else {
                        $yAxis[$sKey]['backgroundColor'] = $dataSeries->backgroundColor ?? $defaultColor[$sKey];
                    }
                } else {
                    $yAxis[$sKey]['backgroundColor'] = $dataSeries->backgroundColor ?? $defaultColor[$sKey];
                }
                $yAxis[$sKey]['data'] = collect($data)->map(function ($item, $key) use ($dataSeries) {
                    return $item->only([$dataSeries->label])[$dataSeries->label];
                });
                $countKey++;
            }

            if ($totalRecordsDto->showTotal == true) {
                $yAxis[$countKey] = $this->counted(
                    $data,
                    $defaultColor[$countKey],
                    'line',
                    $totalRecordsDto->totalLabel
                );
            }
        } else {
            $yAxis[0] = $this->counted(
                $data,
                $defaultColor[0],
                $totalRecordsDto->type,
                $totalRecordsDto->totalLabel
            );
        }

        return [$xAxis, $yAxis];
    }

    protected function counted($dataSet, $bgColor = "#111", $type = "bar", $label = "Total")
    {
        $yAxis = [
            'type'  => $type,
            'label' => $label,
            'data' => collect($dataSet)->map(function ($item, $key) {
                return $item->only(['counted'])['counted'];
            })
        ];

        if ($type == "line") {
            $yAxis['fill'] = false;
            $yAxis['borderColor'] = $bgColor;
        } else {
            $yAxis['backgroundColor'] = $bgColor;
        }

        return $yAxis;
    }
}
