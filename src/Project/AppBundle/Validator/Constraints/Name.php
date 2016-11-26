<?php

namespace Project\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Name extends Constraint
{
    public $message = 'Field "%string%" contains illegal characters or is not a valid combination.';
}