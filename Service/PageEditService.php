<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Cocur\Slugify\Slugify;

class PageEditService
{
    private $authChecker;
    private $container;
    private $knpSnappyPdf;
    private $request;
    private $templating;

    public function __construct(
        \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authChecker,
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Knp\Snappy\Pdf $knpSnappyPdf,
        \Symfony\Component\HttpFoundation\RequestStack $requestStack,
        \Twig_Environment $templating
        )
    {
        $this->authChecker = $authChecker;
        $this->container = $container;
        $this->knpSnappyPdf = $knpSnappyPdf;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
    }

    //Archives file
    public function archiveFile($page, $userId)
    {
        //Defines paths
        $folderPath = $this->getPagesFolder();
        $archivedFolder = $folderPath . 'archived';
        $filePath = $folderPath . $page . '.html.twig';

        //Archives file
        $fs = new Filesystem();
        if ($fs->exists($filePath)) {
            //Create sub-folders
            if (false !== strpos($page, '/')) {
                $subfolder = substr($page, 0, strrpos($page, '/'));
                $fs->mkdir($archivedFolder . '/' . $subfolder, 0770);
            }
            $fs->rename($filePath, $archivedFolder . '/' . $page . '-' . date('Ymd-His') . '-' . $userId . '.html.twig');
        }
    }

