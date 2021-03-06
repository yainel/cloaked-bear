<?php

namespace Buseta\BodegaBundle\Entity\Repository;

use Buseta\BodegaBundle\Form\Model\TerceroFilterModel;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * TerceroRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TerceroRepository extends EntityRepository
{
    public function filter(TerceroFilterModel $filter = null)
    {
        $qb = $this->createQueryBuilder('t');
        $query = $qb->where($qb->expr()->eq(true,true));

        if($filter) {
            if ($filter->getCodigo() !== null && $filter->getCodigo() !== '') {
                $query->andWhere($qb->expr()->like('t.codigo',':codigo'))
                    ->setParameter('codigo', '%' . $filter->getCodigo() . '%');
            }
            if ($filter->getNombres() !== null && $filter->getNombres() !== '') {
                $query->andWhere($query->expr()->eq('t.nombres', ':nombres'))
                    ->setParameter('nombres', $filter->getNombres());
            }
            if ($filter->getApellidos() !== null && $filter->getApellidos() !== '') {
                $query->andWhere($query->expr()->eq('t.apellidos', ':apellidos'))
                    ->setParameter('apellidos', $filter->getApellidos());
            }
            if ($filter->getAlias() !== null && $filter->getAlias() !== '') {
                $query->andWhere($query->expr()->eq('t.alias', ':alias'))
                    ->setParameter('alias', $filter->getAlias());
            }
//            if ($filter->getCliente() !== null && $filter->getCliente() !== '') {
//                $query->andWhere($query->expr()->eq('t.cliente', ':cliente'))
//                    ->setParameter('cliente', $filter->getCliente());
//            }
//            if ($filter->getInstitucion() !== null && $filter->getInstitucion() !== '') {
//                $query->andWhere($query->expr()->eq('t.institucion', ':institucion'))
//                    ->setParameter('institucion', $filter->getInstitucion());
//            }
//            if ($filter->getProveedor() !== null && $filter->getProveedor() !== '') {
//                $query->andWhere($query->expr()->eq('t.proveedor', ':proveedor'))
//                    ->setParameter('proveedor', $filter->getProveedor());
//            }
//            if ($filter->getPersona() !== null && $filter->getPersona() !== '') {
//                $query->andWhere($query->expr()->eq('t.persona', ':persona'))
//                    ->setParameter('persona', $filter->getPersona());
//            }
        }

        $query->orderBy('t.id', 'ASC');

        try {
            return $query->getQuery();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function searchByValor($valores) {
        $q = "SELECT r FROM BusetaBodegaBundle:Tercero r WHERE r.id = :valores";

        $query = $this->_em->createQuery($q);
        $query->setParameter('valores', $valores);

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return false;
        }
    }
}
