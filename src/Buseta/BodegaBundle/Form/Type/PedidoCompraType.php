<?php

namespace Buseta\BodegaBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Buseta\BodegaBundle\Form\Type\PedidoCompraLineaType;

class PedidoCompraType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var Container
     */
    private $serviceContainer;

    function __construct(ObjectManager $em, Container $serviceContainer)
    {
        $this->em = $em;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA,array($this,'preSetData'));
        $builder
            ->add('numero_documento', 'text', array(
                    'required' => true,
                    'label'  => 'Nro.Documento',
                    'attr'   => array(
                        'class' => 'form-control',
                    )
                ))//
            ->add('tercero','entity',array(
                'class' => 'BusetaBodegaBundle:Tercero',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('t')
                        ->where('t.proveedor = true');
                },
                'empty_value' => '---Seleccione un proveedor---',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                )
            ))
            ->add('fecha_pedido','date',array(
                'widget' => 'single_text',
                'format'  => 'dd/MM/yyyy',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('almacen','entity',array(
                'class' => 'BusetaBodegaBundle:Bodega',
                'label' => 'Almacén',
                'empty_value' => '---Seleccione almacén---',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                )
            ))
            ->add('moneda','entity',array(
                'class' => 'BusetaNomencladorBundle:Moneda',
                'empty_value' => '---Seleccione tipo de moneda---',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                )
            ))
            ->add('forma_pago','entity',array(
                'class' => 'BusetaNomencladorBundle:FormaPago',
                'label' => 'Forma de Pago',
                'empty_value' => '---Seleccione forma de pago---',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                )
            ))
            ->add('condiciones_pago','entity',array(
                'class' => 'BusetaTallerBundle:CondicionesPago',
                'empty_value' => '---Seleccione condiciones de pago---',
                'required' => false,
                'attr' => array(
                    'class' => 'form-control',
                )
            ))
            ->add('estado_documento', 'choice', array(
                'required' => true,
                'empty_value' => '---Seleccione estado documento---',
                'translation_domain'=> 'BusetaTallerBundle',
                'choices' => array(
                    'CO' => 'estado.CO',
                    'BO' => 'estado.BO',
                    'PR' => 'estado.PR',
                ),
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('importe_total_lineas', 'text', array(
                'required' => true,
                'label'  => 'Importe total líneas',
                'read_only' => true,
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('importe_total', 'text', array(
                'required' => true,
                'read_only' => true,
                'label'  => 'Importe total',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('pedido_compra_lineas','collection',array(
                'type' => new PedidoCompraLineaType(),
                'label'  => false,
                'required' => false,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Buseta\BodegaBundle\Entity\PedidoCompra'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bodega_pedido_compra';
    }

    public function preSetData(FormEvent $formEvent)
    {
        $form = $formEvent->getForm();

        //Compruebo que existe el consecutivo automatico de Compra
        //Si no existe captu$consecutivoCompraro la configuracion predeterminada,
        //Si existe obtengo el maximo valor de consecutivo de compra y le incremento en 1
        $results = $this->em->getRepository('BusetaBodegaBundle:PedidoCompra')->consecutivoLast();

        $consecutivoCompra = $results ?
            $results['consecutivo_compra'] + 1 :
            $this->serviceContainer->getParameter('consecutivoCompra');

        $form->add('consecutivo_compra', 'text', array(
            'required' => true,
            'read_only' => true,
            'label'  => 'Consecutivo automático',
            'attr'   => array(
                'class' => 'form-control',
            ),
            'data' => $consecutivoCompra,
        ));
    }
}