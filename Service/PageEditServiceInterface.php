<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service;

use c975L\PageEditBundle\Entity\PageEdit;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;

/**
 * Interface to be called for DI for PageEdit Main related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface PageEditServiceInterface
{
    /**
     * Clones the object
     * @return PageEdit
     */
    public function cloneObject(PageEdit $pageEdit);

    /**
     * Shortcut to call EventFormFactory to create Form
     * @return Form
     */
    public function createForm(string $name, PageEdit $pageEdit);

    /**
     * Defines slug and title for a set of pages
     * @return array
     */
    public function definePagesSlugTitle(Finder $finder);

    /**
     * Defines the toolbar
     * @return string
     */
    public function defineToolbar(string $kind, string $page);

    /**
     * Shortcut for $pageEditFile->delete()
     */
    public function delete(string $page, bool $archive);

    /**
     * Gets the change frequency of the page
     * @returns string
     */
    public function getChangeFrequency(string $fileContent);

    /**
     * Get content of the page
     * @return string
     */
    public function getContent(string $fileContent);

    /**
     * Returns a PageEdit object
     * @return PageEdit|false
     */
    public function getData(string $page);

    /**
     * Gets the description of the page
     * @return string|null
     */
    public function getDescription(string $fileContent);

    /**
     * Gets links to display in the select in Tinymce
     * @return array
     */
    public function getLinks();

    /**
     * Returns an array containing all the pages
     * @return array
     */
    public function getPages();

    /**
     * Gets the priority of the page
     * @return int/null
     */
    public function getPriority(string $fileContent);

    /**
     * Gets the title of the page
     * @return string
     */
    public function getTitle(string $fileContent, string $slug);

    /**
     * Gets the translation of title of the page
     * @return string
     */
    public function getTitleTranslated(string $title);

    /**
     * Registers the page and returns the slug
     * @return string
     */
    public function register(string $page, Form $form, $user);
}
