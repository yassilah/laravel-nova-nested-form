# Nova Nested Form

This package allows you to include your nested relationships' forms into a parent form.

# Important changes in 1.0.4

To be more consistent with Nova's other fields, the order of the parameters has changed to become:

```php
NestedForm::make($name, $viaRelationship = null, $class = null),
```

For instance, this:

```php
NestedForm::make('Posts'),
```

Is now the same as:

```php
NestedForm::make('Posts', 'posts', Post::class),
```

Also, translations are now available in your nested field! You just need to add this key in you language file:

```json
"Add a new :resourceSingularName": "Ajouter un(e) :resourceSingularName"
```

# Installation

```bash
composer require yassi/nova-nested-form
```

Then add the NestedFormTrait to your App\Nova\Resource class.

```php
use Yassi\NestedForm\Traits\NestedFormTrait;

abstract class Resource extends NovaResource
{
    use NestedFormTrait;
```

# Attach a new relationship form to a resource

Simply add a NestedForm into your fields. The first parameter must be an existing NovaResource class and the second parameter (optional) must be an existing HasOneOrMany relationship in your model.

```php
namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\Password;
// Add use statement here.
use Yassi\NestedForm\NestedForm;

class User extends Resource
{
    ...
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Gravatar::make(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:6')
                ->updateRules('nullable', 'string', 'min:6'),
                
            // Add NestedForm here.
            NestedForm::make('Posts'),
        ];
    }
```

You can also nest your relationship forms by adding another NestedForm into the fields of your App\Nova\Post.

# NEW: Add a minimum or a maximum number of children

For instance, if you want every user to have at least 3 posts and at most 5 posts, simply use:

```php
NestedForm::make('Posts')->min(3)->max(5),
```

When creating a new user, 3 blank posts will be displayed. If you reach the maximum number of posts, the "Add a new post" button will disappear.

# NEW: Set the default open/collapse behavior

If you want the nested forms to be opened by default, simply use:

```php
NestedForm::make('Posts')->open(true),
```

You can also decide to open only the first nested form by using:

```php
NestedForm::make('Posts')->open('only first'),
```

# Modify the default heading

You can modify the default heading using the heading() method. You can use wildcards to add dynamic content to your label such as '{{id}}', '{{index}}' or any attribute present in the form.

```php
NestedForm::make('Posts')->heading('{{index}} // Post - {{title}}'),
```

# Modify the index separator

You can modify the default index separator using the separator() method when you have nested forms (e.g. 1. Post, 1.1. Comment, 1.1.1. Like).

```php
NestedForm::make('Posts')->separator('\'),
```
