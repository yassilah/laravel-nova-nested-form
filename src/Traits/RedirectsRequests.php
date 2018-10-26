<?php

namespace Yassi\NovaNestedForm\Traits;

use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Nova;
use Laravel\Nova\Http\Controllers\ResourceUpdateController;
use Laravel\Nova\Http\Requests\UpdateResourceRequest;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Http\Requests\CreateResourceRequest;
use Laravel\Nova\Http\Requests\DeleteResourceRequest;
use Laravel\Nova\Http\Controllers\ResourceDestroyController;
use Laravel\Nova\Http\Controllers\ResourceStoreController;

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
            if ($data['status'] === 'unchanged') {
                return $this->relationship->getClass()::fillForUpdate($request->replace($data), $this->relationship->getClass()::newModel()->forceFill($data));
            }
            return $this->controller($data)->handle($this->request($request, $data));
        } catch (ValidationException $e) {
            throw $this->throwValidationException($e, $data['prefix']);
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
        switch ($data['status']) {
            case 'updated':
                return UpdateResourceRequest::createFrom($request)->replace($data);
            case 'created':
                return CreateResourceRequest::createFrom($request)->replace($data);
            case 'removed':
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
        switch ($data['status']) {
            case 'updated':
                $controller = ResourceUpdateController::class;
                break;
            case 'created':
                $controller = ResourceStoreController::class;
                break;
            case 'removed':
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
    protected function query(Model $model, string $attribute, int $retrieved_at)
    {
        return [
            'viaResource' => Nova::resourceForModel(get_class($model))::uriKey(),
            'viaRelationship' => $this->relationship->getName(),
            'viaResourceId' => $model->id,
            'resource' => $attribute,
            '_retrieved_at' => $retrieved_at
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
        foreach ($data as &$value) {
            if (blank($value) || $value === 'null') {
                $value = null;
            }
        }

        return $data;
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
        return array_merge($this->data($data), $this->query($model, $attribute, $retrieved_at));
    }

    /**
     * This method throws the validation exceptions 
     * with the right attributes.
     * 
     * @return throwable
     */
    public function throwValidationException(ValidationException $e, string $prefix)
    {
        /**
         * TODO: find better way to only prefix one time.
         */
        if (!isset($e->validator->errors()->messages()['prefixed'])) {
            return $e->withMessages(collect($e->validator->errors()->messages())->mapWithKeys(function ($message, $attribute) use ($prefix) {
                return [$prefix . '[' . $attribute . ']' => $message];
            })->merge(['prefixed' => true])->toArray());
        }

        return $e;
    }
}