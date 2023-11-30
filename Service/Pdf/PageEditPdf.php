<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service\Pdf;

use c975L\PageEditBundle\Service\File\PageEditFileInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Services related to PageEdit Pdf
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditPdf implements PageEditPdfInterface
{
    /**
     * Stores current Request
     */
    private readonly ?\Symfony\Component\HttpFoundation\Request $request;

    public function __construct(
        /**
         * Stores PageEditFileInterface
         */
        private readonly PageEditFileInterface $pageEditFile,

        RequestStack $requestStack,
        /**
         * Stores Environment
         */
        private readonly Environment $environment
    )
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $filePath, string $page)
    {
        $filePdfPath = $this->pageEditFile->getPagesFolder() . 'pdf/' . $page . '-' . $this->request->getLocale() . '.pdf';
        $amountTime = 24 * 60 * 60;

        //Checks if pdf is not existing, not up-to-date or has exceeded an amount of time
        if (!is_file($filePdfPath) ||
            filemtime($filePdfPath) < filemtime($filePath) ||
            filemtime($filePdfPath) + $amountTime < time()) {

            //Removes full path to allow environment to find the file
            $filePath = str_contains($filePath, '/templates/') ? substr($filePath, strpos($filePath, '/templates/') + 11) : $filePath;

            //Creates pdf
            $html = $this->environment->render($filePath, ['toolbar' => '', 'display' => 'pdf']);
            $this->pageEditFile->createFolders();
//            file_put_contents($filePdfPath, $this->knpSnappyPdf->getOutputFromHtml(str_replace('https:', 'http:', $html)));
        }

        return $filePdfPath;
    }
}