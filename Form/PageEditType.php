<?php

namespace c975L\PageEditBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageEditType extends AbstractType
{

    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, array(
                'label' => false,
                'required' => false,
                'attr' => array(
                    'class' => 'tinymce',
                    'cols' => 100,
                    'rows' => 25,
                    'placeholder' => 'label.content',
                )))
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'c975L\PageEditBundle\Entity\PageEdit',
            'intention'  => 'pageEditForm',
        ));
    }


    public function getName()
    {
        return 'pageEdit';
    }

}
