<?php

namespace Buseta\TallerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CondicionesPagoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CondicionesPagoRepository extends EntityRepository
{
    public function searchByValor($valores) {
        $q = "SELECT r FROM BusetaTallerBundle:CondicionesPago r WHERE r.id = :valores";

        $query = $this->_em->createQuery($q);
        $query->setParameter('valores', $valores);

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return false;
        }
    }
}
