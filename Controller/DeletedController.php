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
use c975L\PageEditBundle\Service\PageEditServiceInterface;

/**
 * DeletedController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class DeletedController extends Controller
{
    /**
     * Stores PageEditServiceInterface
     * @var PageEditServiceInterface
     */
    private $pageEditService;

    public function __construct(PageEditServiceInterface $pageEditService)
    {
        $this->pageEditService = $pageEditService;
    }

//DISPLAY
    /**
     * Displays the deleted page
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/pageedit/deleted/{page}",
     *      name="pageedit_display_deleted",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function display($page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-deleted', null);

        //Renders the deleted page
        $pageEdit = $this->pageEditService->getData($page);
        if ($pageEdit instanceof PageEdit) {
            return $this->render('@c975LPageEdit/pages/deleted.html.twig', array(
                'pageEdit' => $pageEdit,
            ));
        }

        throw $this->createNotFoundException();
    }

//DELETE
    /**
     * Deletes the deleted page
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/pageedit/delete/deleted/{page}",
     *      name="pageedit_delete_deleted",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD", "POST"})
     */
    public function delete(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-deleted-delete', null);

        $pageEdit = $this->pageEditService->getData($page);
        if ($pageEdit instanceof PageEdit) {
            $form = $this->pageEditService->createForm('delete', $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $this->pageEditService->deleteFile('deleted/' . $page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard', array('v' => 'deleted'));
            }

            //Renders the delete form
            return $this->render('@c975LPageEdit/forms/deleteDeleted.html.twig', array(
                'form' => $form->createView(),
                'pageEdit' => $pageEdit,
            ));
        }

        throw $this->createNotFoundException();
    }
}
