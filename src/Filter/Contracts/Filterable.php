<?php

namespace Savich\Filter\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filterable
{
    /**
     * Building query for applying filters from url.
     * @param Builder $query
     * @return Builder
     */
    public function build(Builder $query);

    /**
     * Specify model namespace
     * By this namespace will be grouping filters
     * @return string
     */
    public static function modelNamespace();

    /**
     * Specify alias of the filter
     * By this alias will be searching filter class
     * @return string
     */
    public static function alias();
}
