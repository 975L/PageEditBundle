<?php
/*
 * (c) 2017: 975l <contact@975l.com>
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;
use c975L\PageEditBundle\Service\PageEditService;

class PageEditController extends Controller
{
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
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
            $protectedFolderPath = $folderPath . '/protected';

            //Gets pages
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

                //Adds page to array
                $pages[] = array(
                    'slug' => $slug,
                    'title' => $titleTranslated,
                    'protected' => strpos($file->getPath(), 'protected') !== false ? true : false,
                );
            }
            sort($pages);

            //Pagination
            $paginator  = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $pages,
                $request->query->getInt('p', 1),
                15
            );

            //Returns the dashboard
            return $this->render('@c975LPageEdit/pages/dashboard.html.twig', array(
                'pages' => $pagination,
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                    'type' => 'dashboard',
                    'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                    'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
                )),
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
     *          "page": "^(?!dashboard|help|links|new|upload)([a-z0-9\-]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayAction($page)
    {
        $filePath = $this->getParameter('c975_l_page_edit.folderPages') . '/' . $page . '.html.twig';
        $fileRedirectedPath = $this->getParameter('c975_l_page_edit.folderPages') . '/redirected/' . $page . '.html.twig';
        $fileDeletedPath = $this->getParameter('c975_l_page_edit.folderPages') . '/deleted/' . $page . '.html.twig';
        $fileProtectedPath = $this->getParameter('c975_l_page_edit.folderPages') . '/protected/' . $page . '.html.twig';

        //Redirected page
        if ($this->get('templating')->exists($fileRedirectedPath)) {
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/';
            return $this->redirectToRoute('pageedit_display', array(
                'page' => file_get_contents($folderPath . $fileRedirectedPath),
            ));
        }
        //Protected page
        elseif ($this->get('templating')->exists($fileProtectedPath)) {
            return $this->render($fileProtectedPath, array(
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                    'type' => 'protected',
                    'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                    'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
                )),
            ));
        }
        //Deleted page
        elseif ($this->get('templating')->exists($fileDeletedPath)) {
            throw new HttpException(410);
        }
        //Not existing page
        elseif (!$this->get('templating')->exists($filePath)) {
            throw $this->createNotFoundException();
        }

        //Gets the user
        $user = $this->getUser();

        //Adds toolbar if rights are ok
        $toolbar = null;
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            $toolbar = $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                'type' => 'display',
                'page' => $page,
                'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
            ));
        }

        return $this->render($filePath, array(
            'toolbar' => $toolbar,
        ));
    }

//NEW
    /**
     * @Route("/pages/new",
     *      name="pageedit_new")
     * )
     */
    public function newAction(Request $request)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Defines form
            $pageEdit = new PageEdit('new');
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $pageEditService = $this->get(PageEditService::class);
                $slug = $pageEditService->slugify($form->getData()->getSlug());

                //Writes file
                $pageEditService->writeFile($slug, null, $form->getData(), $user->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageNew.html.twig', array(
                'form' => $form->createView(),
                'page' => 'new',
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                    'type' => 'new',
                    'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                    'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
                )),
                'tinymceApiKey' => $this->container->hasParameter('tinymceApiKey') ? $this->getParameter('tinymceApiKey') : null,
                'tinymceLanguage' => $this->getParameter('c975_l_page_edit.tinymceLanguage'),
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//EDIT
    /**
     * @Route("/pages/edit/{page}",
     *      name="pageedit_edit",
     *      requirements={
     *          "page": "^([a-z0-9\-]+)"
     *      })
     * )
     */
    public function editAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Gets the FileSystem
            $fs = new Filesystem();

            //Defines path
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
            $filePath = $folderPath . '/' . $page . '.html.twig';

            //Gets the content
            $originalContent = null;
            if ($fs->exists($filePath)) {
                $fileContent = file_get_contents($filePath);

                $startBlock = '{% block pageEdit %}';
                $endBlock = '{% endblock %}';
                $entryPoint = strpos($fileContent, $startBlock) + strlen($startBlock);
                $exitPoint = strpos($fileContent, $endBlock, $entryPoint);

                $originalContent = trim(substr($fileContent, $entryPoint, $exitPoint - $entryPoint));
            }

            //Gets title
            $pageEditService = $this->get(PageEditService::class);
            $title = $pageEditService->getTitle($fileContent, $page);
            $titleTranslated = $pageEditService->getTitleTranslated($title);

            //Gets changeFrequency
            $changeFrequency = $pageEditService->getChangeFrequency($fileContent);

            //Gets priority
            $priority = $pageEditService->getPriority($fileContent);

            //Defines form
            $pageEdit = new PageEdit('edit', $originalContent, $title, $page, $changeFrequency, $priority);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $pageEditService = $this->get(PageEditService::class);
                $slug = $pageEditService->slugify($form->getData()->getSlug());

                //Archives and redirects the file if title (then slug) has changed
                if ($slug != $page) {
                    $pageEditService->archiveFile($page, $user->getId());
                    $pageEditService->redirectFile($page, $slug);
                }

                //Writes file
                $pageEditService->writeFile($slug, $originalContent, $form->getData(), $user->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageEdit.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => str_replace('\"', '"', $titleTranslated),
                'page' => $page,
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                    'type' => 'edit',
                    'page' => $page,
                    'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                    'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
                )),
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
     *          "page": "^([a-z0-9\-]+)"
     *      })
     * )
     */
    public function deleteAction(Request $request, $page)
    {
        //Gets the user
        $user = $this->getUser();

        //Defines the form
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Gets the FileSystem
            $fs = new Filesystem();

            //Defines paths
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
            $filePath = $folderPath . '/' . $page . '.html.twig';

            //Gets the content
            $originalContent = null;
            if ($fs->exists($filePath)) {
                $fileContent = file_get_contents($filePath);

                $startBlock = '{% block pageEdit %}';
                $endBlock = '{% endblock %}';
                $entryPoint = strpos($fileContent, $startBlock) + strlen($startBlock);
                $exitPoint = strpos($fileContent, $endBlock, $entryPoint);

                $originalContent = trim(substr($fileContent, $entryPoint, $exitPoint - $entryPoint));
            }

            //Gets title
            $pageEditService = $this->get(PageEditService::class);
            $title = $pageEditService->getTitle($fileContent, $page);
            $titleTranslated = $pageEditService->getTitleTranslated($title);

            //Defines form
            $pageEdit = new PageEdit('delete', $originalContent, $title, $page);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $pageEditService->deleteFile($page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_dashboard');
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageDelete.html.twig', array(
                'form' => $form->createView(),
                'pageTitle' => $titleTranslated,
                'page' => $page,
                'pageContent' => $originalContent,
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                    'type' => 'delete',
                    'page' => $page,
                    'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                    'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
                )),
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
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
            $protectedFolderPath = $folderPath . '/protected';

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
                    'value' => "{{path('pageedit_display',{'page':'" . $slug . "'})}}",
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
     *          "page": "^([a-z0-9\-]+)"
     *      })
     * @Method({"POST"})
     */
    public function uploadAction(Request $request, $page)
    {
        //Creates structure in case it not exists
        $pageEditService = $this->get(PageEditService::class);
        $pageEditService->createFolders();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/../web/images/' . $this->getParameter('c975_l_page_edit.folderPages');

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
                $filename = $page . '-' . $now->format('Ymd-His-u') . '.' . $extension;
                move_uploaded_file($file->getRealPath(), $folderPath . '/' . $filename);

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
            //Returns the help
            return $this->render('@c975LPageEdit/pages/help.html.twig', array(
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array(
                    'type' => 'help',
                    'dashboardRoute' => $this->getParameter('c975_l_page_edit.dashboardRoute'),
                    'signoutRoute' => $this->getParameter('c975_l_page_edit.signoutRoute'),
                )),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }
}