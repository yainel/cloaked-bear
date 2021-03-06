<?php

namespace Buseta\CombustibleBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Marchamo1Valido
 *
 * @package Buseta\CombustibleBundle\Validator\Constraints
 * @Annotation
 */
class Marchamo1Valido extends Constraint
{
    public $message = 'El Marchamo no coincide con el anterior Servicio.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'combustible_marchamo1valido_validador';
    }
}
