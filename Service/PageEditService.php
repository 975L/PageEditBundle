<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Cocur\Slugify\Slugify;

class PageEditService
{
    private $container;
    private $parser;
    private $locator;

    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser $parser, \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator $locator)
    {
        $this->container = $container;
        $this->parser = $parser;
        $this->locator = $locator;
    }

    //Creates the folders needed by the Bundle
    public function createFolders()
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines paths
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages');
        $archivedFolder = $folderPath . '/archived';
        $deletedFolder = $folderPath . '/deleted';
        $protectedFolderPath = $folderPath . '/protected';
        $redirectedFolder = $folderPath . '/redirected';
        $imageFolderPath = $this->container->getParameter('kernel.root_dir') . '/../web/images/' . $this->container->getParameter('c975_l_page_edit.folderPages');

        //Creates folders
        $fs->mkdir($archivedFolder, 0770);
        $fs->mkdir($deletedFolder, 0770);
        $fs->mkdir($protectedFolderPath, 0770);
        $fs->mkdir($redirectedFolder, 0770);
        $fs->mkdir($imageFolderPath, 0770);
    }

    //Gets the start and end of the skeleton
    public function getSkeleton()
    {
        $skeleton = file_get_contents($this->locator->locate($this->parser->parse('c975LPageEditBundle::skeleton.html.twig')));

        $startBlock = '{% block pageEdit %}';
        $endBlock = '{% endblock %}';

        $entryPoint = strpos($skeleton, $startBlock) + strlen($startBlock);
        $exitPoint = strpos($skeleton, $endBlock, $entryPoint);

        return array(
            'startSkeleton' => trim(substr($skeleton, 0, $entryPoint)),
            'endSkeleton' => trim(substr($skeleton, $exitPoint))
        );
    }

    //Slugify function - https://github.com/cocur/slugify
    public function slugify($text)
    {
        $slugify = new Slugify();
        return $slugify->slugify($text);
    }

    //Archives file
    public function archiveFile($page, $userId)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines paths
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages');
        $archivedFolder = $folderPath . '/archived';
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Archives file
        if ($fs->exists($filePath)) {
            $fs->rename($filePath, $archivedFolder . '/' . $page . '-' . date('Ymd-His-') . $userId . '.html.twig');
        }
    }

    //Creates the redirection file
    public function redirectFile($page, $slug)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages');
        $redirectedFolder = $folderPath . '/redirected';

        //Sets the redirection
        $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
        $fs->dumpFile($redirectedFilePath, $slug);
    }

    //Moves to deleted/redirected folder the requested file
    public function deleteFile($page, $redirect, $slug = null)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';
        $deletedFolder = $folderPath . '/deleted';
        $redirectedFolder = $folderPath . '/redirected';

        //Sets the redirection
        if ($redirect === true) {
            $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
            $fs->dumpFile($redirectedFilePath, $slug);
        }

        //Deletes file
        if ($fs->exists($filePath)) {
            $fs->rename($filePath, $deletedFolder . '/' . $page . '.html.twig');
        }
    }

    //Archives old file and writes new one
    public function writeFile($page, $originalContent, $formData, $userId)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Gets the skeleton
        extract($this->getSkeleton());

        //Gets title
        $title = $formData->getTitle();

        //Title is using Twig code to translate it
        if (strpos($title, '{{') === 0) {
            $title = trim(str_replace(array('{{', '}}'), '', $title));
        }
        //Title is text
        else {
            $title = '"' . str_replace('"', '\"', $formData->getTitle()) . '"';
        }

        //Updates metadata
        $startSkeleton = preg_replace('/pageedit_title=\"(.*)\"/', 'pageedit_title=' . $title, $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_changeFrequency=\"(.*)\"/', 'pageedit_changeFrequency="' . $formData->getChangeFrequency() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_priority=\"(.*)\"/', 'pageedit_priority="' . $formData->getPriority() . '"', $startSkeleton);

        //Concatenate skeleton + metadata + content
        $finalContent = $startSkeleton . "\n" . $formData->getContent() . "\n\t\t" . $endSkeleton;

        //Archives old file if content or metadata are different
        if ($fs->exists($filePath) && file_get_contents($filePath) !== $finalContent) {
            $this->archiveFile($page, $userId);
        }

        //Writes new file
        $newFilePath = $folderPath . '/' . $page . '.html.twig';
        $fs->dumpFile($newFilePath, $finalContent);
        $fs->chmod($newFilePath, 0770);

        //Clears the cache otherwise changes will not be reflected
        $fs->remove($this->container->getParameter('kernel.cache_dir') . '/../prod/twig');
    }
}