<?php

namespace Buseta\TallerBundle\Controller;

use Buseta\TallerBundle\Event\FilterDiagnosticoEvent;
use Buseta\TallerBundle\Event\FilterReporteEvent;
use Buseta\TallerBundle\Event\ReporteEvents;
use Buseta\TallerBundle\Event\DiagnosticoEvents;
use Buseta\TallerBundle\Entity\Diagnostico;
use Buseta\TallerBundle\Entity\TareaDiagnostico;
use Buseta\TallerBundle\Form\Type\ObservacionDiagnosticoType;
use Buseta\TallerBundle\Form\Type\TareaDiagnosticoType;
use Doctrine\ORM\AbstractQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Buseta\TallerBundle\Form\Type\DiagnosticoType;
use Buseta\TallerBundle\Form\Model\DiagnosticoFilterModel;
use Buseta\TallerBundle\Form\Filter\DiagnosticoFilter;
use Symfony\Component\HttpFoundation\Response;
use APY\BreadcrumbTrailBundle\Annotation\Breadcrumb;
/**
 * Diagnostico controller.
 *
 * @Route("/diagnostico")
 *
 * @Breadcrumb(title="Inicio", routeName="core_homepage")
 * @Breadcrumb(title="Módulo Estación de Servicios", routeName="taller_principal")
 *
 */
class DiagnosticoController extends Controller
{

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function procesarDiagnosticoAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $diagnostico = $em->getRepository('BusetaTallerBundle:Diagnostico')->find($id);


        if (!$diagnostico) {
            throw $this->createNotFoundException('Unable to find Diagnostico entity.');
        }

        //Se llama al EventDispatcher
        $eventDispatcher = $this->get('event_dispatcher');

        //Crear Eventos para el EventDispatcher
        $evento = new FilterDiagnosticoEvent($diagnostico);
        $evento->setDiagnostico($diagnostico);

        //Lanzo los Evento donde se crea el diagnostico y
        //cambio el estado de la solicitud de Abierto a Pendiente
        $eventDispatcher->dispatch(DiagnosticoEvents::PROCESAR_DIAGNOSTICO, $evento);
        $eventDispatcher->dispatch(DiagnosticoEvents::CAMBIAR_ESTADO_PR, $evento);
        //$eventDispatcher->dispatch(  OrdenTrabajoEvents::CAMBIAR_ESTADO_ABIERTO , $evento );

