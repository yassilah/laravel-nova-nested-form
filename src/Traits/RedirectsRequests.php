<?php

namespace Yassi\NestedForm\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Laravel\Nova\Nova;
use Yassi\NestedForm\Exceptions\NestedValidationException;

trait RedirectsRequests
{

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
        $data = $request->all();

        foreach ($data as $key => $value) {
            if (str_contains($key, self::ATTRIBUTE_PREFIX)) {
                $data[str_replace(self::ATTRIBUTE_PREFIX, '', $key)] = $value;
                unset($data[$key]);
            }
        }

        if (isset($data[$attribute])) {
            foreach ($data[$attribute] as $data) {
                $this->run($request, $this->newRequestData($data, $model, $attribute, $request->_retrieved_at));
            }
        }

        $request->request->remove($attribute);
    }

    /**
     * This method runs the handle method of the selected controller.
     *
     * @return
     */
    protected function run(NovaRequest $request, array $data)
    {
        try {
            if ($data[self::STATUS] === self::UNCHANGED) {
                return $this->relatedResource::fillForUpdate($request->replace($data), $this->relatedResource::newModel()->forceFill($data));
            }
            $this->controller($data)->handle($this->request($request, $data));
        } catch (ValidationException $e) {
            throw new NestedValidationException($e, $data[self::PREFIX]);
        }
    }

    /**
     * This method retrurns the right request for the given data.
     *
     * @param NovaRequest $request
     * @param array $data
     * @return NovaRequest
     */
    protected function request(NovaRequest $request, array $data)
    {
        switch ($data[self::STATUS]) {
            case self::UPDATED:
                return UpdateResourceRequest::createFrom($request)->replace($data);
            case self::CREATED:
                return CreateResourceRequest::createFrom($request)->replace($data);
            case self::REMOVED:
                return DeleteResourceRequest::createFrom($request)->replace(array_merge($data, ['resources' => [['id' => $data['id']]]]));
        }
    }

    /**
     * This method retrurns the right controller for the given data.
     *
     * @param array $data
     * @return Controller
     */
    protected function controller(array $data)
    {
        switch ($data[self::STATUS]) {
            case self::UPDATED:
                $controller = ResourceUpdateController::class;
                break;
            case self::CREATED:
                $controller = ResourceStoreController::class;
                break;
            case self::REMOVED:
                $controller = ResourceDestroyController::class;
                break;
        }

        return new $controller;
    }

    /**
     * This method returns the query parameters to send
     * in the fill attribute request.
     *
     * @param Model $model
     * @param string $attribute
     * @param int $retrieved_at
     * @return array
     */
    protected function query(Model $model, string $attribute, array $data, int $retrieved_at)
    {
        return [
            'viaResource' => Nova::resourceForModel(get_class($model))::uriKey(),
            'viaRelationship' => $this->viaRelationship,
            'viaResourceId' => $model->id,
            'resource' => $this->relatedResource::uriKey(),
            'resourceId' => $data['id'] ?? null,
            '_retrieved_at' => $retrieved_at,
        ];
    }

    /**
     * This method returns the query parameters to send
     * in the fill attribute request.
     *
     * @param Model $model
     * @param string $attribute
     * @param int $retrieved_at
     * @return array
     */
    protected function data(array $data)
    {
        $newData = [];

        foreach ($data as $attribute => $value) {

            if (blank($value) || $value === 'null') {
                $value = null;
            }

            $newData[$attribute] = $value;
        }

        return $newData;
    }

    /**
     * This method returns the query parameters to send
     * in the fill attribute request.
     *
     * @param Model $model
     * @param string $attribute
     * @param int $retrieved_at
     * @return array
     */
    protected function newRequestData(array $data, Model $model, string $attribute, int $retrieved_at)
    {
        return array_merge($this->data($data), $this->query($model, $attribute, $data, $retrieved_at));
    }
}
