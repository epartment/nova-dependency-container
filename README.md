# Nova Field Dependency Container

[![Latest Version on Packagist](https://img.shields.io/packagist/v/epartment/nova-dependency-container.svg)](https://packagist.org/packages/epartment/nova-dependency-container)
[![Total Downloads](https://img.shields.io/packagist/dt/epartment/nova-dependency-container.svg)](https://packagist.org/packages/epartment/nova-dependency-container)
[![License](https://img.shields.io/packagist/l/epartment/nova-dependency-container.svg)](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md)

### Description

A container for grouping fields that depend on other field values. Dependencies can be set on any field type or value.

### Demo

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo.gif)

### Installation

Install through composer: `composer require epartment/nova-dependency-container`

### Usage

Add the `Eparment\NovaDependencyContainer\HasDependencies` trait to your Nova Resource.

```php
namespace App\Nova;

use Eparment\NovaDependencyContainer\HasDependencies;

class Page extends Resource
{
    use HasDependencies;

    ...
}
```

Add a new `NovaDependencyContainer` to your Nova Resource.

```php
use \Epartment\NovaDependencyContainer\NovaDependencyContainer;

Select::make('Name format', 'name_format')->options([
    0 => 'First Name',
    1 => 'First Name / Last Name',
    2 => 'Full Name'
])->displayUsingLabels(),

NovaDependencyContainer::make([

    Text::make('First Name', 'first_name')

])->dependsOn('name_format', 0),
```

### Options

It's possible to rely on just a field without requiring a specific value. This is especially for handling relationshop fields, like a `BelongsTo` field.

```php
NovaDependencyContainer::make([

    Text::make('First Name', 'first_name')

])->dependsOnNotEmpy('customer'),
```

It is also possible to set up multiple dependencies for your container by calling `dependsOn` multiple times on the container.

You can use any type of field type dependency, i.e. a checkbox:

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo-2.gif)

```php
Boolean::make('Active', 'active'),

NovaDependencyContainer::make([

    Text::make('First Name', 'first_name')

])->dependsOn('active', true),
```

### Data handling
The container it self won't contain any values and will simply pass through the values of the contained fields and their corresponding attributes.

### License
The MIT License (MIT). Please see [License File](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md) for more information.
