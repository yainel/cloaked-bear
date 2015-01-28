<?php

namespace Buseta\TallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Buseta\TallerBundle\Form\EventListener\AddGrupoFieldSubscriber;
use Buseta\TallerBundle\Form\EventListener\AddSubgrupoFieldSubscriber;

class TareaMantenimientoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $objeto = $builder->getFormFactory();
        $subgrupoSubscriber = new AddSubgrupoFieldSubscriber($objeto);
        $builder->addEventSubscriber($subgrupoSubscriber);
        $grupoSubscriber = new AddGrupoFieldSubscriber($objeto);
        $builder->addEventSubscriber($grupoSubscriber);

        $builder
            ->add('autobus','entity',array(
                'class' => 'BusetaBusesBundle:Autobus',
                'empty_value' => '---Seleccione un autobús---',
                'required' => true,
                'attr' => array(
                    'class' => 'form-control',
                )
            ))
            ->add('tarea', 'text', array(
                'required' => false,
                'label'  => 'Tarea',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('kilometraje', 'number', array(
                'required' => false,
                'label'  => 'Kilometraje',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('horas', 'number', array(
                'required' => false,
                'label'  => 'Horas',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('recorrido_inicio', 'date', array(
                'required' => false,
                'label'  => 'Recorrido Inició',
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
            ->add('ultimo_cumplio', 'date', array(
                'required' => false,
                'label'  => 'Ultimo cumplió',
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr'   => array(
                    'class' => 'form-control',
                )
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Buseta\TallerBundle\Entity\TareaMantenimiento',
            'action' => 'POST'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'buseta_tallerbundle_tareamantenimiento';
    }


}
