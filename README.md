# laravel-filter
The filter service for laravel

# Installation

To install you just need to require package:

```
composer require ilyasavich/filter
```

# Usage

## Create service

First of all you need to extending from `Savich\Filter\Kernel` class
Communication with the service will be provided using this class

```
use Savich\Filter\Kernel;

class YourFilterKernel extends Kernel
{
}
```

## Create custom filters


### Create class

To create your custom filter you need to extending from `Savich\Filter\Contracts\Filter` class

```
use Savich\Filter\Contracts\Filter;

class YourCustomFilter extends Filter
{
}
```

### Initialize custom filter

In created `YourCustomFilter` class you necessarily need to implement 3 methods:

+ First method is `alias()`
You need to specify the alias of your filter in return statement. By this alias the system will search the filter class.

+ The second you need to implement `modelNamespace()` method.
There you need to specify the model namespace in return statement. The filter will be applied to this model

+ And finally you need implement the method `build()`
There you can specify the logic of the query of your filter.

Example:
```
use Savich\Filter\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use My\Model\Namaspace\MyModel;

class YourCustomFilter extends Filter
{
    public function alias()
    {
        return 'filter_alias';
    }
    
    public function modelNamespace()
    {
        return MyModel::class;
    }
    
    public function build(Builder $query)
    {
        return $query->where('id', '!=', 1); // example query
    }
}
```

### Register your filter

To say to the system about your filter just override the `$filters` field in your filter kernel and add your custom filter to this array. Example:

```
use Savich\Filter\Kernel;

class YourFilterKernel extends Kernel
{
    protected $filters = [
        YourCustomFilter::class,
    ];
}
```

### Using Filter Trait

If you want to use filter in models you need use `Filter` trait for it.
 
```
use Savich\Filter\Mixins\Filter;

class YourModel extends Model
{
    use Filter;
}

```

### Use

To use the system you need to call method `make()` of the filter kernel class. Example:
```
use Namespace\Of\YourFilterKernel;

$kernel = YourFilterKernel::instance();
$kernel->make();
// or
$kernel->make($arrayOfFilterAliases);

// or through models

YourModel::filter()->get();
// or
YourModel::filter($filters)->get();
```

In case using filters in models method `filter` returns instance of `Illuminate\Database\Eloquent\Builder` so that you can use it in query chains.

### What will happen?

<b>By default</b> the method `make()` will get filters aliases from the url parameters and try to find there in filters that you registered in kernel.
If you will pass array of filters in `make` method it will filtering through this filters
Then it will grouping all filters by models namespaces and building query for each group
The method `make()` return array, where keys are models namespaces and values are instances of `Illuminate\Database\Eloquent\Builder`

## Example of works <a name="example-of-works"></a>

For example you try to filtering data by this url `http://domain.com/url/path?0=filter1&1=filter2%3Aparam1%2Cparam2&2=filter2%3Aparam3`
Url parameters have the following representation: `['filter1', 'filter2:param1,param2', 'filter2:param3']`

```
use Savich\Filter\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use My\Model\Namaspace\MyModel;

class Filter1 extends Filter
{
    public function alias()
    {
        return 'filter1';
    }
    
    public function modelNamespace()
    {
        return MyModel::class;
    }
    
    public function build(Builder $query)
    {
        return $query->where('id', '!=', 1); // example query
    }
}
==========================================

use Savich\Filter\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use My\Model\Namaspace\MyModel;

class Filter2 extends Filter
{
    public function alias()
    {
        return 'filter2';
    }
    
    public function modelNamespace()
    {
        return MyModel::class;
    }
    
    public function build(Builder $query)
    {
        return $query->whereIn('id', '!=', $this->parameters); // example query
    }
}
==========================================

use Savich\Filter\Kernel;

class YourFilterKernel extends Kernel
{
    protected $filters = [
        Filter1::class,
        Filter2::class,
    ];
}
==========================================

use Namespace\Of\YourFilterKernel;

$kernel = YourFilterKernel::instance();
$kernel->make();
// or
$kernel->make($arrayOfFilterAliases);

```

The method make will return associative array:

```
array (size=1)
  'My\Model\Namaspace\MyModel' => object(Illuminate\Database\Eloquent\Builder)
```

## URL pass parameters

So you need to set in url filters as array, parameters of the filters are optional. Example array got, `['filter1', 'filter2:param1,param2', 'filter2:param3']`
Parameters can using, for example, when you need to apply filters on roles in your table users.

You have several filters:

+ for role user
+ for role admin
+ for role publisher and so on.

You can set as: 

+ role:user
+ role:admin
+ role:publisher

and add there in url.
It will be looks like `['role:user', 'role:admin', 'role:publisher']`

The system parse each filter and create single class with alias `role` and will set `user`, `admin` and `publisher` to `$parameters` array in your custom filter. This is you can see above in [there](##example-of-works)

## Some Customizations

### Customize Group Name

If you need to customize groups names, you don't want to grouping by whole model namespace, you can make some customization not so much as may you want or need, but still you have some possibilities
For it in your filter kernel class override method `getGroupName()` that passing 1 parameter and it is model namespace

```
use Savich\Filter\Kernel;

class YourFilterKernel extends Kernel
{
    protected function ($modelNamspace)
    {
        $groupName = lowercase($modelNamespace); // example
        
        return $groupName; 
    }
}
```

If you have any idea how it can be improved if this is really necessarily send me email.