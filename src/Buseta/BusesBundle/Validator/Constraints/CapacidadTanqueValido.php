<?php

namespace Buseta\BusesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class CapacidadTanqueValido
 * @package Buseta\BusesBundle\Validator\Constraints
 * @Annotation
 */
class CapacidadTanqueValido extends Constraint{

    public $messageCantidadLibros = 'Se excede la capacidad del tanque de combustible con la cantidad de libros deseada.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'capacidadTanque_validador'; // TODO: Change the autogenerated stub
    }

}