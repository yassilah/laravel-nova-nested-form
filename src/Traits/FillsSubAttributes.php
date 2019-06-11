<?php

namespace Yassi\NestedForm\Traits;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Laravel\Nova\Nova;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Yassi\NestedForm\Exceptions\NestedValidationException;
use Yassi\NestedForm\NestedForm;
use Yassi\NestedForm\Requests\CustomCreateResourceRequest;
use Yassi\NestedForm\Requests\CustomUpdateResourceRequest;
use Yassi\NestedForm\Requests\CustomDeleteResourceRequest;

trait FillsSubAttributes
{

    /**
     * List of children that have been
     * either created or updated during the
     * process.
     *
     * @var Collection
     */
    protected $touched = [];

    /**
     * Indicates whether all children
     * should be removed because none was present
     * in the request.
     *
     * @var bool
     */
    protected $shouldRemoveAll = false;

    /**
     * Fills the attributes of the model within the container if the dependencies for the container are satisfied.
     *
     * @param NovaRequest $request
     * @param string $requestAttribute
     * @param object $model
     * @param string $attribute
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($model->exists) {

            if (isset($this->beforeFillCallback)) {
                call_user_func($this->beforeFillCallback, $request, $model, $attribute, $requestAttribute);
                unset($this->beforeFillCallback);
            }

            $this->setRequest($request)->runNestedOperations($request, $attribute, $model)->removeUntouched($model);

            if (isset($this->afterFillCallback)) {
                call_user_func($this->afterFillCallback, $request, $model, $attribute, $requestAttribute, $this->touched);
                unset($this->afterFillCallback);
            }

            $this->removeCurrentAttribute($request, $attribute);
        } else {
            $model::saved(function ($model) use ($request, $requestAttribute, $attribute) {
                $this->fillAttributeFromRequest($request, $requestAttribute, $model, $attribute);
            });
        }
    }

    /**
     * This method retrurns the right request for the given data.
     *
     * @param array $data
     * @param Model $model
     * @param string $attribute
     * @param int|null $index
     * @return NovaRequest
     */
    protected function newRequest(array $data, Model $model, string $attribute, int $index = null)
    {
        if (isset($data[self::ID])) {
            $request = CustomUpdateResourceRequest::createFrom($this->request);
            $request->setCustomResource($this->resourceClass, $data[self::ID]);
        } else {
            $request = CustomCreateResourceRequest::createFrom($this->request);
            $request->setCustomResource($this->resourceClass);
        }

        $request->files = new FileBag($this->request->file($attribute)[$index ?? 0] ?? []);

        $request->query = new ParameterBag($this->query($data, $attribute, $index));

        $request->replace($this->data($data, $model));

        return $request;
    }

    /**
     * This method retrurns the right controller for the given data.
     *
     * @param array $data
     * @return Controller
     */
    protected function controller(array $data)
    {
        if (isset($data[self::ID])) {
            $controller = ResourceUpdateController::class;
        } else {
            $controller = ResourceStoreController::class;
        }

        return new $controller;
    }

    /**
     * This method returns the query parameters to send
     * in the fill attribute request.
     *
     * @param array $data
     * @param string $attribute
     * @param int|null $index
     * @return array
     */
    protected function query(array $data, string $attribute, int $index = null)
    {
        return [
            'resource' => $this->resourceName,
            'resourceId' => $data[self::ID] ?? null,
            'currentIndex' => $index,
            'parentIndex' => $this->request->currentIndex ?? null,
            self::ATTRIBUTE => $this->attribute($attribute, $index),
        ];
    }

