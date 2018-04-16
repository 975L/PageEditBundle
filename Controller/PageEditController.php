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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;
use c975L\PageEditBundle\Service\PageEditService;

class PageEditController extends Controller
{
//REMOVE TRAILING SLASH
    /**
    * @Route("/{url}",
    *       name="remove_trailing_slash",
    *       requirements={"url": "^.*\/$"})
    * @Method({"GET", "HEAD"})
    */
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        return $this->redirect($url);
    }

//HOME
    /**
     * @Route("/pages")
     * @Method({"GET", "HEAD"})
     */
    public function redirectPagesAction()
    {
        return $this->redirectToRoute('pageedit_home');
    }
    /**
     * @Route("/",
     *      name="pageedit_home")
     * @Method({"GET", "HEAD"})
     */
    public function homeAction()
    {
        return new Response(
            $this->forward('c975L\PageEditBundle\Controller\PageEditController::displayAction', array(
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
    public function dashboardAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the dashboard content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Creates structure in case it not exists
            $pageEditService = $this->get(PageEditService::class);
            $pageEditService->createFolders();

            //Gets the Finder
            $finder = new Finder();

            //Defines paths
            $folderPath = $pageEditService->getPagesFolder();

            //Adjust path is specific folder is requested
            $view = $request->get('v');
            if ($view !== '' && in_array($view, array('archived', 'deleted', 'redirected'))) {
                $folderPath .= $view;
            }

            //Gets pages
            if ($view == '') {
                $finder
                    ->files()
                    ->in($folderPath)
                    ->exclude('archived')
                    ->exclude('deleted')
                    ->exclude('redirected')
                    ->name('*.html.twig')
                    ->sortByType()
                ;
            } else {
                $finder
                    ->files()
                    ->in($folderPath)
                    ->name('*.html.twig')
                    ->sortByType()
                ;
            }

            //Defines slug and title
            $pages = array();
            $subpages = array();
            foreach ($finder as $file) {
                $slug = str_replace('.html.twig', '', $file->getRelativePathname());
                $title = $pageEditService->getTitle($file->getContents(), $slug);
                $titleTranslated = $pageEditService->getTitleTranslated($title);

                //Defines status of page
                if (strpos($file->getPath(), 'protected') !== false) {
                    $status = 'protected';
                } elseif ($view == '') {
                    $status = 'current';
                } else {
                    $status = $view;
                }
                //Adds page to array
                if (strpos($slug, '/') === false) {
                    $pages[] = array(
                        'slug' => $slug,
                        'title' => $titleTranslated,
                        'status' => $status,
                    );
                //Adds subpage
                } else {
                    $subpages[] = array(
                        'slug' => $slug,
                        'title' => $titleTranslated,
                        'status' => $status,
                    );

                }
            }

            //Pagination
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                array_merge($pages, $subpages),
                $request->query->getInt('p', 1),
                15
            );

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'dashboard',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the dashboard
            return $this->render('@c975LPageEdit/pages/dashboard.html.twig', array(
                'pages' => $pagination,
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DISPLAY
    /**
     * @Route("/pages/{page}",
     *      name="pageedit_display",
     *      requirements={
     *          "page": "^(?!archived|dashboard|delete|deleted|duplicate|modify|help|links|new|pdf|redirected|slug|upload)([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayAction($page)
    {
        $page = rtrim($page, '/');

        $pageEditService = $this->get(PageEditService::class);
        $folderPath = $pageEditService->getPagesFolder();

        //Existing page
        $filePath = $folderPath . $page . '.html.twig';
        if (is_file($filePath)) {
            //Gets the user
            $user = $this->getUser();

            //Defines toolbar
            $toolbar = '';
            if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
                $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                    'type' => 'display',
                    'page' => $page,
                ));
                $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                    'tools'  => $tools,
                    'dashboard'  => 'pageedit',
                ))->getContent();
            }

            //Renders the page
            return $this->render($filePath, array(
                'toolbar' => $toolbar,
                'display' => 'html',
            ));
        }

        //Protected page
        $fileProtectedPath = $folderPath . 'protected/' . $page . '.html.twig';
        if (is_file($fileProtectedPath)) {
            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'protected',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            return $this->render($fileProtectedPath, array(
                'toolbar' => $toolbar,
            ));
        }

        //Redirected page
        $fileRedirectedPath = $folderPath . 'redirected/' . $page . '.html.twig';
        //Redirected page
        if (is_file($fileRedirectedPath)) {
            return $this->redirectToRoute('pageedit_display', array(
                'page' => trim(file_get_contents($fileRedirectedPath)),
            ));
        }

        //Deleted page
        $fileDeletedPath = $folderPath . 'deleted/' . $page . '.html.twig';
        if (is_file($fileDeletedPath)) {
            throw new GoneHttpException();
        }

        //Not existing page
        throw $this->createNotFoundException();
    }

//DISPLAY ARCHIVED
    /**
     * @Route("/pages/archived/{page}",
     *      name="pageedit_display_archived",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayArchivedAction($page)
    {
        //Gets the user
        $user = $this->getUser();

        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            $filePath = $this->getParameter('c975_l_page_edit.folderPages') . '/archived/' . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data for archived
            $lastHyphen = strrpos($page, '-') + 1;
            $userId = substr($page, $lastHyphen);
            $userManager = $this->container->get('fos_user.user_manager');
            $userArchived = $userManager->findUserBy(array('id' => $userId));
            if ($userArchived !== null) {
                $username = $userArchived->getFirstname() . ' ' . $userArchived->getLastname();
            } else {
                $username = $userId;
            }
            $datetime = \DateTime::createFromFormat('Ymd-His', substr($page, $lastHyphen - strlen($userId) - 15, 15));

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'archived',
                'page' => $page,
                'datetime' => $datetime,
                'username' => $username,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            return $this->render($filePath, array(
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DISPLAY DELETED
    /**
     * @Route("/pages/deleted/{page}",
     *      name="pageedit_display_deleted",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayDeletedAction($page)
    {
        //Gets the user
        $user = $this->getUser();

        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . 'deleted/' . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data for deleted
            $datetime = \DateTime::createFromFormat('Ymd-His', date('Ymd-His', filemtime($filePath)));

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'deleted',
                'page' => $page,
                'datetime' => $datetime,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            return $this->render($filePath, array(
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DISPLAY REDIRECTED
    /**
     * @Route("/pages/redirected/{page}",
     *      name="pageedit_display_redirected",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayRedirectedAction($page)
    {
        //Gets the user
        $user = $this->getUser();

        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPages . 'redirected/' . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data for redirected
            $datetime = \DateTime::createFromFormat('Ymd-His', date('Ymd-His', filemtime($filePath)));
            $redirection = file_get_contents($filePath);

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'redirected',
                'page' => $page,
                'datetime' => $datetime,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the page
            return $this->render('@c975LPageEdit/pages/redirect.html.twig', array(
                'page' => $page,
                'redirection' => $redirection,
                'toolbar' => $toolbar
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//PDF
    /**
     * @Route("/pages/pdf/{page}",
     *      name="pageedit_pdf",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function pdfAction(Request $request, $page)
    {
        $page = rtrim($page, '/');

        $pageEditService = $this->get(PageEditService::class);
        $folderPath = $pageEditService->getPagesFolder();
        $folderPdfPath = $folderPath . 'pdf/';

        $filePath = $folderPath . $page . '.html.twig';
        $fileProtectedPath = $folderPath . 'protected/' . $page . '.html.twig';
        $filePdfPath = $folderPdfPath . $page . '-' . $request->getLocale() . '.pdf';

        //Defines the location of page
        $fileFinalPath = null;
        if (is_file($filePath)) {
            $fileFinalPath = $filePath;
        } elseif (is_file($fileProtectedPath)) {
            $fileFinalPath = $fileProtectedPath;
        }

        //Not existing page
        if (!is_file($fileFinalPath)) {
            throw $this->createNotFoundException();
        }

        //Creates the pdf if not existing, not up-to-date or has exceeded an amount of time
        $amountTime = 60 * 60 * 24;//24 hours
        if (!is_file($filePdfPath) || filemtime($filePdfPath) < filemtime($fileFinalPath) || filemtime($filePdfPath) + $amountTime < time()) {
            $pageEditService->createFolders();
            $html = $this->renderView($fileFinalPath, array(
                'toolbar' => null,
                'display' => 'pdf',
            ));
            $pdf = $this->get('knp_snappy.pdf')->getOutputFromHtml($html);
            file_put_contents($filePdfPath, $pdf);
        }

        //Returns the pdf
        return new Response(file_get_contents($filePdfPath), 200, array('Content-Type' => 'application/pdf'));
    }

//NEW
    /**
     * @Route("/pages/new",
     *      name="pageedit_new")
     */
    public function newAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines form
            $pageEdit = new PageEdit();
            $pageEditConfig = array(
                'action' => 'new',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $pageEditService = $this->get(PageEditService::class);
                $slug = $pageEditService->slugify($form->getData()->getSlug(), true);

                //Writes file
                $pageEditService->writeFile($slug, null, $form->getData(), $user->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'new',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to create new page
            return $this->render('@c975LPageEdit/forms/new.html.twig', array(
                'form' => $form->createView(),
                'page' => 'new',
                'toolbar' => $toolbar,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//MODIFY
    /**
     * @Route("/pages/modify/{page}",
     *      name="pageedit_modify",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function modifyAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines path
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data
            extract($pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, $title, $page, $changeFrequency, $priority, $description);
            $pageEditConfig = array(
                'action' => 'modify',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $slug = $pageEditService->slugify($form->getData()->getSlug(), true);

                //Archives and redirects the file if title (then slug) has changed
                if ($slug != $page) {
                    $pageEditService->archiveFile($page, $user->getId());
                    $pageEditService->redirectFile($page, $slug);
                }

                //Writes file
                $pageEditService->writeFile($slug, $originalContent, $form->getData(), $user->getId());

                //Redirects to the homepage
                if ($slug == 'home') {
                    return $this->redirectToRoute('pageedit_home');
                }

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'modify',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to modify content
            return $this->render('@c975LPageEdit/forms/modify.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => str_replace('\"', '"', $titleTranslated),
                'page' => $page,
                'toolbar' => $toolbar,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DUPLICATE
    /**
     * @Route("/pages/duplicate/{page}",
     *      name="pageedit_duplicate",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function duplicateAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines path
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data
            extract($pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, null, null, $changeFrequency, $priority, $description);
            $pageEditConfig = array(
                'action' => 'duplicate',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $slug = $pageEditService->slugify($form->getData()->getSlug(), true);

                //Writes file
                $pageEditService->writeFile($slug, $originalContent, $form->getData(), $user->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'duplicate',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to duplicate content
            return $this->render('@c975LPageEdit/forms/duplicate.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => str_replace('\"', '"', $titleTranslated),
                'page' => $page,
                'toolbar' => $toolbar,
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE
    /**
     * @Route("/pages/delete/{page}",
     *      name="pageedit_delete",
     *      requirements={
     *          "page": "^(?!archived|deleted|redirected)([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function deleteAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines paths
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data
            extract($pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, $title, $page);
            $pageEditConfig = array(
                'action' => 'delete',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $pageEditService->deleteFile($page, true);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'delete',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to delete page
            return $this->render('@c975LPageEdit/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => $titleTranslated,
                'page' => $page,
                'pageContent' => $originalContent,
                'toolbar' => $toolbar,
                'type' => 'delete',
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE ARCHIVED
    /**
     * @Route("/pages/delete/archived/{page}",
     *      name="pageedit_delete_archived",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function deleteArchivedAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines paths
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . 'archived/' . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data
            extract($pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, $title, $page);
            $pageEditConfig = array(
                'action' => 'delete',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $pageEditService->deleteFile('archived/' . $page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Gets data for archived
            $lastHyphen = strrpos($page, '-') + 1;
            $userId = substr($page, $lastHyphen);
            $userManager = $this->container->get('fos_user.user_manager');
            $userArchived = $userManager->findUserBy(array('id' => $userId));
            if ($userArchived !== null) {
                $username = $userArchived->getFirstname() . ' ' . $userArchived->getLastname();
            } else {
                $username = $userId;
            }
            $datetime = \DateTime::createFromFormat('Ymd-His', substr($page, $lastHyphen - strlen($userId) - 15, 15));

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'delete-archived',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to delete the archived page
            return $this->render('@c975LPageEdit/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => $titleTranslated,
                'page' => $page,
                'pageContent' => $originalContent,
                'type' => 'archived',
                'toolbar' => $toolbar,
                'username' => $username,
                'datetime' => $datetime,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE DELETED
    /**
     * @Route("/pages/delete/deleted/{page}",
     *      name="pageedit_delete_deleted",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function deleteDeletedAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines paths
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . 'deleted/' . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Gets data
            extract($pageEditService->getData($filePath));

            //Defines form
            $pageEdit = new PageEdit($originalContent, $title, $page);
            $pageEditConfig = array(
                'action' => 'delete',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $pageEditService->deleteFile('deleted/' . $page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Defines data
            $datetime = \DateTime::createFromFormat('Ymd-His', date('Ymd-His', filemtime($filePath)));

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'delete-deleted',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to delete the deleted page
            return $this->render('@c975LPageEdit/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => $titleTranslated,
                'page' => $page,
                'pageContent' => $originalContent,
                'type' => 'deleted',
                'toolbar' => $toolbar,
                'datetime' => $datetime,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE REDIRECTED
    /**
     * @Route("/pages/delete/redirected/{page}",
     *      name="pageedit_delete_redirected",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     */
    public function deleteRedirectedAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines paths
            $pageEditService = $this->get(PageEditService::class);
            $folderPath = $pageEditService->getPagesFolder();
            $filePath = $folderPath . 'redirected/' . $page . '.html.twig';

            //Not existing page
            if (!is_file($filePath)) {
                throw $this->createNotFoundException();
            }

            //Defines form
            $pageEdit = new PageEdit($page, $page, $page);
            $pageEditConfig = array(
                'action' => 'delete',
            );
            $form = $this->createForm(PageEditType::class, $pageEdit, array('pageEditConfig' => $pageEditConfig));
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $pageEditService->deleteFile('redirected/' . $page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Defines data
            $datetime = \DateTime::createFromFormat('Ymd-His', date('Ymd-His', filemtime($filePath)));

            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'delete-redirected',
                'page' => $page,
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the form to delete the redirected page
            return $this->render('@c975LPageEdit/forms/delete.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => $page,
                'page' => $page,
                'pageContent' => ' --> ' . file_get_contents($filePath),
                'type' => 'redirected',
                'toolbar' => $toolbar,
                'datetime' => $datetime,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//LIST FOR URL LINKING
    /**
     * @Route("/pages/links",
     *      name="pageedit_links")
     * @Method({"GET", "HEAD"})
     */
    public function linksAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the list content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Creates structure in case it not exists
            $pageEditService = $this->get(PageEditService::class);
            $pageEditService->createFolders();

            //Gets the Finder
            $finder = new Finder();

            //Defines paths
            $folderPath = $pageEditService->getPagesFolder();
            $protectedFolderPath = $folderPath . 'protected';

            //Finds pages
            $finder
                ->files()
                ->in($folderPath)
                ->in($protectedFolderPath)
                ->depth('== 0')
                ->name('*.html.twig')
                ->sortByName()
                ;

            //Defines slug and title
            $pages = array();
            foreach ($finder as $file) {
                $slug = str_replace('.html.twig', '', $file->getRelativePathname());
                $title = $pageEditService->getTitle($file->getContents(), $slug);
                $titleTranslated = $pageEditService->getTitleTranslated($title);

                //Creates the array of available pages
                $pages[] = array(
                    'title' => $titleTranslated,
                    'value' => "{{ path('pageedit_display', {'page': '" . $slug . "'}) }}",
                );
            }

            //Returns the collection in json format
            return $this->json($pages);
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//UPLOAD PICTURES
    /**
     * @Route("/pages/upload/{page}",
     *      name="pageedit_upload",
     *      requirements={
     *          "page": "^([a-zA-Z0-9\-\/]+)"
     *      })
     * @Method({"POST"})
     */
    public function uploadAction(Request $request, $page)
    {
        //Creates structure in case it not exists
        $pageEditService = $this->get(PageEditService::class);
        $pageEditService->createFolders();

        //Defines path
        $folderPath = $pageEditService->getImagesFolder();

        //Checks origin - https://www.tinymce.com/docs/advanced/php-upload-handler/
        if ($request->server->get('HTTP_ORIGIN') !== null) {
            throw $this->createAccessDeniedException();
        }

        //Checks uploaded file
        $file = $request->files->get('file');
        if (is_uploaded_file($file)) {
            //Checks extension
            $extension = strtolower($file->guessExtension());
            if (in_array($extension, array('jpeg', 'jpg', 'png')) === true) {
                //Moves file
                $now = \DateTime::createFromFormat('U.u', microtime(true));
                if (strpos($page, '/') !== false) {
                    $page = substr($page, strrpos($page, '/') + 1);
                }
                $filename = $page . '-' . $now->format('Ymd-His-u') . '.' . $extension;
                move_uploaded_file($file->getRealPath(), $folderPath . $filename);

                //Respond to the successful upload with JSON
                $location = str_replace('/app_dev.php', '', $request->getUriForPath('/images/' . $this->getParameter('c975_l_page_edit.folderPages') . '/' . $filename));
                return $this->json(array('location' => $location));
            }
        }
    }

//SLUG
    /**
     * @Route("/pages/slug/{text}",
     *      name="pageedit_slug")
     * @Method({"POST"})
     */
    public function slugAction($text)
    {
        $pageEditService = $this->get(PageEditService::class);
        return $this->json(array('a' => $pageEditService->slugify($text)));
    }

//HELP
    /**
     * @Route("/pages/help",
     *      name="pageedit_help")
     * @Method({"GET", "HEAD"})
     */
    public function helpAction()
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the dashboard content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines toolbar
            $tools  = $this->renderView('@c975LPageEdit/tools.html.twig', array(
                'type' => 'help',
            ));
            $toolbar = $this->forward('c975L\ToolbarBundle\Controller\ToolbarController::displayAction', array(
                'tools'  => $tools,
                'dashboard'  => 'pageedit',
            ))->getContent();

            //Returns the help
            return $this->render('@c975LPageEdit/pages/help.html.twig', array(
                'toolbar' => $toolbar,
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }
}