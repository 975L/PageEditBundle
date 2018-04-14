<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageEditType extends AbstractType
{
    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = $options['pageEditConfig']['action'] == 'delete' ? true : false;
        $disabledSlug = $options['data']->getSlug() == 'home' ? true : $disabled;

        $builder
            ->add('title', TextType::class, array(
                'label' => 'label.title',
                'disabled' => $disabled,
                'required' => true,
                'attr' => array(
                    'placeholder' => 'label.title',
                )))
            ->add('slug', TextType::class, array(
                'label' => 'label.semantic_url_explanation',
                'disabled' => $disabledSlug,
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
                ->add('changeFrequency', ChoiceType::class, array(
                    'label' => 'label.change_frequency',
                    'disabled' => $disabled,
                    'required' => true,
                    'choices'  => array(
                        'label.never' => 'never',
                        'label.yearly' => 'yearly',
                        'label.monthly' => 'monthly',
                        'label.weekly' => 'weekly',
                        'label.daily' => 'daily',
                        'label.hourly' => 'hourly',
                        'label.always' => 'always',
                    )))
                ->add('priority', RangeType::class, array(
                    'label' => 'label.significance',
                    'disabled' => $disabled,
                    'required' => true,
                    'attr' => array(
                        'min' => 0,
                        'max' => 10
                    )))
                ->add('description', TextareaType::class, array(
                    'label' => 'label.description',
                    'disabled' => $disabled,
                    'required' => false,
                    'attr' => array(
                        'cols' => 100,
                        'rows' => 5,
                        'placeholder' => 'label.description',
                    )))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'c975L\PageEditBundle\Entity\PageEdit',
            'intention'  => 'pageEditForm',
            'translation_domain' => 'pageedit',
        ));

        $resolver->setRequired('pageEditConfig');
    }
}