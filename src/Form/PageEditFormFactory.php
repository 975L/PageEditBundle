<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Form;

use c975L\PageEditBundle\Entity\PageEdit;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * PageEditFormFactory class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditFormFactory implements PageEditFormFactoryInterface
{
    public function __construct(
        /**
         * Stores FormFactoryInterface
         */
        private readonly FormFactoryInterface $formFactory
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $name, PageEdit $pageEdit)
    {
        switch ($name) {
            case 'create':
            case 'modify':
            case 'duplicate':
            case 'delete':
                $config = ['action' => $name];
                break;
            default:
                $config = [];
                break;
        }

        return $this->formFactory->create(PageEditType::class, $pageEdit, ['config' => $config]);
    }
}
