<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Controller;

use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Service\PageEditServiceInterface;
use c975L\PageEditBundle\Service\Pdf\PageEditPdfInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PdfController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PdfController extends AbstractController
{
//DISPLAY THE PDF
    /**
     * Creates and displays the pdf of the page
     * @return Response
     * @throws NotFoundHttpException
     */
    #[Route(
        '/pages/pdf/{page}',
        name: 'pageedit_pdf',
        requirements: [
            'page' => '^([a-zA-Z0-9\-\/]+)'
        ],
        methods: ['GET']
    )]
    public function display(PageEditServiceInterface $pageEditService, PageEditPdfInterface $pageEditPdf, $page)
    {
        $pageEdit = $pageEditService->getData($page);

        if ($pageEdit instanceof PageEdit) {
            //Creates the pdf
            $filePdfPath = $pageEditPdf->create($pageEdit->getFilePath(), $page);

            //Renders the pdf
            return new Response(file_get_contents($filePdfPath), 200, ['Content-Type' => 'application/pdf']);
        }

        throw $this->createNotFoundException();
    }
}