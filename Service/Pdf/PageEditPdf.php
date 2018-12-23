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
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Environment;

/**
 * Services related to PageEdit Pdf
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditPdf implements PageEditPdfInterface
{
    /**
     * Stores Pdf
     * @var Pdf
     */
    private $knpSnappyPdf;

    /**
     * Stores PageEditFileInterface
     * @var PageEditFileInterface
     */
    private $pageEditFile;

    /**
     * Stores current Request
     * @var Request
     */
    private $request;

    /**
     * Stores Twig_Environment
     * @var Twig_Environment
     */
    private $templating;

    public function __construct(
        Pdf $knpSnappyPdf,
        PageEditFileInterface $pageEditFile,
        RequestStack $requestStack,
        Twig_Environment $templating
    )
    {
        $this->knpSnappyPdf = $knpSnappyPdf;
        $this->pageEditFile = $pageEditFile;
        $this->request = $requestStack->getCurrentRequest();
        $this->templating = $templating;
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

            $html = $this->templating->render($filePath, array(
                'toolbar' => '',
                'display' => 'pdf',
            ));
            $this->pageEditFile->createFolders();
            file_put_contents($filePdfPath, $this->knpSnappyPdf->getOutputFromHtml(str_replace('https:', 'http:', $html)));
        }

        return $filePdfPath;
    }
}