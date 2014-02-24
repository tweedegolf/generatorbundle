<?php

namespace TweedeGolf\GeneratorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class BundleNameValidator extends ConstraintValidator
{
    /**
     * {@override}
     */
    public function validate($value, Constraint $constraint)
    {
        if (strlen($value) < 7 || substr($value, -6) !== 'Bundle') {
            $this->context->addViolation($constraint->message, ['%string%' => $value], $value);
        } elseif (1 !== preg_match('/[a-z][a-z0-9_]*/i', $value)) {
            $this->context->addViolation($constraint->message, ['%string%' => $value], $value);
        }
    }
}
