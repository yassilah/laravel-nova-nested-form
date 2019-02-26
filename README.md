
[![Latest Stable Version](https://poser.pugx.org/yassi/nova-nested-form/v/stable)](https://packagist.org/packages/yassi/nova-nested-form)
[![Total Downloads](https://poser.pugx.org/yassi/nova-nested-form/downloads)](https://packagist.org/packages/yassi/nova-nested-form)
[![Latest Unstable Version](https://poser.pugx.org/yassi/nova-nested-form/v/unstable)](https://packagist.org/packages/yassi/nova-nested-form)
[![License](https://poser.pugx.org/yassi/nova-nested-form/license)](https://packagist.org/packages/yassi/nova-nested-form)
[![Monthly Downloads](https://poser.pugx.org/yassi/nova-nested-form/d/monthly)](https://packagist.org/packages/yassi/nova-nested-form)
[![Daily Downloads](https://poser.pugx.org/yassi/nova-nested-form/d/daily)](https://packagist.org/packages/yassi/nova-nested-form)

# Nova Nested Form

This package allows you to include your nested relationships' forms into a parent form.

# Release note for 2.0.5

- [x] Max number of children
- [x] Min number of children
- [x] Set default open/collapse behavior
- [x] Set heading template
- [x] Set separator
- [x] No need for trait anymore
- [x] Auto detection of parent ID field
- [x] Catching UpdatedSinceLastRetrieval exception (even for children)
- [x] AfterFillCallback
- [x] BeforeFillCallback
- [x] Updated design for empty relationship
- [x] Handle file uploads
- [ ] **COMING** Conditional nested forms
- [ ] **COMING** Updated design for single field nested form

# Important changes since 1.0.4

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

# NEW: Add a callback before and/or after your relationships have been updated

For instance, if you have to modify a value on the request before the nested form is filled or trigger an event after all relations have been set, you can now simply use this:

```php
NestedForm::make('Posts')
->beforeFill(function ($request, $model, $attribute, $requestAttribute) {
    $request->merge(['key' => 'value']);
    // or
    if (!$model->hasSomeProperty) {
        throw new \Exception('You cannot do this.');
    }
})
->afterFill(function ($request, $model, $attribute, $requestAttribute, $touched) {
    $touched->each(function ($model) {
        if ($model->wasRecentlyCreated) {
            // do something
        }
    });
})
```

# Add a minimum or a maximum number of children

For instance, if you want every user to have at least 3 posts and at most 5 posts, simply use:

```php
NestedForm::make('Posts')->min(3)->max(5),
```

When creating a new user, 3 blank posts will be displayed. If you reach the maximum number of posts, the "Add a new post" button will disappear.

# Set the default open/collapse behavior

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
