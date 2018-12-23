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
use Symfony\Component\Form\Form;

/**
 * Interface to be called for DI for PageEditFormFactoryInterface related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface PageEditFormFactoryInterface
{
    /**
     * Returns the defined form
     * @return Form
     */
    public function create(string $name, PageEdit $pageEdit);
}
