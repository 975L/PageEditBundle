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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * ArchivedController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class ArchivedController extends AbstractController
{
    public function __construct(
        /**
         * Stores PageEditServiceInterface
         */
        private readonly PageEditServiceInterface $pageEditService
    )
    {
    }

//DISPLAY
    /**
     * Displays the archived page
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    #[Route(
        '/pageedit/archived/{page}',
        name: 'pageedit_display_archived',
        requirements: [
            'page' => '^([a-zA-Z0-9\-\/]+)'
        ],
        methods: ['GET']
    )]
    public function display($page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-archived', null);

        //Renders the archived page
        $pageEdit = $this->pageEditService->getData($page);
        if ($pageEdit instanceof PageEdit) {
            return $this->render(
                '@c975LPageEdit/pages/archived.html.twig',
                ['pageEdit' => $pageEdit]
            )->setMaxAge(3600);
        }

        throw $this->createNotFoundException();
    }

//DELETE
    /**
     * Deletes the archived page
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    #[Route(
        '/pageedit/delete/archived/{page}',
        name: 'pageedit_delete_archived',
        requirements: [
            'page' => '^([a-zA-Z0-9\-\/]+)'
        ],
        methods: ['GET']
    )]
    public function delete(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-archived-delete', null);

        $pageEdit = $this->pageEditService->getData($page);
        if ($pageEdit instanceof PageEdit) {
            $form = $this->pageEditService->createForm('delete', $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $this->pageEditService->deleteFile('archived/' . $page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard', ['v' => 'archived']);
            }

            //Renders the delete form
            return $this->render(
                '@c975LPageEdit/forms/deleteArchived.html.twig',
                ['form' => $form->createView(), 'pageEdit' => $pageEdit]
            )->setMaxAge(3600);
        }

        throw $this->createNotFoundException();
    }
}