<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service\File;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use c975L\PageEditBundle\Entity\PageEdit;

/**
 * Interface to be called for DI for PageEdit File related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface PageEditFileInterface
{
    /**
     * Archives file
     */
    public function archive(string $page, object $user);

    /**
     * Creates the folders needed by PageEditBundle
     */
    public function createFolders();

    /**
     * Moves the page to the deleted folder
     */
    public function delete(string $page, bool $archive);

    /**
     * Returns the images folder
     * @return string
     */
    public function getImagesFolder();

    /**
     * Gets all available pages
     * @return Finder
     */
    public function getPages();

    /**
     * Returns the pages folder
     * @return string
     */
    public function getPagesFolder();

    /**
     * Returns file path
     * @return string
     */
    public function getPath(string $page);

    /**
     * Gets the start and end of the skeleton
     * @return array
     */
    public function getSkeletonStartEnd();

    /**
     * Creates the redirection file
     */
    public function redirect(string $page, string $slug);

    /**
     * Writes file (+ archive old file)
     */
    public function write(string $slug, Form $form, object $user);
}
