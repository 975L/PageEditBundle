<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service\File;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Form\PageEditFormFactoryInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * Services related to PageEdit File
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditFile implements PageEditFileInterface
{
    /**
     * Stores AuthorizationCheckerInterface
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * Stores ContainerInterface
     * @var ContainerInterface
     */
    private $configService;

    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $container;

    /**
     * Stores Pdf
     * @var Pdf
     */
    private $knpSnappyPdf;

    /**
     * Stores PageEditFormFactoryInterface
     * @var PageEditFormFactoryInterface
     */
    private $pageEditFormFactory;

    /**
     * Stores current Request
     * @var Request
     */
    private $request;

    /**
     * Stores Environment
     * @var Environment
     */
    private $environment;

    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        ConfigServiceInterface $configService,
        ContainerInterface $container,
        Pdf $knpSnappyPdf,
        PageEditFormFactoryInterface $pageEditFormFactory,
        RequestStack $requestStack,
        Environment $environment
    )
    {
        $this->authChecker = $authChecker;
        $this->configService = $configService;
        $this->container = $container;
        $this->knpSnappyPdf = $knpSnappyPdf;
        $this->pageEditFormFactory = $pageEditFormFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(string $page, object $user)
    {
        $folderPath = $this->getPagesFolder();
        $archivedFolder = $folderPath . 'archived';
        $filePath = $folderPath . $page . '.html.twig';

        $fs = new Filesystem();
        if ($fs->exists($filePath)) {
            //Create sub-folders
            if (false !== strpos($page, '/')) {
                $subfolder = substr($page, 0, strrpos($page, '/'));
                $fs->mkdir($archivedFolder . '/' . $subfolder, 0770);
            }

            $fs->rename($filePath, $archivedFolder . '/' . $page . '-' . date('Ymd-His') . '-' . $user->getId() . '.html.twig');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createFolders()
    {
        //Defines folders
        $folderPath = $this->getPagesFolder();
        $folders = array (
            $folderPath . 'archived',
            $folderPath . 'deleted',
            $folderPath . 'pdf',
            $folderPath . 'protected',
            $folderPath . 'redirected',
            $this->getImagesFolder(),
        );

        //Creates folders
        $fs = new Filesystem();
        $fs->mkdir($folders, 0770);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $page, bool $archive)
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

    /**
     * {@inheritdoc}
     */
    public function getImagesFolder()
    {
        static $imagesFolder;

        if (null !== $imagesFolder) {
            return $imagesFolder;
        }

        $root = $this->container->getParameter('kernel.project_dir');
        $imagesFolder = '3' === substr(Kernel::VERSION, 0, 1) ? $root . '/../web/images/' : $root . '/../public/images/';

        return $imagesFolder . $this->configService->getParameter('c975LPageEdit.folderPages') . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function getPages()
    {
        static $pages;

        if (null !== $pages) {
            return $pages;
        }

        $pages = new Finder();
        $folderPath = $this->getPagesFolder();
        $view = $this->request->get('v');

        //Gets pages for specific folder
        if (null !== $view && in_array($view, array('archived', 'deleted', 'redirected'))) {
            $pages
                ->files()
                ->in($folderPath .= $view)
                ->name('*.html.twig')
                ->sortByType()
            ;
        //Gets current pages
        } else {
            $pages
                ->files()
                ->in($folderPath)
                ->exclude('archived')
                ->exclude('deleted')
                ->exclude('redirected')
                ->name('*.html.twig')
                ->sortByType()
            ;
        }

        return $pages;
    }

    /**
     * {@inheritdoc}
     */
    public function getPagesFolder()
    {
        static $pageFolder;

        if (null !== $pageFolder) {
            return $pageFolder;
        }

        $root = $this->container->getParameter('kernel.project_dir');
        $pageFolder = '3' === substr(Kernel::VERSION, 0, 1) ? $root . '/Resources/views/' : $root . '/../templates/';

        return $pageFolder . $this->configService->getParameter('c975LPageEdit.folderPages') . '/';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(string $page)
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

    //Gets the start and end of the skeleton
    public function getSkeletonStartEnd()
    {
        $skeleton = file_get_contents($this->container->getParameter('kernel.project_dir') . '/../vendor/c975l/pageedit-bundle/Resources/views/skeleton.html.twig');

        $startBlock = '{% block pageedit_content %}';
        $endBlock = '{% endblock %}';

        $entryPoint = strpos($skeleton, $startBlock) + strlen($startBlock);
        $exitPoint = strpos($skeleton, $endBlock, $entryPoint);

        return array(
            'start' => trim(substr($skeleton, 0, $entryPoint)),
            'end' => trim(substr($skeleton, $exitPoint)),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function redirect(string $page, string $slug)
    {
        $fs = new Filesystem();
        $redirectedFolder = $this->getPagesFolder() . 'redirected';

        //Sets the redirection
        $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
        $fs->dumpFile($redirectedFilePath, $slug);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $slug, Form $form, object $user)
    {
        $folderPath = $this->getPagesFolder();
        $folderImage = $this->getImagesFolder();
        $filePath = $folderPath . $slug . '.html.twig';
        $formData = $form->getData();
        $skeleton = $this->getSkeletonStartEnd();

        //Gets title
        $title = $formData->getTitle();
        //Title is using Twig code to translate it
        if (0 === strpos($title, '{{')) {
            $title = trim(str_replace(array('{{', '}}'), '', $title));
        //Title is text
        } elseif (false === strpos($title, '|trans')) {
            $title = '"' . str_replace('"', '\"', $formData->getTitle()) . '"';
        }

        //Updates metadata
        $startSkeleton = preg_replace('/pageedit_title=\"(.*)\"/', 'pageedit_title=' . $title, $skeleton['start']);
        $startSkeleton = preg_replace('/pageedit_changeFrequency=\"(.*)\"/', 'pageedit_changeFrequency="' . $formData->getChangeFrequency() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_priority=\"(.*)\"/', 'pageedit_priority="' . $formData->getPriority() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_description=\"(.*)\"/', 'pageedit_description="' . $formData->getDescription() . '"', $startSkeleton);

        //Cleans content
        $content = str_replace(array('{{path', '{{asset'), array('{{ path', '{{ asset'), $formData->getContent());
        $content = preg_replace('#href=\"(.*){{ path#', 'href="{{ path', $content);
        $content = preg_replace('#src=\"(.*){{ asset#', 'src="{{ asset', $content);

        //Renames new images using the slug
        preg_match_all('#src=\".*/(new\-[0-9]{8}\-[0-9]{6}\-[0-9]{6}\.[jpg|jpeg|gif|png]{1,4})+\"#', $content, $images, PREG_OFFSET_CAPTURE);
        preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $images);
        preg_match_all('/< *img[^>]*src *= *["\']?[^"\']*(new\-[0-9]{8}\-[0-9]{6}\-[0-9]{6}\.[jpg|jpeg|gif|png]+)/i', $content, $images);
        $fs = new Filesystem();
        if (!empty($images)) {
            foreach ($images[1] as $image) {
                $slugImage = str_replace('new', $slug, $image);
                $content = str_replace($image, $slugImage, $content);
                if ($fs->exists($folderImage . $image)) {
                    $fs->rename($folderImage . $image, $folderImage . $slugImage);
                }
            }
        }

        //Concatenate skeleton + metadata + content
        $finalContent = $startSkeleton . "\n" . $content . "\n\t\t" . $skeleton['end'];

        //Archives old file if content or metadata are different
        $this->createFolders();
        if ($fs->exists($filePath) && file_get_contents($filePath) !== $finalContent) {
            $this->archive($slug, $user);
        }

        //Create sub-folders
        if (false !== strpos($slug, '/')) {
            $subfolder = substr($slug, 0, strrpos($slug, '/'));
            $fs->mkdir($folderPath . $subfolder, 0770);
        }

        //Writes new file
        $newFilePath = $folderPath . $slug . '.html.twig';
        $fs->dumpFile($newFilePath, $finalContent);
        $fs->chmod($newFilePath, 0770);

        //Clears the cache otherwise changes will not be reflected
        $fs->remove($this->container->getParameter('kernel.cache_dir') . '/../prod/twig');
    }
}
