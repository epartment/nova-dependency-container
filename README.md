# Nova Field Dependency Container

![Packagist](https://img.shields.io/packagist/v/epartment/nova-dependency-container.svg)
![Packagist](https://img.shields.io/packagist/dt/epartment/nova-dependency-container.svg)
![Packagist](https://img.shields.io/packagist/l/epartment/nova-dependency-container.svg)

### Description
A container for grouping fields that depend on other field values. Dependencies can be set on any field type or value.

### Demo

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo.gif)

### Installation
Install through composer: `composer require epartment/nova-dependency-container`

### Usage

Add a new `NovaDependencyContainer` to your Nova Resource:

```php
\Laravel\Nova\Fields\Select::make('Name format', 'name_format')
    ->options([
        0 => 'First Name',
        1 => 'First Name / Last Name',
        2 => 'Full Name'
    ])->displayUsingLabels(),

\Epartment\NovaDependencyContainer\NovaDependencyContainer::make('Dependent settings', [
    \Laravel\Nova\Fields\Text::make('First Name', 'first_name')
])->dependsOn('name_format', 0)->onlyOnForms(),
```

It is also possible to set up multiple dependencies for your container by calling `dependsOn` multiple times on the container. 

You can use any type of field type dependency, i.e. a checkbox:

```php
\Laravel\Nova\Fields\Boolean::make('Active', 'active'),

\Epartment\NovaDependencyContainer\NovaDependencyContainer::make('Dependent settings', [
    \Laravel\Nova\Fields\Text::make('First Name', 'first_name')
])->dependsOn('active', true)->onlyOnForms(),
```

The container it self won't contain any values and will simply pass through the values of the contained fields and their corresponding attributes.

### License
The MIT License (MIT). Please see [License File](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md) for more information.
