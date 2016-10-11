<?php

namespace Savich\Filter\Contracts;

abstract class Filter implements Filterable
{
    /**
     * The name of the filter.
     * By this alias the system will finding
     * the current filter class for building filter query
     * @var string
     */
    protected $alias;

    /**
     * Array of parameters.
     * You can specify filter parameter with syntax. Example: fitler_1:param1,param2
     * Parameters will be setting in this array by the system
     * @var array
     */
    protected $parameters = [];

    /**
     * There your should specify the model which data will be filtering
     * @var string
     */
    protected $model;

    public function __construct()
    {
        $this->alias = $this->alias();
        $this->model = $this->modelNamespace();
    }

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
