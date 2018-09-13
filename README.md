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

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo-2.gif)

```php
\Laravel\Nova\Fields\Boolean::make('Active', 'active'),

\Epartment\NovaDependencyContainer\NovaDependencyContainer::make('Dependent settings', [
    \Laravel\Nova\Fields\Text::make('First Name', 'first_name')
])->dependsOn('active', true)->onlyOnForms(),
```

### 3rd Party support
Using the `dependsOnCustomComponent` method on the Dependency Container, you can target 3rd Party Nova Components. Specifying the exact component name , the Dependency Container will add value watchers to these components, allowing you to depend on their values. You can find the component names `field.js` file in the source of the 3rd party package. Usually they start with `form-{component-name}`.

For example using the https://github.com/davidpiesse/nova-toggle field, you can get the name of the component here: https://github.com/davidpiesse/nova-toggle/blob/master/resources/js/field.js#L7 and use it like below:

```php
\Davidpiesse\NovaToggle\Toggle::make('Our Option', 'selectable_option')->hideFromIndex(),

\Epartment\NovaDependencyContainer\NovaDependencyContainer::make('Dependent settings', [
    \Laravel\Nova\Fields\Trix::make('Details', 'selectable_option_details'),
])->dependsOn('selectable_option', true)->dependsOnCustomComponent('form-nova-toggle'),
```


### Data handling
The container it self won't contain any values and will simply pass through the values of the contained fields and their corresponding attributes.

### License
The MIT License (MIT). Please see [License File](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md) for more information.
