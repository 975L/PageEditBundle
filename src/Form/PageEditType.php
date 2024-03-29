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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * PageEdit FormType
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $options['config']['action'] == 'delete' ? true : false;
        $disabledSlug = $options['data']->getSlug() == 'home' ? true : $disabled;

        $builder
            ->add('title', TextType::class, ['label' => 'label.title', 'disabled' => $disabled, 'required' => true, 'attr' => ['placeholder' => 'label.title']])
            ->add('slug', TextType::class, ['label' => 'label.semantic_url_explanation', 'disabled' => $disabledSlug, 'required' => true, 'attr' => ['placeholder' => 'text.semantic_url']])
        ;
        if ($disabled === false) {
            $builder
                ->add('content', TextareaType::class, ['label' => 'label.content', 'disabled' => $disabled, 'required' => true, 'attr' => ['class' => 'tinymce', 'cols' => 100, 'rows' => 25, 'placeholder' => 'label.content']])
                ->add('changeFrequency', ChoiceType::class, ['label' => 'label.change_frequency', 'disabled' => $disabled, 'required' => true, 'choices'  => ['label.never' => 'never', 'label.yearly' => 'yearly', 'label.monthly' => 'monthly', 'label.weekly' => 'weekly', 'label.daily' => 'daily', 'label.hourly' => 'hourly', 'label.always' => 'always']])
                ->add('priority', RangeType::class, ['label' => 'label.significance', 'disabled' => $disabled, 'required' => true, 'attr' => ['min' => 0, 'max' => 10]])
                ->add('description', TextareaType::class, ['label' => 'label.description', 'disabled' => $disabled, 'required' => false, 'attr' => ['cols' => 100, 'rows' => 5, 'placeholder' => 'label.description']])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => \c975L\PageEditBundle\Entity\PageEdit::class, 'intention'  => 'pageEditForm', 'translation_domain' => 'pageedit']);

        $resolver->setRequired('config');
    }
}
