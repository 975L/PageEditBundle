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
    public function dashboardAction()
    {
        //Gets the user
        $user = $this->getUser();

        //Returns the dashboard content
        if ($user !== null && $this->get('security.authorization_checker')->isGranted($this->getParameter('c975_l_page_edit.roleNeeded'))) {
            //Gets the Finder
            $finder = new Finder();

            //Defines path
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');

            //Finds pages
            $finder
                ->files()
                ->in($folderPath)
                ->depth('== 0')
                ->name('*.html.twig')
                ->sortByName()
                ;

            //Defines slug and title
            $pages = array();
            foreach ($finder as $file) {
                $slug = str_replace('.html.twig', '', $file->getRelativePathname());
                preg_match('/pageedit_title=\"(.*)\"/', $file->getContents(), $matches);
                $pages[$slug] = $matches[1];
            }

            //Returns the dashboard
            return $this->render('@c975LPageEdit/pages/dashboard.html.twig', array(
                'pages' => $pages,
                'title' => $this->get('translator')->trans('label.dashboard', array(), 'pageedit'),
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
     *          "page": "^(?!dashboard|help|new)([a-z0-9\-]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayAction($page)
    {
        $filePath = $this->getParameter('c975_l_page_edit.folderPages') . '/' . $page . '.html.twig';
        $fileRedirectedPath = $this->getParameter('c975_l_page_edit.folderPages') . '/redirected/' . $page . '.html.twig';
        $fileDeletedPath = $this->getParameter('c975_l_page_edit.folderPages') . '/deleted/' . $page . '.html.twig';

        //Redirected page
        if ($this->get('templating')->exists($fileRedirectedPath)) {
            $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/';
            return $this->redirectToRoute('pageedit_display', array(
                'page' => file_get_contents($folderPath . $fileRedirectedPath),
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
            $toolbar = $this->renderView('@c975LPageEdit/toolbar.html.twig', array('page' => $page));
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
                $slug = $this->slugify($form->getData()->getTitle());
                $pageEdit->setSlug($slug);

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

            //Gets the metadata
            preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);
            $title = $matches[1];

            //Defines form
            $pageEdit = new PageEdit('edit', $originalContent, $title, $page);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Gets slug
                $slug = $this->slugify($form->getData()->getTitle());

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

            //Gets the metadata
            preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);
            $title = $matches[1];

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
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
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
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
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


//FUNCTIONS
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


    //Slugify function - https://gist.github.com/umidjons/9757010
    public function slugify($text)
    {
        $slug = preg_replace('/\s\s+/', ' ', trim(mb_strtolower($text)));
        $slug = str_replace(array(',',';','.',':','·','(',')','[',']','{','}','+','\\','/','#','~','&','$','£','µ','@','=','<','>','$','^','°','|'),'', $slug);
        $slug = str_replace(array('œ','Œ'), 'oe', $slug);
        $slug = str_replace(array('æ','Æ'), 'ae', $slug);
        $slug = str_replace(array(' '), '-', $slug);
        $search =  array('ª','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','º','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ŭ','µ','ý','ÿ','ß','æ','œ','_','"',"'");
        $replace = array('a','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','o','u','u','u','u','u','u','y','y','s','a','o','-','-','-');
        $slug = str_replace($search, $replace, $slug);
        $slug = str_replace(array('--', '--', '--'), '-', $slug);
        return $slug;
    }


    //Archives file
    public function archiveFile($page, $userId)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Creates folder
        $archivedFolder = $folderPath . '/archived';
        $fs->mkdir($archivedFolder, 0770);

        //Archives file
        if ($fs->exists($filePath)) {
            $fs->rename($filePath, $archivedFolder . '/' . $page . '-' . date('Ymd-His-') . $userId . '.html.twig');
        }
    }


    //Creates the redirection file
    public function redirectFile($page, $slug)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');

        //Creates folder
        $redirectedFolder = $folderPath . '/redirected';
        $fs->mkdir($redirectedFolder, 0770);

        //Sets the redirection
        $redirectedFilePath = $redirectedFolder . '/' . $page . '.html.twig';
        $fs->dumpFile($redirectedFilePath, $slug);
    }


    //Moves to deleted/redirected folder the requested file
    public function deleteFile($page, $redirect, $slug = null)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Creates folders
        $deletedFolder = $folderPath . '/deleted';
        $fs->mkdir($deletedFolder, 0770);
        $redirectedFolder = $folderPath . '/redirected';
        $fs->mkdir($redirectedFolder, 0770);

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
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines path
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Gets the skeleton
        extract($this->getSkeleton());

        //Updates metadata
        $startSkeleton = preg_replace('/pageedit_title=\"(.*)\"/', 'pageedit_title="' . $formData->getTitle() . '"', $startSkeleton);

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