        return $this->redirect($this->generateUrl('diagnostico'));
    }

    public function cancelarDiagnosticoAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $diagnostico = $em->getRepository('BusetaTallerBundle:Diagnostico')->find($id);

        if (!$diagnostico) {
            throw $this->createNotFoundException('Unable to find Diagnostico entity.');
        }

        $report = $diagnostico->getReporte();

        //Se llama al EventDispatcher
        $eventDispatcher = $this->get('event_dispatcher');

        //Crear Eventos para el EventDispatcher


        $diagEvent = new FilterDiagnosticoEvent($diagnostico);
        $diagEvent->setDiagnostico($diagnostico);


        //Lanzo los Eventos donde se pasa la solicitud que esta en pendiente a completada
        // y el evento que pasa la orden de trabajo a completada

        if (!($report === null)) {
            $evento = new FilterReporteEvent($report);
            $evento->setReporte($report);
            $eventDispatcher->dispatch(ReporteEvents::CAMBIAR_ESTADO_CANCELADO, $evento);
        }

        $eventDispatcher->dispatch(DiagnosticoEvents::CAMBIAR_ESTADO_CO, $diagEvent);
        $eventDispatcher->dispatch(DiagnosticoEvents::CAMBIAR_CANCELADO, $diagEvent);

        return $this->redirect($this->generateUrl('diagnostico'));
    }

    /**
     * Lists all Diagnostico entities.
     *
     * @Route("/diagnostico", name="diagnostico")
     *
     * @Breadcrumb(title="Listado de Diagnósticos", routeName="diagnostico")
     */
    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $filter = new DiagnosticoFilterModel();
        $form = $this->createForm(new DiagnosticoFilter(), $filter, array(
            'action' => $this->generateUrl('diagnostico'),
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entities = $this->get('doctrine.orm.entity_manager')
                ->getRepository('BusetaTallerBundle:Diagnostico')->filter($filter);
        } else {
            $entities = $this->get('doctrine.orm.entity_manager')
                ->getRepository('BusetaTallerBundle:Diagnostico')->filter();
        }

        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $entities,
            $request->query->get('page', 1),
            10
        );

        $resumentotal = $em->getRepository('BusetaTallerBundle:Diagnostico')->findTotalAtrasadasFilter($entities);

        return $this->render('BusetaTallerBundle:Diagnostico:index.html.twig', array(
            'entities' => $entities,
            'filter_form' => $form->createView(),
            'resumentotal' => $resumentotal
        ));
    }

    /**
     * Finds and displays a Diagnostico entity.
     *
     * @Security("is_granted('SHOW', diagnostico)")
     *
     * @Route("/{id}/show", name="diagnostico_show")
     *
     * @Breadcrumb(title="Ver Datos de Diagnóstico", routeName="diagnostico_show", routeParameters={"id"})
     */
    public function showAction(Diagnostico $diagnostico)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        if ($diagnostico->getReporte()) {
            $reportes = $em->getRepository('BusetaTallerBundle:Reporte')->find($diagnostico->getReporte()->getId());
        } else {
            $reportes = null;
        }

        return $this->render('BusetaTallerBundle:Diagnostico:show.html.twig', array(
            'entity' => $diagnostico,
            'reportes' => $reportes,
            'id' => $diagnostico->getId(),
        ));
    }

    /**
     * Displays a form to edit an existing Diagnostico entity.
     *
     * @Security("is_granted('EDIT', diagnostico)")
     *
     * @Route("/{id}/edit", name="diagnostico_edit")
     *
     * @Breadcrumb(title="Modificar Diagnóstico", routeName="diagnostico_edit", routeParameters={"id"})
     */
    public function editAction(Diagnostico $diagnostico)
    {
        $editForm = $this->createEditForm($diagnostico);
        $deleteForm = $this->createDeleteForm($diagnostico->getId());

        return $this->render('BusetaTallerBundle:Diagnostico:edit.html.twig', array(
            'entity' => $diagnostico,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Creates a form to edit a Diagnostico entity.
     *
     * @param Diagnostico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Diagnostico $entity)
    {
        $form = $this->createForm(new DiagnosticoType(), $entity, array(
            'action' => $this->generateUrl('diagnostico_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        //$form->add('submit', 'submit', array('label' => 'Actualizar'));

        return $form;
    }

    /**
     * Creates a form to delete a Diagnostico entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('diagnostico_delete', array('id' => $id)))
            ->setMethod('DELETE')
            //->add('submit', 'submit', array('label' => 'Eliminar'))
            ->getForm();
    }

    /**
     * Deletes a TareaDiagnostico entity.
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BusetaTallerBundle:Diagnostico')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Diagnostico entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('diagnostico'));
    }

    /**
     * Edits an existing Usuario entity.
     *
     * @Route("/{id}/update", name="diagnostico_update")
     *
     * @Method({"POST", "PUT"})
     *
     * @Breadcrumb(title="Modificar Diagnóstico", routeName="diagnostico_update", routeParameters={"id"})
     */
    public function updateAction(Request $request, Diagnostico $diagnostico)
    {
        $em = $this->getDoctrine()->getManager();

        $deleteForm = $this->createDeleteForm($diagnostico->getId());

        $tareasD = array();

        foreach ($diagnostico->getTareaDiagnostico() as $tarea) {
            $tareasD[] = $tarea;
        }

        $editForm = $this->createEditForm($diagnostico);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            // filtra $originalTags para que contenga las etiquetas
            // que ya no están presentes
            foreach ($diagnostico->getTareaDiagnostico() as $tarea) {
                foreach ($tareasD as $key => $toDel) {
                    if ($toDel->getId() === $tarea->getId()) {
                        unset($tareasD[$key]);
                    }
                }
            }

            // Elimina la relación entre la etiqueta y la Tarea
            foreach ($tareasD as $tarea) {
                // Si deseas eliminar la etiqueta completamente, también lo puedes hacer
                $em->remove($tarea);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Se han editado los datos satisfactoriamente.');

            return $this->redirect($this->generateUrl('diagnostico_edit', array('id' => $diagnostico->getId())));
        }

        return $this->render('BusetaTallerBundle:Diagnostico:edit.html.twig', array(
            'entity' => $diagnostico,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Diagnostico entity.
     *
     * @Security("is_granted('CREATE', 'Buseta\\TallerBundle\\Entity\\Diagnostico')")
     *
     * @Route("/new", name="diagnostico_new")
     *
     * @Breadcrumb(title="Crear Nuevo Diagnóstico", routeName="diagnostico_new")
     */
    public function newAction()
    {
        $sequenceManager = $this->get('hatuey_soft.sequence.manager');
        $entity = new Diagnostico();

        if ($sequenceManager->hasSequence(ClassUtils::getRealClass($entity))) {
            $entity->setNumero($sequenceManager->getNextValue('diagnostico_seq'));
        }

        $observacion = $this->createForm(new ObservacionDiagnosticoType());
        $tareadiagno = $this->createForm(new TareaDiagnosticoType());

        $form = $this->createCreateForm($entity);

        return $this->render('BusetaTallerBundle:Diagnostico:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'observacion' => $observacion->createView(),
            'tareaDiagnostico' => $tareadiagno->createView(),
        ));
    }

    /**
     * Creates a form to create a Diagnostico entity.
     *
     * @param Diagnostico $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Diagnostico $entity)
    {
        $form = $this->createForm(new DiagnosticoType(), $entity, array(
            'action' => $this->generateUrl('diagnostico_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    /**
     * Creates a new Diagnostico entity.
     * @Route("/create", name="diagnostico_create")
     *
     * @Method("POST")
     *
     * @Security("is_granted('CREATE', 'Buseta\\TallerBundle\\Entity\\Diagnostico')")
     *
     * @Breadcrumb(title="Crear Nuevo Diagnóstico", routeName="diagnostico_create")
     */
    public function createAction(Request $request)
    {
        $entity = new Diagnostico();
        $tarea = new TareaDiagnostico();


        //Esto me agrega solo la primera tarea, hacer que me las agregue todas
        $tarea->setDiagnostico($entity);
        $entity->addTareaDiagnostico($tarea);


        $form = $this->createCreateForm($entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');

            try {
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()
                    ->add('success', 'Se ha creado el Diagnóstico de forma satisfactoria.');

                return $this->redirect($this->generateUrl('diagnostico_show', array('id' => $entity->getId())));
            } catch (\Exception $e) {
                $this->get('logger')
                    ->addCritical(sprintf('Ha ocurrido un error creando el Diagnóstico. Detalles: %s',
                        $e->getMessage()));

                $this->get('session')->getFlashBag()
                    ->add('danger', 'Ha ocurrido un error creando el Diagnóstico.');
            }
        }

        return $this->render('BusetaTallerBundle:Diagnostico:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Updated automatically select Autobus when change select Reporte
     *
     */
    public function select_reporte_autobusAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response('Acceso Denegado', 403);
        }

        if (!$request->isXmlHttpRequest()) {
            return new Response('No es una petición Ajax', 500);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $reportes = $em->find('BusetaTallerBundle:Reporte', $request->query->get('reporte_id'));

        $numero = $reportes->getNumero();
        $medio = $reportes->getMedioReporte()->getValor();
        //$bus = $reportes->getAutobus()->getMatricula().'-'.$reportes->getAutobus()->getNumero();

        if ($reportes->getReporta()) {
            $reporta = $reportes->getReporta();
        } else {
            $reporta = '-';
        }

        if ($reportes->getEsUsuario()) {
            $esUsuario = 'Sí';
            $nombrePersona = $reportes->getNombrePersona();
            $emailPersona = $reportes->getEmailPersona();
            $telefonoPersona = $reportes->getTelefonoPersona();
        } else {
            $esUsuario = 'No';
            $nombrePersona = '-';
            $emailPersona = '-';
            $telefonoPersona = '-';
        }

        return new JsonResponse(array(
            'autobus' => $reportes->getAutobus()->getId(),
            //'bus' => $bus,
            'reporta' => $reporta,
            'numero' => $numero,
            'medio' => $medio,
            'esUsuario' => $esUsuario,
            'nombrePersona' => $nombrePersona,
            'emailPersona' => $emailPersona,
            'telefonoPersona' => $telefonoPersona,
        ), 200);
    }

    /**
     * Updated automatically select Autobus y DiagnosticadoPor when change select Diagnostico
     *
     */
    public function select_diagnostico_ordentrabajoAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response('Acceso Denegado', 403);
        }

        if (!$request->isXmlHttpRequest()) {
            return new Response('No es una petición Ajax', 500);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $diagnostico_id = $request->query->get('diagnostico_id');

        $diagnostico = $em->getRepository('BusetaTallerBundle:Diagnostico')->findBy(array(
            'id' => $diagnostico_id,
        ));

        $json = array();
        foreach ($diagnostico as $diag) {
            $json[] = array(
                'id' => $diag->getAutobus()->getId(),
                'matricula' => $diag->getAutobus()->getMatricula(),
            );
        }

        return new \Symfony\Component\HttpFoundation\Response(json_encode($json), 200);
    }


}
