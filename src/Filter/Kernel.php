<?php

namespace Savich\Filter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Input;
use Savich\Filter\Contracts\Filter;

/**
 * Class Kernel
 * Class used for registering filters and for finding the filter by it alias
 * @package App\Services\Filter
 */
abstract class Kernel
{
    /**
     * There you can register your filter classes
     * @var array
     */
    protected $filters = [];

    /**
     * The array of registered filters after register method
     * @var array
     */
    protected $registeredFilters = [];

    /**
     * Array of filters from url
     * @var array
     */
    protected $usingFilters = [];

    public function __construct()
    {
        $this->register();
        $this->groupUsingFilters();
    }

    /**
     * Init registered filters
     * Create array that will used for finding filters by there aliases
     *
     * @throws \Exception
     */
    protected function register()
    {
        foreach ($this->filters as $filter) {
            /* @var $filterClass Filter */
            $filterClass = new $filter;

            $this->checkFilterClass($filterClass, $filter);
            $this->addRegistered($filterClass);
        }
    }

    /**
     * Grouping all filters from url by models.
     * Group name is the model class name in camel case
     * @param array $filters
     */
    public function groupUsingFilters(array $filters = [])
    {
        $this->cleanUsingFilters();

        $usingFilters = $this->getFilters($filters);

        foreach ($usingFilters as $usingFilter) {
            list($filterAlias, $parameters) = $this->parseUsingFilter($usingFilter);

            $filterClass = $this->find($filterAlias);

            if (!$filterClass) {
                continue;
            }

            $filterClass->addParameters($parameters);
            $groupName = $this->getGroupName($filterClass->modelNamespace());

            if (!$this->hasUsed($groupName, $filterAlias)) {
                $this->usingFilters[$groupName][$filterAlias] = $filterClass;
            }
        }
    }

    /**
     * Getting filters from url
     * @param array $filters
     * @return array
     */
    public function getFilters(array $filters)
    {
        return empty($filters) ? Input::all() : $filters;
    }

    /**
     * Parse filter from url
     * Get alias and parameters
     * @param string $urlFilter
     * @return array
     */
    protected function parseUsingFilter($urlFilter)
    {
        preg_match('/([^:]*)(:(.*))?/', $urlFilter, $matches);

        $parameters = isset($matches[3]) ? explode(',', $matches[3]) : [];
        $parameters = $this->filteringParameters($parameters);

        return [$matches[1], $parameters,];
    }

    /**
     * Getting filters group name by models classes names
     * Group name is the model class name in camel case
     * @param string $modelNamespace
     * @return string
     */
    protected function getGroupName($modelNamespace)
    {
        return $modelNamespace;
    }

    /**
     * Finding filter by it alias
     * @param string $alias
     * @return Filter|bool
     */
    protected function find($alias)
    {
        return array_key_exists($alias, $this->registeredFilters) ? $this->registeredFilters[$alias] : false;
    }

    /**
     * Add registered filter
     * @param Filter $filter
     */
    protected function addRegistered($filter)
    {
        $this->registeredFilters[$filter->alias()] = $filter;
    }

    /**
     * Check if selected filter is already using in current filtering process
     * @param string $filterGroup
     * @param string $filterAlias
     * @return bool
     */
    public function hasUsed($filterGroup, $filterAlias)
    {
        if (!isset($this->usingFilters[$filterGroup])) {
            return false;
        }

        return array_key_exists($filterAlias, $this->usingFilters[$filterGroup]);
    }

    /**
     * Building queries for each filter groups
     */
    public function make()
    {
        $resultFilter = [];

        foreach ($this->usingFilters as $groupName => $groupFilters) {
            $resultFilter[$groupName] = $this->makeGroup($groupFilters);
        }

        return $resultFilter;
    }

    /**
     * Build filter query for group
     * @param array $groupFilters
     * @param Builder $query
     * @return Builder
     */
    protected function makeGroup($groupFilters, Builder $query = null)
    {
        if (is_null($query)) {
            $query = call_user_func($this->getModelQueryFunction($groupFilters));
        }

        foreach ($groupFilters as $groupFilter) {
            /* @var Filter $groupFilter */
            $groupFilter->build($query);
        }

        return $query;
    }

    /**
     * Getting method to get model query
     * @param array $groupFilters
     * @return string
     */
    protected function getModelQueryFunction($groupFilters)
    {
        /* @var Filter $filterClass */
        $filterClass = array_first($groupFilters);

        return $filterClass->modelNamespace() . '::query';
    }

    /**
     * Filtering parameters
     * Remove empty
     * @param array $parameters
     * @return array
     */
    protected function filteringParameters($parameters)
    {
        return array_filter($parameters, function ($parameter) {
            $parameter = trim($parameter);

            return !empty($parameter);
        });
    }

    /**
     * Uses for example if the you passing filters not through the url parameters
     * or want to change it format in url
     * For it you need first of all clean using filters
     * and then repeat grouping filters by calling groupUsingFilters() method
     */
    public function cleanUsingFilters()
    {
        $this->usingFilters = [];
    }

    /**
     * Checking is the custom filter class is inheritor of the Filter
     * @param Filter $filterClass
     * @param string $filter Registered user filter class
     * @throws \Exception
     */
    protected function checkFilterClass($filterClass, $filter)
    {
        if (!$filterClass instanceof Filter) {
            throw new \Exception("The class $filter must be instance of " . Filter::class);
        }
    }
}
