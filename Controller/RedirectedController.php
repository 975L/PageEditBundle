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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;
use c975L\PageEditBundle\Service\PageEditService;

class RedirectedController extends Controller
{
    private $pageEditService;

    public function __construct(\c975L\PageEditBundle\Service\PageEditService $pageEditService)
    {
        $this->pageEditService = $pageEditService;
    }

//DISPLAY
    /**
     * @Route("/pages/redirected/{page}",
     *      name="pageedit_display_redirected",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function display($page)
    {
        $this->denyAccessUnlessGranted('redirected', null);

        //Gets page
        $filePath = $this->pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
            //Renders the page
            $datetime = new \DateTime();
            $datetime->setTimestamp(filemtime($filePath));
            return $this->render('@c975LPageEdit/pages/redirected.html.twig', array(
                'redirection' => trim(file_get_contents($filePath)),
                'datetime' => $datetime,
                'page' => $page,
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//DELETE
    /**
     * @Route("/pages/delete/redirected/{page}",
     *      name="pageedit_delete_redirected",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function delete(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('redirected-delete', null);

        //Gets page
        $filePath = $this->pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
            //Defines form
            $pageEdit = new PageEdit($page, $page, $page);
            $pageEditConfig = array('action' => 'delete');
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $this->pageEditService->deleteFile('redirected/' . $page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard', array('v' => 'redirected'));
            }

            //Returns the delete form
            $datetime = new \DateTime();
            $datetime->setTimestamp(filemtime($filePath));
            return $this->render('@c975LPageEdit/forms/deleteRedirected.html.twig', array(
                'form' => $form->createView(),
                'page' => $page,
                'datetime' => $datetime,
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }
}
