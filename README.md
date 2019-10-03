# Nova Field Dependency Container

[![Latest Version on Packagist](https://img.shields.io/packagist/v/epartment/nova-dependency-container.svg)](https://packagist.org/packages/epartment/nova-dependency-container)
[![Total Downloads](https://img.shields.io/packagist/dt/epartment/nova-dependency-container.svg)](https://packagist.org/packages/epartment/nova-dependency-container)
[![License](https://img.shields.io/packagist/l/epartment/nova-dependency-container.svg)](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md)

<br />

### Description

A container for grouping fields that depend on other field values. Dependencies can be set on any field type or value.

<br />

### Demo

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo.gif)

<br />

### Versions

 - install v1.2.x for Laravel v5.8 or v6.x and Nova 2.x
 - install v1.1.2 for Laravel v5.7 and Nova v1.x

<br />

### Installation

The package can be installed through Composer.

```bash
composer require epartment/nova-dependency-container
```

<br />

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

<br />

### Dependencies

The package supports four kinds of dependencies:

1. `->dependsOn('field', 'value')`
2. `->dependsOnEmpty('field')`
3. `->dependsOnNotEmpty('field')`
4. `->dependsOnNullOrZero('field')`

These dependencies can be combined by chaining the methods on the `NovaDependencyContainer`:

```php
NovaDependencyContainer::make([
  // dependency fields
])
->dependsOn('field1', 'value1')
->dependsOnNotEmpty('field2')
->dependsOn('field3', 'value3')
```

The fields used as dependencies can be of any Laravel Nova field type. Currently only two relation field types are supported, `BelongsTo` and `MorphTo`. 

Here is an example using a checkbox:

![Demo](https://raw.githubusercontent.com/epartment/nova-dependency-container/master/docs/demo-2.gif)


<br />

### BelongsTo dependency

If we follow the example of a *Post model belongsTo a User model*, taken from Novas documentation [BelongsTo](https://nova.laravel.com/docs/2.0/resources/relationships.html#belongsto), the dependency setup has the following construction.

We use the singular form of the `belongsTo` resource in lower case, in this example `Post` becomes `post`. Then we define in dot notation, the property of the resource we want to depend on. In this example we just use the `id` property, as in `post.id`.

```php
BelongsTo::make('Post'),

NovaDependencyContainer::make([
    Boolean::make('Visible')
])
->dependsOn('post.id', 2)
```

When the `Post` resource with `id` 2 is being selected, a `Boolean` field will appear.

<br />

### MorphTo dependency

A similar example taken from Novas documentation for [MorphTo](https://nova.laravel.com/docs/2.0/resources/relationships.html#morphto) is called commentable. It uses 3 Models; `Comment`, `Video` and `Post`. Here `Comment` has the morphable fields `commentable_id` and `commentable_type`

For a `MorphTo` dependency, the following construction is needed.

`Commentable` becomes lower case `commentable` and the value to depend on is the resource singular form. In this example the dependency container will add two additional fields, `Additional Text` and `Visible`, only when the `Post` resource is selected.

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

<br />

### Releases

I'm going to abuse this README for versioning ...

 - v1.2.4
 	- Fixes a problem where fields with same names use previous validation rules when used in multiple containers [#81](https://github.com/epartment/nova-dependency-container/issues/81), [#60](https://github.com/epartment/nova-dependency-container/pull/60) (@melewski)
 - v1.2.3
   - Fixed belongs-to/morph-to as nested fields [#80](https://github.com/epartment/nova-dependency-container/issues/80)
   - Added missing methods in validation rules
 - v1.2.2
 	- fixed fields not resolving when using `displayUsingLabels`, `resolveUsing` or `displayUsing`. (@dbf)
 	- fixed action fields who do not return a collection (@bsormagec)
 	- added two new methods `onEmpty` and `onNullOrZero`. (@niektenhoopen, @dbf)
 	- changed the logic for dependency satisfaction where it is reversed
 	- changed `field` in two separate names; `field` and `property`, to avoid confusion when being used on a resource or component.
 - v1.2.1
   - fixed support for [BelongsTo](https://nova.laravel.com/docs/1.0/resources/relationships.html#belongsto) and [MorphTo](https://nova.laravel.com/docs/1.0/resources/relationships.html#morphto) fields (@mikaelpopowicz, @dbf)
 - v1.2.0 
   - working version for Laravel 5.8 | 6 and Nova 2.x. (@FastPointGaming, @spaceo, @cdbeaton, @yaroslawww)

<br />

### License

The MIT License (MIT). Please see [License File](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md) for more information.
