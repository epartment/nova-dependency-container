# Nova Field Dependency Container

[![Latest Version on Packagist](https://img.shields.io/packagist/v/epartment/nova-dependency-container.svg)](https://packagist.org/packages/epartment/nova-dependency-container)
[![Total Downloads](https://img.shields.io/packagist/dt/epartment/nova-dependency-container.svg)](https://packagist.org/packages/epartment/nova-dependency-container)
[![License](https://img.shields.io/packagist/l/epartment/nova-dependency-container.svg)](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md)

### Description

A container for grouping fields that depend on other field values. Dependencies can be set on any field type or value.

### Demo

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo.gif)


### Versions

 - install v1.2.0 for Laravel v5.8 or v6.x and Nova 2.x
 - install v1.1.2 for Laravel v5.7 and Nova v1.x

### Installation

The package can be installed through Composer.

```bash
composer require epartment/nova-dependency-container
```



### Usage

1. Add the `Epartment\NovaDependencyContainer\HasDependencies` trait to your Nova Resource.
2. Add the `Epartment\NovaDependencyContainer\NovaDependencyContainer` to your Nova Resource `fields` method.

```php
class Page extends Resource
{
    use HasDependencies;

    public function fields(Request $request)
    {
        return [
            
            Select::make('Name format', 'name_format')->options([
                0 => 'First Name',
                1 => 'First Name / Last Name',
                2 => 'Full Name'
            ])->displayUsingLabels(),

            NovaDependencyContainer::make([
                Text::make('First Name', 'first_name')
            ])->dependsOn('name_format', 0),

        ];
    }
}
```



### Dependencies

The package supports two kinds of dependencies:

1. `->dependsOn('field', 'value')`
2. `->dependsOnNotEmpty('field')`

These dependencies can be combined by chaining the methods on the `NovaDependencyContainer`:

```php
NovaDependencyContainer::make(...)
    ->dependsOn('field1', 'value1')
    ->dependsOnNotEmpty('field2')
    ->dependsOn('field3', 'value3')
```

The fields used as dependencies can by of any of the default Laravel Nova field types.

For example a checkbox:

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo-2.gif)



### BelongsTo dependency

If we follow the example of a *Post model belongsTo a User model*, taken from Novas
documentation [BelongsTo](https://nova.laravel.com/docs/2.0/resources/relationships.html#belongsto), the dependency
setup could look like this. We use the singular form of the `belongsTo` model in lower case, in this example `Post` becomes `post`.

```php
BelongsTo::make('Post'),

NovaDependencyContainer::make([
    Text::make('Visible')
])->dependsOn('post', 2) // 
```

### MorphTo dependency

A similar example from Novas documentation for [MorphTo](https://nova.laravel.com/docs/2.0/resources/relationships.html#morphto), uses 3 Models; `Comment`, `Video` and `Post`.
`Commentable` becomes `commentable` and the value to depend on are the Models singular form. In this example the dependency container
will add two additional fields, `Additional Text` and `Visible`, only when the `Post` model is selected.

```php
MorphTo::make('Commentable')->types([
    Post::class,
    Video::class,
]),

NovaDependencyContainer::make([
    Text::make('Additional Text', 'additional'),
    Boolean::make('Visible', 'visible')
])
    ->dependsOn('commentable', 'Post') 
```


### Releases

I'm going to abuse this README for versioning ...

 - v1.2.1
   - fixed support for [BelongsTo](https://nova.laravel.com/docs/1.0/resources/relationships.html#belongsto) and [MorphTo](https://nova.laravel.com/docs/1.0/resources/relationships.html#morphto) fields (@mikaelpopowicz, @dbf)
 - v1.2.0 
   - working version for Laravel 5.8 | 6 and Nova 2.x. (@FastPointGaming, @spaceo, @cdbeaton, @yaroslawww)



### License

The MIT License (MIT). Please see [License File](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md) for more information.
