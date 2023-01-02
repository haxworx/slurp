<?php

// src/Validator/IsUserAgent.php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsUserAgent extends Constraint
{
    public $message = 'The string "{{ string }}" is not a valid user agent.';
}
