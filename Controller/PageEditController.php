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
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Cocur\Slugify\Slugify;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;

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
            $this->createFolders();

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
                preg_match('/pageedit_title=\"(.*)\"/', $file->getContents(), $matches);
                if (!empty($matches)) $title = $matches[1];
                else {
                    //Title is using Twig code to translate it
                    preg_match('/pageedit_title=(.*)\%\}/', $file->getContents(), $matches);
                    if (!empty($matches)) $title = trim($matches[1]);
                    else $title = $this->get('translator')->trans('label.title_not_found', array(), 'pageedit') . ' (' . $slug . ')';
                }

                //Adds page to array
                $pages[] = array(
                    'slug' => $slug,
                    'title' => $title,
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
                'title' => $this->get('translator')->trans('label.dashboard', array(), 'pageedit'),
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'dashboard')),
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
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'protected')),
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
            $toolbar = $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'display', 'page' => $page));
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
                $slug = $this->slugify($form->getData()->getSlug());

                //Writes file
                $this->writeFile($slug, null, $form->getData(), $user->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageNew.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.new_page', array(), 'pageedit'),
                'page' => 'new',
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'new')),
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
            preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);
            if (!empty($matches)) $title = $matches[1];
            else {
                //Title is using Twig code to translate it
                preg_match('/pageedit_title=(.*)\%\}/', $fileContent, $matches);
                if (!empty($matches)) $title = '{{ ' . trim($matches[1]) . ' }}';
                else $title = $page;
            }

            //Gets changeFrequency
            preg_match('/pageedit_changeFrequency=\"(.*)\"/', $fileContent, $matches);
            if (!empty($matches)) $changeFrequency = $matches[1];
            else $changeFrequency = 'weekly';

            //Gets priority
            preg_match('/pageedit_priority=\"(.*)\"/', $fileContent, $matches);
            if (!empty($matches)) $priority = $matches[1];
            else $priority = '8';

            //Defines form
            $pageEdit = new PageEdit('edit', $originalContent, $title, $page, $changeFrequency, $priority);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $slug = $this->slugify($form->getData()->getSlug());

                //Archives and redirects the file if title (then slug) has changed
                if ($slug != $page) {
                    $this->archiveFile($page, $user->getId());
                    $this->redirectFile($page, $slug);
                }

                //Writes file
                $this->writeFile($slug, $originalContent, $form->getData(), $user->getId());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $slug,
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageEdit.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.modify', array(), 'pageedit') . ' "' . $title . '"',
                'page' => $page,
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'edit', 'page' => $page)),
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
            preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);
            if (!empty($matches)) $title = $matches[1];
            else {
                //Title is using Twig code to translate it
                preg_match('/pageedit_title=(.*)\%\}/', $fileContent, $matches);
                if (!empty($matches)) $title = '{{ ' . trim($matches[1]) . ' }}';
                else $title = $page;
            }

            //Defines form
            $pageEdit = new PageEdit('delete', $originalContent, $title, $page);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Deletes file
                $this->deleteFile($page, false);

                //Redirects to the page which will be HTTP 410
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $page,
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageDelete.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.delete', array(), 'pageedit') . ' "' . $title . '"',
                'page' => $page,
                'pageContent' => $originalContent,
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'delete', 'page' => $page)),
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
            $this->createFolders();

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
                preg_match('/pageedit_title=\"(.*)\"/', $file->getContents(), $matches);
                if (!empty($matches)) $title = $matches[1];
                else {
                    //Title is using Twig code to translate it
                    preg_match('/pageedit_title=(.*)\%\}/', $file->getContents(), $matches);
                    if (!empty($matches)) $title = trim($matches[1]);
                    else $title = $this->get('translator')->trans('label.title_not_found', array(), 'pageedit') . ' (' . $slug . ')';
                }

                //Creates the array of available pages
                $pages[] = array(
                    'title' => $title,
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
        $this->createFolders();

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
        return $this->json(array('a' => $this->slugify($text)));
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
                'title' => $this->get('translator')->trans('label.help', array(), 'pageedit'),
                'toolbar' => $this->renderView('@c975LPageEdit/toolbar.html.twig', array('type' => 'help')),
            ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }


