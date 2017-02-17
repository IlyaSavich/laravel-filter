<?php

namespace Savich\Filter\Contracts;

abstract class Filter implements Filterable
{
    /**
     * Array of parameters.
     * Parameters will be setting in this array by the system
     * @var array
     */
    protected $parameters = [];

    /**
     * Adding parameters to filter.
     * You can pass parameters as array.
     * @param mixed $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }
}
