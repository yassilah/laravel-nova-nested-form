
# Nova Nested Form
This package allows you to include your nested relationships' forms into a parent form.

# Installation 

```
composer require yassi/nova-nested-form
```

# Attach a new relationship form to a resource

Simply add a NestedForm into your fields. The first parameter must be an existing NovaResource class and the second parameter (optional) must be an existing HasOneOrMany relationship in your model.

<pre>
namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\Password;
<b>use Yassi\NestedForm\NestedForm;</b>

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

            <b>NestedForm::make(Post::class, 'posts')</b>
        ];
    }
</pre>


You can also nest your relationship forms by adding another NestedForm into the fields of your App\Nova\Post.

# Modify the default heading

You can modify the default heading using the heading() method. You can use wildcards to add dynamic content to your label such as '{{id}}', '{{index}}' or any attribute present in the form.
```
NestedForm::make('Posts')
->heading('{{index}} // Post - {{title}}')
```