//FUNCTIONS
    //Creates the folders needed by the Bundle
    public function createFolders()
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines paths
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $archivedFolder = $folderPath . '/archived';
        $deletedFolder = $folderPath . '/deleted';
        $protectedFolderPath = $folderPath . '/protected';
        $redirectedFolder = $folderPath . '/redirected';
        $imageFolderPath = $this->getParameter('kernel.root_dir') . '/../web/images/' . $this->getParameter('c975_l_page_edit.folderPages');

        //Creates folders
        $fs->mkdir($archivedFolder, 0770);
        $fs->mkdir($deletedFolder, 0770);
        $fs->mkdir($protectedFolderPath, 0770);
        $fs->mkdir($redirectedFolder, 0770);
        $fs->mkdir($imageFolderPath, 0770);
    }

    //Gets the start and end of the skeleton
    public function getSkeleton()
    {
        $parser = $this->get('templating.name_parser');
        $locator = $this->get('templating.locator');

        $skeleton = file_get_contents($locator->locate($parser->parse('c975LPageEditBundle::skeleton.html.twig')));

        $startBlock = '{% block pageEdit %}';
        $endBlock = '{% endblock %}';

        $entryPoint = strpos($skeleton, $startBlock) + strlen($startBlock);
        $exitPoint = strpos($skeleton, $endBlock, $entryPoint);

        return array (
            'startSkeleton' => trim(substr($skeleton, 0, $entryPoint)),
            'endSkeleton' => trim(substr($skeleton, $exitPoint))
        );
    }


    //Slugify function - https://github.com/cocur/slugify
    public function slugify($text)
    {
        $slugify = new Slugify();
        return $slugify->slugify($text);
    }


    //Archives file
    public function archiveFile($page, $userId)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines paths
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $archivedFolder = $folderPath . '/archived';
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Archives file
        if ($fs->exists($filePath)) {
            $fs->rename($filePath, $archivedFolder . '/' . $page . '-' . date('Ymd-His-') . $userId . '.html.twig');
        }
    }


    //Creates the redirection file
    public function redirectFile($page, $slug)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $redirectedFolder = $folderPath . '/redirected';

        //Sets the redirection
        $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
        $fs->dumpFile($redirectedFilePath, $slug);
    }


    //Moves to deleted/redirected folder the requested file
    public function deleteFile($page, $redirect, $slug = null)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';
        $deletedFolder = $folderPath . '/deleted';
        $redirectedFolder = $folderPath . '/redirected';

        //Sets the redirection
        if ($redirect === true) {
            $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
            $fs->dumpFile($redirectedFilePath, $slug);
        }

        //Deletes file
        if ($fs->exists($filePath)) {
            $fs->rename($filePath, $deletedFolder . '/' . $page . '.html.twig');
        }
    }


    //Archives old file and writes new one
    public function writeFile($page, $originalContent, $formData, $userId)
    {
        //Creates structure in case it not exists
        $this->createFolders();

        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Gets the skeleton
        extract($this->getSkeleton());

        //Gets title
        $title = $formData->getTitle();

        //Title is using Twig code to translate it
        if (strpos($title, '{{') === 0)
            $title = trim(str_replace(array('{{', '}}'), '', $title));
        //Title is text
        else
            $title = '"' . $title . '"';

        //Updates metadata
        $startSkeleton = preg_replace('/pageedit_title=\"(.*)\"/', 'pageedit_title=' . $title, $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_changeFrequency=\"(.*)\"/', 'pageedit_changeFrequency="' . $formData->getChangeFrequency() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_priority=\"(.*)\"/', 'pageedit_priority="' . $formData->getPriority() . '"', $startSkeleton);

        //Concatenate skeleton + metadata + content
        $finalContent = $startSkeleton . "\n" . $formData->getContent() . "\n\t\t" . $endSkeleton;

        //Archives old file if content or metadata are different
        if ($fs->exists($filePath) && file_get_contents($filePath) !== $finalContent) {
            $this->archiveFile($page, $userId);
        }

        //Writes new file
        $newFilePath = $folderPath . '/' . $page . '.html.twig';
        $fs->dumpFile($newFilePath, $finalContent);
        $fs->chmod($newFilePath, 0770);

        //Clears the cache otherwise changes will not be reflected
        $fs->remove($this->getParameter('kernel.cache_dir') . '/../prod/twig');
    }
}
