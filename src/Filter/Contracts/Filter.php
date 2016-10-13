<?php

namespace Savich\Filter\Contracts;

abstract class Filter implements Filterable
{
    /**
     * Array of parameters.
     * You can specify filter parameter with syntax. Example: fitler_1:param1,param2
     * Parameters will be setting in this array by the system
     * @var array
     */
    protected $parameters = [];

    /**
     * Adding parameters to filter.
     * You can pass parameters as array.
     * Also you can pass parameters as list separated by comma
     * @param mixed $parameters
     */
    public function addParameters($parameters)
    {
        if (is_array($parameters)) {
            $this->parameters = array_merge($this->parameters, $parameters);
        } else {
            $this->parameters[] = array_merge($this->parameters, func_get_args());
        }
    }
}
