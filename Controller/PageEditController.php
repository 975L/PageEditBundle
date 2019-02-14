<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Controller;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Service\PageEditServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Main PageEdit Controller class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class PageEditController extends AbstractController
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

//HOME

    /**
     * Redirects to pageedit_home
     * @return Redirect
     *
     * @Route("/pages",
     *    name="pageedit_redirect_home",
     *    methods={"HEAD", "GET"})
     */
    public function redirectPages()
    {
        return $this->redirectToRoute('pageedit_home');
    }

    /**
     * Displays the homepage
     * @return Response
     *
     * @Route("/",
     *    name="pageedit_home",
     *    methods={"HEAD", "GET"})
     */
    public function home()
    {
        return $this->render('pages/home.html.twig', array(
            'toolbar' => $this->pageEditService->defineToolbar('display', 'home'),
            'display' => 'html',
        ));
    }

//DASHBOARD

    /**
     * Displays dashboard
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/dashboard",
     *    name="pageedit_dashboard",
     *    methods={"HEAD", "GET"})
     */
    public function dashboard(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-dashboard', null);

        //Renders the dashboard
        $pages = $paginator->paginate(
            $this->pageEditService->getPages(),
            $request->query->getInt('p', 1),
            15
        );
        return $this->render('@c975LPageEdit/pages/dashboard.html.twig', array(
            'pages' => $pages,
        ));
    }

//DISPLAY

    /**
     * Displays the page
     * @return Response
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @Route("/pages/{page}",
     *    name="pageedit_display",
     *    requirements={"page": "^(?!pdf)([a-zA-Z0-9\-\/]+)"},
     *    methods={"HEAD", "GET"})
     */
    public function display(AuthorizationCheckerInterface $authChecker, $page)
    {
        $pageEdit = $this->pageEditService->getData($page);

        if ($pageEdit instanceof PageEdit) {
            $filePath = $pageEdit->getFilePath();
            //Redirected page
            if (false !== strpos($filePath, '/redirected/')) {
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => trim(file_get_contents($filePath)),
                ));
            //Deleted page
            } elseif (false !== strpos($filePath, '/deleted/')) {
                throw new GoneHttpException();
            //Protected url
            } elseif(false !== strpos($page, 'protected/')) {
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => str_replace('protected/', '', $page),
                ));
            //Homepage called by pages/home
            } elseif ('home' === $page) {
                return $this->redirectToRoute('pageedit_home');
            }

            //Renders page
            $filePath = '4' === substr(Kernel::VERSION, 0, 1) ? substr($filePath, strpos($filePath, '/templates') + 10) : substr($filePath, strpos($filePath, '/views') + 6);
            return $this->render($filePath, array(
                'toolbar' => $this->pageEditService->defineToolbar('display', $page),
                'display' => 'html',
            ));
        }

        throw $this->createNotFoundException();
    }

//CREATE

    /**
     * Creates the PageEdit
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/create",
     *    name="pageedit_create",
     *    methods={"HEAD", "GET", "POST"})
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-create', null);

        $pageEdit = new PageEdit();
        $form = $this->pageEditService->createForm('create', $pageEdit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Registers file
            $slug = $this->pageEditService->register('createNewPageEdit', $form, $this->getUser());

            //Redirects to the page
            return $this->redirectToRoute('pageedit_display', array(
                'page' => $slug,
            ));
        }

        //Returns the create form
        return $this->render('@c975LPageEdit/forms/create.html.twig', array(
            'form' => $form->createView(),
            'pageEdit' => $pageEdit,
        ));
    }

//MODIFY

    /**
     * Modifies the PageEdit
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/modify/{page}",
     *    name="pageedit_modify",
     *    requirements={"page": "^[a-zA-Z0-9\-\/]+"},
     *    methods={"HEAD", "GET", "POST"})
     */
    public function modify(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-modify', null);

        $pageEdit = $this->pageEditService->getData($page);

        if ($pageEdit instanceof PageEdit) {
            $form = $this->pageEditService->createForm('modify', $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Registers file
                $slug = $this->pageEditService->register($page, $form, $this->getUser());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the modify form
            return $this->render('@c975LPageEdit/forms/modify.html.twig', array(
                'form' => $form->createView(),
                'pageEdit' => $pageEdit,
            ));
        }

        throw $this->createNotFoundException();
    }

//DUPLICATE

    /**
     * Duplicates the PageEdit
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/duplicate/{page}",
     *    name="pageedit_duplicate",
     *    requirements={"page": "^[a-zA-Z0-9\-\/]+"},
     *    methods={"HEAD", "GET", "POST"})
     */
    public function duplicate(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-duplicate', null);

        $pageEdit = $this->pageEditService->getData($page);

        if ($pageEdit instanceof PageEdit) {
            $pageEditClone = $this->pageEditService->cloneObject($pageEdit);
            $form = $this->pageEditService->createForm('duplicate', $pageEditClone);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Registers file
                $slug = $this->pageEditService->register($page, $form, $this->getUser());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the duplicate form
            return $this->render('@c975LPageEdit/forms/duplicate.html.twig', array(
                'form' => $form->createView(),
                'pageEdit' => $pageEditClone,
            ));
        }

        throw $this->createNotFoundException();
    }

//DELETE

    /**
     * Deletes the PageEdit (Moves the page to deleted folder)
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/delete/{page}",
     *    name="pageedit_delete",
     *    requirements={"page": "^[a-zA-Z0-9\-\/]+"},
     *    methods={"HEAD", "GET", "POST"})
     */
    public function delete(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-delete', null);

        $pageEdit = $this->pageEditService->getData($page);

        if ($pageEdit instanceof PageEdit) {
            $form = $this->pageEditService->createForm('delete', $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $slug = $this->pageEditService->delete($page, true);

                //Redirects to the dashboard
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Returns the delete form
            return $this->render('@c975LPageEdit/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'pageEdit' => $pageEdit,
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//CONFIG

    /**
     * Displays the configuration
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/config",
     *    name="pageedit_config",
     *    methods={"HEAD", "GET", "POST"})
     */
    public function config(Request $request, ConfigServiceInterface $configService)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-config', null);

        //Defines form
        $form = $configService->createForm('c975l/pageedit-bundle');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Validates config
            $configService->setConfig($form);

            //Redirects
            return $this->redirectToRoute('pageedit_dashboard');
        }

        //Renders the config form
        return $this->render('@c975LConfig/forms/config.html.twig', array(
            'form' => $form->createView(),
            'toolbar' => '@c975LPageEdit',
        ));
    }

//HELP

    /**
     * Displays the help
     * @return Response
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/help",
     *    name="pageedit_help",
     *    methods={"HEAD", "GET"})
     */
    public function help()
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-help', null);

        //Renders the help
        return $this->render('@c975LPageEdit/pages/help.html.twig');
    }
}
