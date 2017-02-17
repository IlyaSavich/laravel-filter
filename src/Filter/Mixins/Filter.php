<?php

namespace Savich\Filter\Mixins;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Savich\Filter\Kernel;

/**
 * Class Filter
 * @package Savich\Filter\Mixins
 * @method static Builder|\Illuminate\Database\Eloquent\Collection|\Eloquent filter(array $filters = [])
 * @mixin Model|\Eloquent
 */
trait Filter
{
    /**
     * @param Builder $query
     * @param array|Collection $filters
     * @return mixed
     * @throws \Exception
     */
    public function scopeFilter($query, $filters = [])
    {
        if (is_null($filters)) {
            $filters = [];
        }

        if (!is_array($filters) && !$filters instanceof Collection) {
            throw new \Exception('Filters must be array or Collection type. Got ' . gettype($filters));
        }

        if (empty($filters)) {
            return static::query();
        }

        /* @var Kernel $kernel */
        $kernel = call_user_func([config('laravel-filter.kernel'), 'instance']);

        return $kernel->filterModel(static::class, $filters, $query);
    }
}