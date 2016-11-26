<?php

namespace Project\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!preg_match('/^[A-Za-z]\'?[- a-zA-Z]+$/', $value, $matches)) {
            $this->context->addViolation(
                $constraint->message,
                array(
                    '%string%' => $value
                )
            );
        }
    }
}