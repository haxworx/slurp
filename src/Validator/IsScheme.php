<?php

// src/Validator/IsScheme.php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class IsScheme extends Constraint
{
    public $message = 'Invalid scheme (http/https supported).';
}
