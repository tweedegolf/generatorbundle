<?php

namespace TweedeGolf\GeneratorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class BundleNamespaceValidator extends IsNamespaceValidator
{
    public function validate($value, Constraint $constraint)
    {
        parent::validate($value, $constraint);
        if (substr_count($value, '\\') < 1) {
            $this->context->addViolation($constraint->message, ['%string%' => $value], $value);
        } elseif (strlen($value) < 6 || substr($value, -6) !== 'Bundle') {
            $this->context->addViolation($constraint->message, ['%string%' => $value], $value);
        }
    }
}
