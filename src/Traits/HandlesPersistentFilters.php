<?php

namespace Coroowicaksono\ChartJsIntegration\Traits;

trait HandlesPersistentFilters
{
    public function getPersistentFilter(string $id): mixed
    {
        return request()->session()->get($this->getPersistentFilterKey($id), false);
    }

    public function setPersistentFilter(string $id, mixed $value): void
    {
        request()->session()->put($this->getPersistentFilterKey($id), $value);
    }

    public function getPersistentFilterKey(string $id): string
    {
        return "persistent-filter.$id";
    }
}
