<?php

namespace Buseta\TallerBundle\Manager;


use Buseta\TallerBundle\Entity\Reporte;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Monolog\Logger;

class ReporteManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;


    /**
     * @param ObjectManager $em
     * @param Logger $logger
     */
    function __construct(ObjectManager $em, Logger $logger)
    {
        $this->em               = $em;
        $this->logger           = $logger;
    }

    /**
     * Cambia es estado de un reporte
     *
     * @param Reporte $reporte
     * @param string $estado
     *
     * @return boolean
     */
    public function cambiarEstado( Reporte $reporte , $estado = 'BO' )
    {
        //Cambia el estado
        $reporte->setEstado( $estado);

        try {
            $this->em->persist($reporte);
            $this->em->flush();

            return true;
        } catch (\Exception $e) {
            $this->logger->critical(sprintf('Ha ocurrido un error al cambiar estado de Solicitud. Detalles: %s', $e->getMessage()));

            return false;
        }
    }
}
