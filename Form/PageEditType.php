<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageEditType extends AbstractType
{

    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = $options['data']->getAction() == 'delete' ? true : false;

        $builder
            ->add('title', TextType::class, array(
                'label' => 'label.title',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'label.title',
                )))
            ->add('slug', TextType::class, array(
                'label' => 'label.semantic_url',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'text.semantic_url',
                )))
        ;
        if ($disabled === false) {
            $builder
                ->add('content', TextareaType::class, array(
                    'label' => 'label.content',
                    'disabled' => $disabled,
                    'required' => true,
                    'attr' => array(
                        'class' => 'tinymce',
                        'cols' => 100,
                        'rows' => 25,
                        'placeholder' => 'label.content',
                    )))
            ;
        }
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
