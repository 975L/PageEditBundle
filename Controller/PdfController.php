<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use c975L\PageEditBundle\Service\PageEditService;

class PdfController extends Controller
{
//DISPLAY
    /**
     * @Route("/pages/pdf/{page}",
     *      name="pageedit_pdf",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function display(PageEditService $pageEditService, $page)
    {
        //Gets page
        $filePath = $pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
            //Creates the pdf
            $filePdfPath = $pageEditService->createPdf($filePath, $page);

            //Renders the pdf
            return new Response(file_get_contents($filePdfPath), 200, array('Content-Type' => 'application/pdf'));
        }

        //Not found
        throw $this->createNotFoundException();
    }
}
