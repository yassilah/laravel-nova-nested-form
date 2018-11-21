<?php

namespace Yassi\NestedForm\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Yassi\NestedForm\NestedForm;

class NestedValidationException extends Exception
{
    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 422;

    /**
     * Create a new exception instance.
     *
     * @param  ValidationException  $exception
     * @param  string|null  $attribute
     * @return void
     */
    public function __construct(ValidationException $exception, string $attribute = null)
    {
        $this->exception = $exception;
        $this->attribute = $attribute;
    }

    /**
     * Builds the exception content.
     *
     * @return array
     */
    public function render()
    {
        return response()->json([
            'messages' => 'The given data was invalid.',
            'errors' => $this->errors(),
        ], $this->status);
    }

    /**
     * Get all of the validation error messages and prepend the prefix to their attribute.
     *
     * @return array
     */
    public function errors()
    {
        return collect($this->exception->validator->errors()->messages())->mapWithKeys(function ($message, $attribute) {
            return [$this->attribute . '[' . $attribute . ']' => $message];
        })->toArray();
    }

}