    /**
     * This method returns the query parameters to send
     * in the fill attribute request.
     *
     * @param array $data
     * @param Model $model
     * @return array
     */
    protected function data(array $data, Model $model)
    {
        foreach ($data as $attribute => &$value) {
            if (blank($value) || $value === 'null' || $value === 'undefined') {
                $value = null;
            }
        }

        $data['_retrieved_at'] = $this->request->_retrieved_at;

        if ($this->inverseRelationship) {
            $data[$this->inverseRelationship] = $model->id;
        }

        if (isset($this->meta['morph_many']) || isset($this->meta['morph_one'])) {
            $relationship = $model->{$this->viaRelationship}();
            $key = head(explode('_type', $relationship->getMorphType()));
            $data[$relationship->getMorphType()] = $this->meta['viaResource'];
            $data[$key] = $model->{$relationship->getLocalKeyName()};
        }

        return $data;
    }

    /**
     * This method returns the new nested attribute to
     * be passed into the next request.
     *
     * @param string $attribute
     * @param int|null $index
     * @return string
     */
    protected function attribute(string $attribute, int $index = null)
    {
        $current = $this->request->{self::ATTRIBUTE};

        if ($current) {
            if (!preg_match("/\[?$attribute\]?(?:\[[0-9]*\])?$/", $current)) {
                $attribute = $current . '[' . $attribute . ']';
            } else {
                $attribute = preg_replace("/(?:\[[0-9]*\])?$/", '', $current);
            }
        }

        return $attribute . ($this->isManyRelationship() ? '[' . $index . ']' : '');
    }

    /**
     * This method loops through the request data
     * and runs the necessary controller with the right
     * request.
     *
     * @param string $attribute
     * @param Model $model
     * @return self
     */
    protected function runNestedOperations(NovaRequest $request, string $attribute, Model $model)
    {
        $data = $request->{$attribute} ?? null;

        if (is_object($data) || is_array($data)) {
            foreach ($data as $index => $value) {

                if (!is_int($index)) {
                    $value = $request->{$attribute};
                    $index = null;
                }

                $this->runNestedOperation($value, $model, $attribute, $index, $request);

                if ($value instanceof Response) {
                    abort($value->getStatusCode());
                }

                $this->touched[] = $value->getData()->id;

                if (is_null($index)) {
                    break;
                }
            }
        } else {
            $this->shouldRemoveAll = true;
        }

        return $this;
    }

    /**
     * This method runs the necessary controller with the right
     * request.
     *
     * @param array $value
     * @param Model $model
     * @param string $attribute
     * @param int|null $index
     * @return self
     */
    protected function runNestedOperation(array &$value, Model $model, string $attribute, int $index = null)
    {
        try {
            $this->setRequest($this->newRequest($value, $model, $attribute, $index));
            $value = $this->controller($value)->handle($this->request);
        } catch (Exception $exception) {
            if ($exception instanceof ValidationException) {
                throw new NestedValidationException($exception, $this->request->{self::ATTRIBUTE});
            }

            throw $exception;
        }

        return $this;
    }

    /**
     * This method removes the current attribute
     * to from the current request.
     *
     * @param NovaRequest $request
     * @param string $attribute
     * @return self
     */
    protected function removeCurrentAttribute(NovaRequest $request, string $attribute)
    {
        $request->request->remove($attribute);

        return $this;
    }

    /**
     * This method removes all the children that were not
     * sent in the request (and were therefore deleted on the
     * client).
     *
     * @param Model $model
     * @return self
     */
    protected function removeUntouched(Model $model)
    {

        if (count($this->touched) > 0 || $this->shouldRemoveAll) {

            if ($this->shouldRemoveAll) {
                $ids = $model->{$this->viaRelationship}()->pluck('id');
            } else {
                $ids = $model->{$this->viaRelationship}()->whereNotIn($this->viaRelationship . '.id', $this->touched)->pluck($this->viaRelationship . '.id');
            }

            $request = CustomDeleteResourceRequest::createFrom($this->request->replace(['resources' => $ids]));
            $request->setCustomResource($this->resourceClass);

            $request->query = new ParameterBag(['resource' => $this->resourceName]);

            $controller = new ResourceDestroyController;

            $controller->handle($request);
        }

        return $this;
    }
}
