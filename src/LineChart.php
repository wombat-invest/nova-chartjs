<?php

namespace Coroowicaksono\ChartJsIntegration;

use Coroowicaksono\ChartJsIntegration\Traits\HasOptions;
use Laravel\Nova\Card;

class LineChart extends Card
{
    use HasOptions;

    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    public function __construct($component = null)
    {

        parent::__construct($component);
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'stripe-chart';
    }

    public function series(array $series): self
    {
        foreach ($series as $key => $data) {
            $series[$key]['fill'] = false;
        }
        return $this->withMeta(['series' => $series]);
    }

    public function type(string $type): self
    {
        return $this->withMeta(['type' => $type]);
    }

    public function animations(array $animations): self
    {
        return $this->withMeta(['animations' => $animations]);
    }

    public function title(string $title): self
    {
        return $this->withMeta(['title' => $title]);
    }

    public function model(string $model): self
    {
        return $this->withMeta(['model' => $model]);
    }

    public function col_xaxis(string $col_xaxis): self
    {
        return $this->withMeta(['col_xaxis' => $col_xaxis]);
    }

    public function uriKey(string $uriKey)
    {
        return $this->withMeta(['uriKey' => $uriKey]);
    }

    public function join(string $joinTable, string $joinColumnFirst, string $joinEqual, string $joinColumnSecond): self
    {
        return $this->withMeta(['join' => ['joinTable' => $joinTable, 'joinColumnFirst' => $joinColumnFirst, 'joinEqual' => $joinEqual, 'joinColumnSecond' => $joinColumnSecond]]);
    }
}
