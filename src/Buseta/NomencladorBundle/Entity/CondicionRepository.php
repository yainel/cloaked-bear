<?php

namespace Buseta\NomencladorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * CondicionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CondicionRepository extends EntityRepository
{
    public function searchByValor($valores) {
        $q = "SELECT r FROM BusetaNomencladorBundle:Condicion r WHERE r.id = :valores";

        $query = $this->_em->createQuery($q);
        $query->setParameter('valores', $valores);

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return false;
        }
    }
}
