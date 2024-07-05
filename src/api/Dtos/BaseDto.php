<?php

namespace Coroowicaksono\ChartJsIntegration\Api\Dtos;

abstract class BaseDto
{
    protected function __construct(array $data = [])
    {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }

    /**
     * Converts an array to a DTO
     *
     * @param array $data
     *
     * @return static
     */
    public static function fromArray(array $data = [])
    {
        $validatedData = [];

        foreach ($data as $property => $value) {
            if (property_exists(static::class, $property)) {
                $validatedData[$property] = $value;
            }
        }

        return new static($validatedData);
    }
}
