<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
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
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Knp\Component\Pager\PaginatorInterface;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;

class PageEditController extends Controller
{
    private $pageEditService;

    public function __construct(\c975L\PageEditBundle\Service\PageEditService $pageEditService)
    {
        $this->pageEditService = $pageEditService;
    }

//HOME
    /**
     * @Route("/pages")
     * @Method({"GET", "HEAD"})
     */
    public function redirectPages()
    {
        return $this->redirectToRoute('pageedit_home');
    }
    /**
     * @Route("/",
     *      name="pageedit_home")
     * @Method({"GET", "HEAD"})
     */
    public function home()
    {
        return new Response(
            $this->forward('c975L\PageEditBundle\Controller\PageEditController::display', array(
                'page'  => 'home',
            ))->getContent()
        );
    }

//DASHBOARD
    /**
     * @Route("/pages/dashboard",
     *      name="pageedit_dashboard")
     * @Method({"GET", "HEAD"})
     */
    public function dashboard(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('dashboard', null);

        //Gets pages
        $pages = $this->pageEditService->getPages();
        $pagination = $paginator->paginate(
            $pages,
            $request->query->getInt('p', 1),
            15
        );

        //Renders the dashboard
        return $this->render('@c975LPageEdit/pages/dashboard.html.twig', array(
            'pages' => $pagination,
        ));
    }

//DISPLAY
    /**
     * @Route("/pages/{page}",
     *      name="pageedit_display",
     *      requirements={
     *          "page": "^(?!archived|create|dashboard|delete|deleted|duplicate|home|modify|help|links|new|pdf|redirected|slug|upload)([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function display(AuthorizationCheckerInterface $authChecker, $page)
    {
        //Gets page
        $filePath = $this->pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
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
            }

            //Renders page
            return $this->render(substr($filePath, strpos($filePath, '/views') + 6), array(
                'toolbar' => $this->pageEditService->defineToolbar('display', $page),
                'display' => 'html',
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//CREATE
    /**
     * @Route("/pages/create",
     *      name="pageedit_create")
     */
    public function create(Request $request)
    {
        $pageEdit = new PageEdit();
        $this->denyAccessUnlessGranted('create', null);

        //Defines form
        $pageEditConfig = array('action' => 'create');
        $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Writes file
            $slug = $this->pageEditService->slugify($form->getData()->getSlug(), true);
            $this->pageEditService->writeFile($slug, null, $form->getData(), $this->getUser()->getId());

            //Redirects to the page
            return $this->redirectToRoute('pageedit_display', array(
                'page' => $slug,
            ));
        }

        //Returns the create form
        return $this->render('@c975LPageEdit/forms/create.html.twig', array(
            'form' => $form->createView(),
            'page' => 'new',
            'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
            'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
            ));
    }

//MODIFY
    /**
     * @Route("/pages/modify/{page}",
     *      name="pageedit_modify",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function modify(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('modify', null);

        //Gets page
        $filePath = $this->pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
            //Gets data
            extract($this->pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, $title, $page, $changeFrequency, $priority, $description);
            $pageEditConfig = array('action' => 'modify');
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Writes file
                $slug = $this->pageEditService->modifyFile($page, $originalContent, $form->getData(), $this->getUser()->getId());

                //Redirects to the homepage
                if ($slug == 'home') {
                    return $this->redirectToRoute('pageedit_home');
                }

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the modify form
            return $this->render('@c975LPageEdit/forms/modify.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => str_replace('\"', '"', $titleTranslated),
                'page' => $page,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//DUPLICATE
    /**
     * @Route("/pages/duplicate/{page}",
     *      name="pageedit_duplicate",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function duplicate(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('duplicate', null);

        //Gets page
        $filePath = $this->pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
            //Gets data
            extract($this->pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, null, null, $changeFrequency, $priority, $description);
            $pageEditConfig = array('action' => 'duplicate');
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Writes file
                $slug = $this->pageEditService->slugify($form->getData()->getSlug(), true);
                $this->pageEditService->writeFile($slug, $originalContent, $form->getData(), $this->getUser()->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the form to duplicate content
            return $this->render('@c975LPageEdit/forms/duplicate.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => str_replace('\"', '"', $titleTranslated),
                'page' => $page,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//DELETE
    /**
     * @Route("/pages/delete/{page}",
     *      name="pageedit_delete",
     *      requirements={
     *          "page": "^(?!archived|deleted|redirected)([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function delete(Request $request, $page)
    {
        $this->denyAccessUnlessGranted('delete', null);

        //Gets page
        $filePath = $this->pageEditService->getFilePath($page);

        //Existing page
        if (false !== $filePath) {
            //Gets data
            extract($this->pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, $title, $page);
            $pageEditConfig = array('action' => 'delete');
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $this->pageEditService->deleteFile($page, true);

                //Redirects to the dashboard
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Returns the form to delete page
            return $this->render('@c975LPageEdit/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => $titleTranslated,
                'page' => $page,
                'pageContent' => $originalContent,
                'type' => 'delete',
            ));
        }

        //Not found
        throw $this->createNotFoundException();
    }

//HELP
    /**
     * @Route("/pages/help",
     *      name="pageedit_help")
     * @Method({"GET", "HEAD"})
     */
    public function help()
    {
        $this->denyAccessUnlessGranted('help', null);

        //Renders the help
        return $this->render('@c975LPageEdit/pages/help.html.twig');
    }
}
