<?php

namespace TweedeGolf\GeneratorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsNamespaceValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (1 !== preg_match('/^[a-z][a-z0-9_]*(\\\\[a-z][a-z0-9_]*)*$/i', $value)) {
            $this->context->addViolation($constraint->message, ['%string%' => $value], $value);
        }
    }
}
