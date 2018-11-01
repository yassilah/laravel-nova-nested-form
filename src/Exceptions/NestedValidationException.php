<?php

namespace Yassi\NestedForm\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

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
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  string  $errorBag
     * @return void
     */
    public function __construct(ValidationException $exception, string $prefix)
    {
        $this->exception = $exception;
        $this->prefix = $prefix;
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
            return [$this->prefix . '[' . $attribute . ']' => $message];
        })->toArray();
    }

}
