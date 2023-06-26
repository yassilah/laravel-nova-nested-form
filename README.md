[![Latest Stable Version](https://poser.pugx.org/yassi/nova-nested-form/v/stable)](https://packagist.org/packages/yassi/nova-nested-form) [![Total Downloads](https://poser.pugx.org/yassi/nova-nested-form/downloads)](https://packagist.org/packages/yassi/nova-nested-form) [![Latest Unstable Version](https://poser.pugx.org/yassi/nova-nested-form/v/unstable)](https://packagist.org/packages/yassi/nova-nested-form) [![License](https://poser.pugx.org/yassi/nova-nested-form/license)](https://packagist.org/packages/yassi/nova-nested-form) [![Monthly Downloads](https://poser.pugx.org/yassi/nova-nested-form/d/monthly)](https://packagist.org/packages/yassi/nova-nested-form) [![Daily Downloads](https://poser.pugx.org/yassi/nova-nested-form/d/daily)](https://packagist.org/packages/yassi/nova-nested-form)

# Nova Nested Form

This package allows you to include your nested relationships' forms into a parent form. Test CI/CD

# Installation

```bash
composer require yassi/nova-nested-form
```

# Contributions

As I did not anticipate so many people would use that package (which is awesome) and simply do not have enough time to update/enhance this package more regularly on my own, I am looking for other contributors to help me with the maintenance and feature requests. Don't hesitate to contact me if you're interested!  

# Update to 3.0

The **afterFill** and **beforeFill** methods are no longer available.

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

# Choose when to display the form

For instance, if the nested form should only be available if the value of the "has_comments" attirbute is true, you can use:

```php
class Post extends Resource
{
    ...
    public function fields(Request $request)
    {
        return [
            Boolean::make('Has Comments'),
            NestedForm::make('Comments')->displayIf(function ($nestedForm, $request) {
               return [
                    [ 'attribute' => 'has_comments', 'is' => true ]
               ];
        ];
    }
})
```

The **displayIf** method is excepted to return an array of array as you may want to add several conditions.

```php
class Post extends Resource
{
    ...
    public function fields(Request $request)
    {
        return [
            Boolean::make('Has Comments'),
            Text::make('Title'),
            Text::make('Subtitle')->nullable(),
            Number::make('Number of comments allowed'),
            NestedForm::make('Comments')->displayIf(function ($nestedForm, $request) {
                return [
                    [ 'attribute' => 'has_comments', 'is' => true ],
                    [ 'attribute' => 'title', 'isNotNull' => true ],
                    [ 'attribute' => 'subtitle', 'isNull' => true ],
                    [ 'attribute' => 'title', 'includes' => 'My' ],
                    [ 'attribute' => 'number_of_comments_allowed', 'moreThanOrEqual' => 1 ],

                    // Integration for nova booleanGroup field
                    [ 'attribute' => 'my_multiple_checkbox', 'booleanGroup' => 'the_checkbox_key_to_target' ],
                ];
            })
        ];
    }
}
```

The package will then add those conditions and dynamically update your form as you fill the fields. The available rules are:

- [x] is
- [x] isNot
- [x] isNull
- [x] isNotNull
- [x] isMoreThan
- [x] isMoreThanOrEqual
- [x] isLessThan
- [x] isLessThanOrEqual
- [x] includes
- [x] booleanGroup

# Add a minimum or a maximum number of children

For instance, if you want every user to have at least 3 posts and at most 5 posts, simply use:

```php
NestedForm::make('Posts')->min(3)->max(5),
```

Please note that the package automatically detects whether the relationship excepts many children or a single child, and sets the maximum value accordingly.

When creating a new user, 3 blank posts will be displayed. If you reach the maximum number of posts, the "Add a new post" button will disappear.

# Set the default open/collapse behavior

If you want the nested forms to be opened by default, simply use:

```php
NestedForm::make('Posts')->open(true),
```

# Modify the default heading

You can modify the default heading using the heading() method. You can use the helper method **wrapIndex()** to add the current child index to your header.

```php
NestedForm::make('Posts')->heading(NestedForm::wrapIndex() . ' // Post'),
```

You can also add any attribute of the current child into your heading using the helper method **wrapAttribute()**.

```php
NestedForm::make('Posts')->heading(NestedForm::wrapIndex() . ' // ' . NestedForm::wrapAttribute('title', 'My default title')),
```

# Modify the index separator

You can modify the default index separator using the separator() method when you have nested forms (e.g. 1. Post, 1.1. Comment, 1.1.1. Like).

```php
NestedForm::make('Posts')->separator('\'),
```
