<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Twig;

use Symfony\Component\Finder\Finder;
use c975L\PageEditBundle\Service\PageEditService;

class PageEditFolderContent extends \Twig_Extension
{
    private $container;

    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('folder_content', array($this, 'folderContent')),
        );
    }

    public function folderContent($folder)
    {
        //Gets the Finder
        $finder = new Finder();

        //Finds files
        $folderPath = $this->container->getParameter('kernel.root_dir') . '/Resources/views/' . $this->container->getParameter('c975_l_page_edit.folderPages') . '/' . $folder;
        $finder
            ->files()
            ->in($folderPath)
            ->name('*.html.twig')
            ->sortByType()
        ;

        //Finds titles
        $pageEditService = $this->container->get(PageEditService::class);
        $folderContent = array();
        foreach ($finder as $file) {
            $title = $pageEditService->getTitle($file->getContents(), $file);
            $titleTranslated = $pageEditService->getTitleTranslated($title);
            $folderContent[$folder . '/' . str_replace('.html.twig', '', $file->getFilename())] = $titleTranslated;
        }

        return $folderContent;
    }
}