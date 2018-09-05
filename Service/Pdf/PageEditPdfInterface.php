<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service\Pdf;

/**
 * Interface to be called for DI for PageEdit Pdf related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface PageEditPdfInterface
{
    /**
     * Creates the pdf
     */
    public function create(string $filePath, string $page);
}
