<?php

namespace Yassi\NestedForm;

use Illuminate\Database\Eloquent\Model;

class NestedFormChild extends NestedFormSchema
{

    /**
     * Name of the fields' fitler method.
     * 
     * @var string
     */
    protected static $filterMethod = 'removeNonUpdateFields';
}
