<?php

namespace Savich\Filter;

use Illuminate\Database\Eloquent\Builder;
use Savich\Filter\Contracts\Filter;

/**
 * Class Kernel
 * Class used for registering filters and for finding the filter by it alias
 * @package App\Services\Filter
 */
class Kernel
{
    /**
     * Indexes after parsing filters
     */
    const ALIAS_INDEX = 0;
    const PARAMETERS_INDEX = 1;

    /**
     * @var Kernel|static
     */
    protected static $instance;

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

    protected function __construct()
    {
        $this->register();
    }

    /**
     * Create singleton instance
     * @return Kernel|static
     */
    public static function instance()
    {
        if (is_null(static::$instance)) {
            return new static;
        }

        return static::$instance;
    }

    /**
     * Making Kernel full singleton
     */
    public function __clone()
    {
    }

    /**
     * Init registered filters
     * Create array that will used for finding filters by there aliases
     * @throws \Exception
     */
    protected function register()
    {
        foreach ($this->filters as $filter) {
            $this->checkFilterClass($filter);
            $this->addRegistered($filter);
        }
    }

    /**
     * Checking is the custom filter class is inheritor of the Filter
     * @param string $filter Registered user filter class namespace
     * @throws \Exception
     */
    protected function checkFilterClass($filter)
    {
        if (!is_subclass_of($filter, Filter::class)) {
            throw new \Exception("The class $filter must be instance of " . Filter::class);
        }
    }

    /**
     * Add registered filter
     * @param string|Filter $filter
     */
    protected function addRegistered($filter)
    {
        $this->registeredFilters[$filter::alias()] = $filter;
    }

    /**
     * Applying filters and query result
     * @param array $filters
     * @return array
     */
    public function get(array $filters = [])
    {
        $filtered = $this->make($filters);

        $result = [];
        foreach ($filtered as $key => $query) {
            /* @var Builder $query */
            $result[$key] = $query->get();
        }

        return $result;
    }

    /**
     * Building queries for each filter groups
     * @param array $filters
     * @return array
     */
    public function make(array $filters = [])
    {
        $this->groupUsingFilters($filters);

        $resultFilter = [];

        foreach ($this->usingFilters as $groupName => $groupFilters) {
            $resultFilter[$groupName] = $this->makeGroup($groupFilters);
        }

        return $resultFilter;
    }

    /**
     * Grouping all filters from url by models.
     * Group name is the model class name in camel case
     * @param array $filters
     */
    public function groupUsingFilters(array $filters = [])
    {
        $this->cleanUsingFilters();

        $this->makeFilterGroups($filters);
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
            $filter = array_first($groupFilters);

            $query = $this->getModelQuery($filter);
        }

        foreach ($groupFilters as $groupFilter) {
            /* @var Filter $groupFilter */
            $groupFilter->build($query);
        }

        return $query;
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
     * Grouping filters for future building query
     * @param array $filters
     * @return array
     * @throws \Exception
     */
    protected function makeFilterGroups(array $filters = [])
    {
        foreach ($filters as $alias => $parameters) {
            /* @var string|Filter $filterNamespace */
            $filterNamespace = $this->find($alias);

            if (!$filterNamespace) {
                continue;
            }

            /* @var Filter $filterClass */
            $filterClass = new $filterNamespace;

            $groupName = $this->getGroupName($filterClass->modelNamespace());

            if (!$this->hasUsed($groupName, $alias)) {
                $this->usingFilters[$groupName][$alias] = $filterClass;
                $this->usingFilters[$groupName][$alias]->addParameters($filters);
            }
        }

        return $this->usingFilters;
    }

    /**
     * Getting method to get model query
     * @param Filter $filter
     * @return string
     */
    public function getModelQuery($filter)
    {
        return call_user_func([$filter->modelNamespace(), 'query']);
    }

    /**
     * Finding filter by it alias
     * @param string $alias
     * @return string|bool
     */
    protected function find($alias)
    {
        return array_key_exists($alias, $this->registeredFilters) ? $this->registeredFilters[$alias] : false;
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
     * Applying filters only for specified model
     * @param string $namespace
     * @param array $filters
     * @param Builder $query
     * @return Builder
     */
    public function filterModel($namespace, array $filters = [], Builder $query = null)
    {
        $modelFilters = $this->makeModelGroup($namespace, $filters);

        return $this->makeGroup($modelFilters, $query);
    }

    /**
     * Making group of filters for model namespace
     * @param string $namespace
     * @param array $filters
     * @return array
     */
    protected function makeModelGroup($namespace, array $filters)
    {
        $this->usingFilters[$namespace] = [];

        foreach ($filters as $alias => $parameters) {
            /* @var string|Filter $filterNamespace */
            $filterNamespace = $this->find($alias);

            if (!$filterNamespace) {
                continue;
            }

            if ($filterNamespace::modelNamespace() != $namespace) {
                continue;
            }

            if (!$this->hasUsed($namespace, $alias)) {
                $this->usingFilters[$namespace][$alias] = new $filterNamespace;
                $this->usingFilters[$namespace][$alias]->addParameters($filters);
            }
        }

        return $this->usingFilters[$namespace];
    }
}
