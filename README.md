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
3. Add the `Epartment\NovaDependencyContainer\ActionHasDependencies` trait to your Nova Actions that you wish to use dependencies on.

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
2. `->dependsOnNot('field', 'value')`
3. `->dependsOnEmpty('field')`
4. `->dependsOnNotEmpty('field')`
5. `->dependsOnNullOrZero('field')`

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

### BelongsToMany dependency

A [BelongsToMany](https://nova.laravel.com/docs/2.0/resources/relationships.html#belongstomany) setup is similar to that of a [BelongsTo](https://nova.laravel.com/docs/2.0/resources/relationships.html#belongsto).

The `dependsOn` method should be pointing to the name of the intermediate table. If it is called `role_user`, the setup should be 

```php
BelongsToMany::make('Roles')
	->fields(function() {
		return [
			NovaDependencyContainer::make([
			    // pivot field rules_all
			    Boolean::make('Rules All', 'rules_all')
			])
			->dependsOn('role_user', 1)
		]
	}),
```

If the pivot field name occurs multiple times, consider using [custom intermediate table models](https://laravel.com/docs/6.x/eloquent-relationships#defining-custom-intermediate-table-models) and define it in the appropiate model relation methods. The only reliable solution I found was using mutators to get/set a field which was being used multiple times. Although this may seem ugly, the events which should be fired on the intermediate model instance, when using an Observer, would work unreliable with every new release of Nova.

> If Nova becomes reliable firing eloquent events on the intermediate table, I will update this examples with a more elegant approach using events instead.

Here is an (ugly) example of a get/set mutator setup for an intermediate table using a pivot field called `type`.

```php
// model User
class User ... { 
   
   public function roles() {
   		return $this->belongsToMany->using(RoleUser::class)->withPivot('rules_all');
   }
   
}

// model Role
class Role ... { 
   
   public function users() {
   		return $this->belongsToMany->using(RoleUser::class)->withPivot('rules_all');
   }
   
}

// intermediate table
use Illuminate\Database\Eloquent\Relations\Pivot;
class RoleUser extends Pivot {  

	protected $table 'role_user';

	public function getType1Attribute() {
	    return $this->type;
	}

	public function setType1Attribute($value) {
		$this->attributes['type'] = $value;
	}

	// ... repeat for as many types as needed
}
```

And now for the dependency container.

```php
->fields(function() {
	return [
		NovaDependencyContainer::make([
		    // pivot field rules_all
		    Select::make('Type', 'type_1')
		    	->options([ 
		    		/* some options */ 
	    		])
		    	->displayUsingLabels()
		])
		->dependsOn('role_user', 1)
		,
	
		NovaDependencyContainer::make([
		    // pivot field rules_all
		    Select::make('Type', 'type_2')
		    	->options([ 
		    		/* different options */ 
	    		])
		    	->displayUsingLabels()
		])
		->dependsOn('role_user', 2)
		,
		
		// .. and so on
	]
}),
```

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

### License

The MIT License (MIT). Please see [License File](https://github.com/epartment/nova-dependency-container/blob/master/LICENSE.md) for more information.
