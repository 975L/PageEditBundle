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

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser $parser,
        \Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator $locator
        )
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

    //Gets the change frequency of the page
    public function getChangeFrequency($fileContent)
    {
        $changeFrequency = 'monthly';

        preg_match('/pageedit_changeFrequency=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $changeFrequency = $matches[1];
        }

        return $changeFrequency;
    }

    //Gets the description of the page
    public function getDescription($fileContent)
    {
        $description = '';

        preg_match('/pageedit_description=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $description = $matches[1];
        }

        return $description;
    }

    //Gets the priority of the page
    public function getPriority($fileContent)
    {
        $priority = '5';

        preg_match('/pageedit_priority=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $priority = $matches[1];
        }

        return $priority;
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

    //Gets the title of the page
    public function getTitle($fileContent, $slug)
    {
        $title = $this->container->get('translator')->trans('label.title_not_found', array(), 'pageedit') . ' (' . $slug . ')';

        preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $title = str_replace('\"', '"', $matches[1]);
        } else {
            //Title is using Twig code to translate it
            preg_match('/pageedit_title=(.*)\%\}/', $fileContent, $matches);
            if (!empty($matches)) {
                $title = trim($matches[1]);
            }
        }

        return $title;
    }

    //Gets the translation of title of the page
    public function getTitleTranslated($title)
    {
        $titleTranslated = $title;

        if (strpos($title, '|trans') !== false) {
            $translateLabel = trim(substr($title, 0, strpos($title, '|trans')), "'");
            $translateDomain = 'messages';
            if (strpos($title, '|trans(') !== false) {
                $translateDomain = trim(trim(substr($title, strpos($title, '}') + 2)), "'");
                $translateDomain = substr($translateDomain, 0, strlen($translateDomain) - 2);
            }
            $titleTranslated = $this->container->get('translator')->trans($translateLabel, array(), $translateDomain);
        }

        return $titleTranslated;
    }

    //Slugify function - https://github.com/cocur/slugify
    public function slugify($text, $keepSlashes = false)
    {
        $slugify = new Slugify();
        if ($keepSlashes === true) {
            $slugify->addRule('/', '-thereisaslash-');
        }
        $slugifyText = $slugify->slugify($text);

        return str_replace('-thereisaslash-', '/', $slugifyText);
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
            //Create sub-folders
            if (strpos($page, '/') !== false) {
                $subfolder = substr($page, 0, strrpos($page, '/'));
                $fs->mkdir($archivedFolder . '/' . $subfolder, 0770);
            }
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
    public function deleteFile($page, $archive)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';
        $deletedFolder = $folderPath . '/deleted';

        //Deletes file
        if ($fs->exists($filePath)) {
            if ($archive === true) {
                //Create sub-folders
                if (strpos($page, '/') !== false) {
                    $subfolder = substr($page, 0, strrpos($page, '/'));
                    $fs->mkdir($deletedFolder . '/' . $subfolder, 0770);
                }
                $fs->rename($filePath, $deletedFolder . '/' . $page . '.html.twig');
            } else {
                $fs->remove($filePath);
            }
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
        //Title is text
        } elseif (strpos($title, '|trans') === false) {
            $title = '"' . str_replace('"', '\"', $formData->getTitle()) . '"';
        }

        //Updates metadata
        $startSkeleton = preg_replace('/pageedit_title=\"(.*)\"/', 'pageedit_title=' . $title, $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_changeFrequency=\"(.*)\"/', 'pageedit_changeFrequency="' . $formData->getChangeFrequency() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_priority=\"(.*)\"/', 'pageedit_priority="' . $formData->getPriority() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_description=\"(.*)\"/', 'pageedit_description="' . $formData->getDescription() . '"', $startSkeleton);

        //Cleans content
        $content = str_replace('{{path', '{{ path', $formData->getContent());
        $content = preg_replace('#href=\"(.*){{ path#', 'href="{{path', $content);

        //Concatenate skeleton + metadata + content
        $finalContent = $startSkeleton . "\n" . $content . "\n\t\t" . $endSkeleton;

        //Archives old file if content or metadata are different
        if ($fs->exists($filePath) && file_get_contents($filePath) !== $finalContent) {
            $this->archiveFile($page, $userId);
        }

        //Create sub-folders
        if (strpos($page, '/') !== false) {
            $subfolder = substr($page, 0, strrpos($page, '/'));
            $fs->mkdir($folderPath . '/' . $subfolder, 0770);
        }

        //Writes new file
        $newFilePath = $folderPath . '/' . $page . '.html.twig';
        $fs->dumpFile($newFilePath, $finalContent);
        $fs->chmod($newFilePath, 0770);

        //Clears the cache otherwise changes will not be reflected
        $fs->remove($this->container->getParameter('kernel.cache_dir') . '/../prod/twig');
    }
}