    //Creates the folders needed by the Bundle
    public function createFolders()
    {
        //Defines folders
        $folderPath = $this->getPagesFolder();
        $folders = array (
            $folderPath . 'pdf',
            $folderPath . 'archived',
            $folderPath . 'deleted',
            $folderPath . 'protected',
            $folderPath . 'redirected',
            $this->getImagesFolder(),
        );

        //Creates folders
        $fs = new Filesystem();
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                $fs->mkdir($folder, 0770);
            }
        }
    }

    //Creates the pdf
    public function createPdf($filePath, $page)
    {
        $filePdfPath = $this->getPagesFolder() . 'pdf/' . $page . '-' . $this->request->getLocale() . '.pdf';
        $amountTime = 60 * 60 * 24;//24 hours

        //Checks if pdf is not existing, not up-to-date or has exceeded an amount of time
        if (!is_file($filePdfPath) ||
            filemtime($filePdfPath) < filemtime($filePath) ||
            filemtime($filePdfPath) + $amountTime < time()) {

            $html = $this->templating->render($filePath, array(
                'toolbar' => '',
                'display' => 'pdf',
            ));
            $this->createFolders();
            file_put_contents($filePdfPath, $this->knpSnappyPdf->getOutputFromHtml(str_replace('https:', 'http:', $html)));
        }

        return $filePdfPath;
    }

    //Defines slug and title for a set of pages
    public function definePagesSlugTitle($finder, $view)
    {
        $pages = array();
        foreach ($finder as $file) {
            $slug = str_replace('.html.twig', '', $file->getRelativePathname());
            $title = $this->getTitle($file->getContents(), $slug);
            $titleTranslated = $this->getTitleTranslated($title);

            //Defines status of page
            if (false !== strpos($file->getPath(), 'protected')) {
                $status = 'protected';
            } elseif ($view == '') {
                $status = 'current';
            } else {
                $status = $view;
            }

            //Adds page to array
            $pages[] = array(
                'slug' => $slug,
                'title' => $titleTranslated,
                'status' => $status,
            );
        }

        return $pages;
    }

    //Defines toolbar
    public function defineToolbar($kind, $page)
    {
        $toolbar = '';
        if ($this->authChecker->isGranted($this->container->getParameter('c975_l_page_edit.roleNeeded'))) {
            $tools = $this->templating->render('@c975LPageEdit/tools.html.twig', array(
                'type' => $kind,
                'object' => $page,
            ));
            $toolbar = $this->templating->render('@c975LToolbar/toolbar.html.twig', array(
                'tools' => $tools,
                'size' => 'md',
            ));
        }

        return $toolbar;
    }

    //Moves to deleted/redirected folder the requested file
    public function deleteFile($page, $archive)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getPagesFolder();
        $filePath = $folderPath . $page . '.html.twig';
        $deletedFolder = $folderPath . 'deleted';

        //Deletes file
        if ($fs->exists($filePath)) {
            if ($archive) {
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

    //Gets all data relative to page
    public function getData($filePath)
    {
        $fs = new Filesystem();
        if ($fs->exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $title = $this->getTitle($fileContent, str_replace( array($this->getPagesFolder(), '.html.twig'), '', $filePath));

            return array(
                'originalContent' => $this->getOriginalContent($fileContent),
                'title' => $title,
                'titleTranslated' => $this->getTitleTranslated($title),
                'changeFrequency' => $this->getChangeFrequency($fileContent),
                'priority' => $this->getPriority($fileContent),
                'description' => $this->getDescription($fileContent),
            );
        }

        return null;
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

    //Returns file path
    public function getFilePath($page)
    {
        $page = rtrim($page, '/') . '.html.twig';
        $folderPath = $this->getPagesFolder();

        //Normal
        if (is_file($folderPath . $page)) {
            return $folderPath . $page;
        //Protected
        } elseif (is_file($folderPath . 'protected/' . $page)) {
            return $folderPath . 'protected/' . $page;
        //Archived
        } elseif (is_file($folderPath . 'archived/' . $page)) {
            return $folderPath . 'archived/' . $page;
        //Redirected
        } elseif (is_file($folderPath . 'redirected/' . $page)) {
            return $folderPath . 'redirected/' . $page;
        //Deleted
        } elseif (is_file($folderPath . 'deleted/' . $page)) {
            return $folderPath . 'deleted/' . $page;
        }

        return false;
    }

    //Returns the images folder
    public function getImagesFolder()
    {
        if (substr(\Symfony\Component\HttpKernel\Kernel::VERSION, 0, 1) == 4) {
            return $this->container->getParameter('kernel.root_dir') . '/../public/images/' . $this->container->getParameter('c975_l_page_edit.folderPages') . '/';
        }

        return $this->container->getParameter('kernel.root_dir') . '/../web/images/' . $this->container->getParameter('c975_l_page_edit.folderPages') . '/';
    }

    //Gets links to display in the select in Tinymce
    public function getLinks()
    {
        //Defines paths
        $folderPath = $this->getPagesFolder();
        $protectedFolderPath = $folderPath . 'protected';

        //Finds pages
        $finder = new Finder();
        $finder
            ->files()
            ->in($folderPath)
            ->in($protectedFolderPath)
            ->depth('== 0')
            ->name('*.html.twig')
            ->sortByName()
            ;

        //Defines slug and title
        $pages = array();
        foreach ($finder as $file) {
            $slug = str_replace('.html.twig', '', $file->getRelativePathname());
            $title = $this->getTitle($file->getContents(), $slug);
            $titleTranslated = $this->getTitleTranslated($title);

            //Creates the array of available pages
            $pages[] = array(
                'title' => $titleTranslated,
                'value' => "{{ path('pageedit_display', {'page': '" . $slug . "'}) }}",
            );
        }

        return $pages;
    }

    //Get original content from a file
    public function getOriginalContent($fileContent)
    {
        //Kept `pageEdit` for compatibility for files not yet modified with new skeleton (06/03/2018)
        $startBlock = strpos($fileContent, '{% block pageedit_content %}') !== false ? '{% block pageedit_content %}' : '{% block pageEdit %}';
        $endBlock = '{% endblock %}';
        $entryPoint = strpos($fileContent, $startBlock) + strlen($startBlock);
        $exitPoint = strpos($fileContent, $endBlock, $entryPoint);

        $originalContent = trim(substr($fileContent, $entryPoint, $exitPoint - $entryPoint));

        return $originalContent;
    }

    //Gets all available pages
    public function getPages()
    {
        //Defines path
        $finder = new Finder();
        $folderPath = $this->getPagesFolder();

        //Gets pages for specific folder
        $view = $this->request->get('v');
        if ($view !== '' && in_array($view, array('archived', 'deleted', 'redirected'))) {
            $finder
                ->files()
                ->in($folderPath .= $view)
                ->name('*.html.twig')
                ->sortByType()
            ;
        //Gets current pages
        } else {
            $finder
                ->files()
                ->in($folderPath)
                ->exclude('archived')
                ->exclude('deleted')
                ->exclude('redirected')
                ->name('*.html.twig')
                ->sortByType()
            ;
        }

        //Returns pages with slug and title
        return $this->definePagesSlugTitle($finder, $view);
    }

    //Returns the pages folder
    public function getPagesFolder()
    {
        if (substr(\Symfony\Component\HttpKernel\Kernel::VERSION, 0, 1) == 4) {
            return $this->container->getParameter('kernel.root_dir') . '/../templates/' . $this->container->getParameter('c975_l_page_edit.folderPages') . '/';
        }

        return $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages') . '/';
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
        $skeleton = file_get_contents($this->container->getParameter('kernel.root_dir') . '/../vendor/c975l/pageedit-bundle/Resources/views/skeleton.html.twig');

        //Kept `pageEdit` for compatibility for files not yet modified with new skeleton (06/03/2018)
        $startBlock = strpos($skeleton, '{% block pageedit_content %}') !== false ? '{% block pageedit_content %}' : '{% block pageEdit %}';
        $endBlock = '{% endblock %}';

        $entryPoint = strpos($skeleton, $startBlock) + strlen($startBlock);
        $exitPoint = strpos($skeleton, $endBlock, $entryPoint);

        return array(
            'startSkeleton' => trim(substr($skeleton, 0, $entryPoint)),
            'endSkeleton' => trim(substr($skeleton, $exitPoint)),
        );
    }

    //Gets the title of the page
    public function getTitle($fileContent, $slug)
    {
        preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);

        //Plain title
        if (!empty($matches)) {
            $title = str_replace('\"', '"', $matches[1]);
        //Title is using Twig code to translate it
        } else {
            preg_match('/pageedit_title=(.*)\%\}/', $fileContent, $matches);
            if (!empty($matches)) {
                $title = trim($matches[1]);
            //Title not found
            } else {
                $title = $this->container->get('translator')->trans('label.title_not_found', array(), 'pageedit') . ' (' . $slug . ')';
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

    //Modifies files (+ archive old file)
    public function modifyFile($page, $originalContent, $formData, $userId)
    {
        //Gets slug
        if ($page != $formData->getSlug()) {
            $slug = $this->slugify($formData->getSlug(), true);
        } else {
            $slug = $formData->getSlug();
        }

        //Archives and redirects the file if title (then slug) has changed
        if ($slug != $page) {
            $this->archiveFile($page, $userId);
            $this->redirectFile($page, $slug);
        }

        //Writes file
        $this->writeFile($slug, $originalContent, $formData, $userId);

        return $slug;
    }

    //Creates the redirection file
    public function redirectFile($page, $slug)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getPagesFolder();
        $redirectedFolder = $folderPath . 'redirected';

        //Sets the redirection
        $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
        $fs->dumpFile($redirectedFilePath, $slug);
    }

    //Slugify function - https://github.com/cocur/slugify
    public function slugify($text, $keepSlashes = false)
    {
        $slugify = new Slugify();
        if ($keepSlashes) {
            $slugify->addRule('/', '-thereisaslash-');
        }
        $slug = str_replace('-thereisaslash-', '/', $slugify->slugify($text));

        //Checks unicity of slug
        $finalSlug = $slug;
        $slugExists = true;
        $i = 1;
        do {
            $slugExists = $this->slugExists($finalSlug);
            if ($slugExists) {
                $finalSlug = $slug . '-' . $i++;
            }
        } while (false !== $slugExists);

        return $finalSlug;
    }

    //Checks if slug already exists
    public function slugExists($slug)
    {
        $pages = $this->getPages();

        foreach ($pages as $page) {
            if (str_replace('protected/', '', $page['slug']) == $slug) {
                return true;
            }
        }

        return false;
    }

    //Writes file (+ archive old file)
    public function writeFile($page, $originalContent, $formData, $userId)
    {
        //Defines path
        $folderPath = $this->getPagesFolder();
        $filePath = $folderPath . $page . '.html.twig';

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
        $content = str_replace(array('{{path', '{{asset'), array('{{ path', '{{ asset'), $formData->getContent());
        $content = preg_replace('#href=\"(.*){{ path#', 'href="{{ path', $content);
        $content = preg_replace('#src=\"(.*){{ asset#', 'src="{{ asset', $content);

        //Concatenate skeleton + metadata + content
        $finalContent = $startSkeleton . "\n" . $content . "\n\t\t" . $endSkeleton;

        //Archives old file if content or metadata are different
        $this->createFolders();
        $fs = new Filesystem();
        if ($fs->exists($filePath) && file_get_contents($filePath) !== $finalContent) {
            $this->archiveFile($page, $userId);
        }

        //Create sub-folders
        if (false !== strpos($page, '/')) {
            $subfolder = substr($page, 0, strrpos($page, '/'));
            $fs->mkdir($folderPath . $subfolder, 0770);
        }

        //Writes new file
        $newFilePath = $folderPath . $page . '.html.twig';
        $fs->dumpFile($newFilePath, $finalContent);
        $fs->chmod($newFilePath, 0770);

        //Clears the cache otherwise changes will not be reflected
        $fs->remove($this->container->getParameter('kernel.cache_dir') . '/../prod/twig');
    }
}
