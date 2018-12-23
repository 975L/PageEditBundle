<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Twig;

use c975L\PageEditBundle\Service\File\PageEditFileInterface;
use c975L\PageEditBundle\Service\PageEditServiceInterface;
use Symfony\Component\Finder\Finder;
use Twig_Extension;

/**
 * Twig extension to display the formatted GiftVoucherPurchased identifier using `|gv_identifier`
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditFolderContent extends Twig_Extension
{
    /**
     * Stores PageEditFileInterface
     * @var PageEditFileInterface
     */
    private $pageEditFile;

    /**
     * Stores PageEditServiceInterface
     * @var PageEditServiceInterface
     */
    private $pageEditService;

    public function __construct(
        PageEditFileInterface $pageEditFile,
        PageEditServiceInterface $pageEditService
    )
    {
        $this->pageEditFile = $pageEditFile;
        $this->pageEditService = $pageEditService;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('folder_content', array($this, 'folderContent')),
        );
    }

    /**
     * Returns an associative array(filename => titleTranslated) for the content ot specified folder
     * @return array
     */
    public function folderContent($folder)
    {
        //Gets folder's files
        $folderPath = $this->pageEditFile->getPagesFolder() . $folder;
        $finder = new Finder();
        $finder
            ->files()
            ->in($folderPath)
            ->name('*.html.twig')
            ->sortByType()
        ;

        //Finds titles
        $folderContent = array();
        foreach ($finder as $file) {
            $title = $this->pageEditService->getTitle($file->getContents(), $file);
            $titleTranslated = $this->pageEditService->getTitleTranslated($title);

            $folderContent[$folder . '/' . str_replace('.html.twig', '', $file->getFilename())] = $titleTranslated;
        }

        return $folderContent;
    }
}