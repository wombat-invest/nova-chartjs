<?php

namespace Coroowicaksono\ChartJsIntegration\Traits;

trait HasOptions
{
    use HandlesPersistentFilters;

    public function options(array $options): self
    {
        if (isset($options['persistentFilterId'])) {
            $persistentFilterValue = $this->getPersistentFilter($options['persistentFilterId']);

            if ($persistentFilterValue) {
                $options['btnFilterDefault'] = $persistentFilterValue;
            }
        }

        return $this->withMeta(['options' => (object) $options]);
    }
}
