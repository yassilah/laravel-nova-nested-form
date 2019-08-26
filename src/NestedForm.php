<?php

namespace Yassi\NestedForm;

use App\Nova\Resource;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Panel;

class NestedForm extends Field
{

    /**
     * Wrap left.
     * 
     * @var string
     */
    const WRAP_LEFT = '{{';

    /**
     * Wrap right.
     * 
     * @var string
     */
    const WRAP_RIGHT = '}}';

    /**
     * INDEX.
     * 
     * @var string
     */
    const INDEX = 'INDEX';

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nested-form';

    /**
     * The field's relationship resource class.
     * 
     * @var string
     */
    public $resourceClass;

    /**
     * The field's relationship resource name.
     * 
     * @var string
     */
    public $resourceName;

    /**
     * The field's relationship name.
     * 
     * @var string
     */
    public $viaRelationship;

    /**
     * The field's singular label.
     * 
     * @var string
     */
    public $singularLabel;

    /**
     * The field's plural label.
     * 
     * @var string
     */
    public $pluralLabel;

    /**
     * From resource uriKey.
     * 
     * @var string
     */
    public $fromResource;

    /**
     * Whether the form should be opened by default.
     * 
     * @var boolean
     */
    public $opened = true;

    /**
     * The heading template for children.
     * 
     * @var string
     */
    public $heading;

    /**
     * The maximum number of children.
     * 
     * @var int
     */
    public $max = 0;

    /**
     * The minimum number of children.
     * 
     * @var int
     */
    public $min = 0;

    /**
     * Condition to display the nested form.
     */
    public $displayIf;

    /**
     * Panel instance: this is the panel within which 
     * the current nested form will be displayed.
     * 
     * @var Panel
     */
    protected $panelInstance;

    /**
     * Create a new nested form.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct(string $name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);
        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->viaRelationship = $this->attribute;
        $this->singularLabel = Str::singular($this->name);
        $this->pluralLabel = Str::plural($this->name);

        $request = app(NovaRequest::class);

        $this->fromResource = $request->route('resource');
    }

    /**
     * Resolve the form fields.
     *
     * @param $resource
     * @param $attribute
     *
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        $this->withMeta([
            'children' => $resource->{$this->viaRelationship}()->get()->map(function ($model, $index) {
                return NestedFormChild::make($model, $index, $this);
            })->all(),
            'schema' => NestedFormSchema::make($resource->{$this->viaRelationship}()->getModel(), static::wrapIndex(), $this)
        ]);
    }


    /**
     * Set the heading.
     * 
     * @param string $heading
     */
    public function heading(string $heading)
    {
        $this->heading = $heading;

        return $this->panelInstance ?? $this;
    }

    /**
     * Set whether the form should be opened by default.
     * 
     * @param boolean $opened
     */
    public function opened(boolean $opened)
    {
        $this->opened = $opened;

        return $this->panelInstance ?? $this;
    }

    /**
     * Set the maximum number of children.
     */
    public function max(int $max)
    {
        $this->max = $max;

        return $this->panelInstance ?? $this;
    }

    /**
     * Set the minimum number of children.
     */
    public function min(int $min)
    {
        $this->min = $min;

        return $this->panelInstance ?? $this;
    }

    /**
     * Set the condition to display the form.
     */
    public function displayIf(\Closure $displayIfCallback)
    {
        $this->displayIf = collect(call_user_func($displayIfCallback, $this, app(Novarequest::class)))->map(function ($condition) {
            if (isset($condition['attribute'])) {
                $condition['attribute'] = static::conditional($condition['attribute']);
            }

            return $condition;
        });

        return $this->panelInstance ?? $this;
    }

    /**
     * Set the panel instance.
     */
    public function setPanelInstance(Panel $panel)
    {
        $this->panelInstance = $panel;
    }

    /**
     * Create a new NestedForm instance.
     */
    public static function make(...$arguments)
    {
        return new NestedFormPanel(new static(...$arguments));
    }

    /**
     * Wrap an attribute into a dynamic attribute
     * value.
     * 
     * @param string $attribute
     * @param string $default
     */
    public static function wrapAttribute(string $attribute, $default = '')
    {
        return self::WRAP_LEFT . $attribute . '|' . $default . self::WRAP_RIGHT;
    }

    /**
     * Turn a given attribute string into 
     * a conditional attribute.
     * 
     * @param string $attribute
     */
    public static function conditional(string $attribute)
    {
        return preg_replace('/\.\$\./', '.' . static::wrapIndex() . '.', preg_replace('/\.\*\./', '\.[0-9]+\.', $attribute));
    }

    /**
     * Wrap the index key.
     */
    public static function wrapIndex()
    {
        return self::WRAP_LEFT . self::INDEX . self::WRAP_RIGHT;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(
            parent::jsonSerialize(),
            [
                'singularLabel' => $this->singularLabel,
                'pluralLabel' => $this->pluralLabel,
                'indexKey' => static::wrapIndex(),
                'wrapLeft' => self::WRAP_LEFT,
                'wrapRight' => self::WRAP_RIGHT,
                'min' => $this->min,
                'max' => $this->max,
                'displayIf' => $this->displayIf
            ],
        );
    }
}
