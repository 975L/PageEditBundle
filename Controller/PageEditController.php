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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;

class PageEditController extends Controller
{
//DISPLAY
    /**
     * @Route("/pages/{page}",
     *      name="pageedit_display",
     *      requirements={
     *          "page": "^(?!new)([a-z0-9\-\_]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayAction($page)
    {
        $filePath = $this->getParameter('c975_l_page_edit.folderPages') . '/' . $page . '.html.twig';
        $fileDeletedPath = $this->getParameter('c975_l_page_edit.folderPages') . '/deleted/' . $page . '.html.twig';

        //Deleted page
        if ($this->get('templating')->exists($fileDeletedPath)) {
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
                //Writes file
                $this->writeFile($form->getData()->getSlug(), null, $form->getData());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $form->getData()->getSlug(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageNew.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.new_page'),
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//EDIT
    /**
     * @Route("/pages/edit/{page}",
     *      name="pageedit_edit",
     *      "requirements" ={
     *          "page": "^([a-z0-9\-\_]+)"
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
            preg_match('/pageedit_slug=\"(.*)\"/', $fileContent, $matches);
            $slug = $matches[1];

            //Defines form
            $pageEdit = new PageEdit('edit', $originalContent, $title, $slug);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Writes file
                $this->writeFile($page, $originalContent, $form->getData());

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $form->getData()->getSlug(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageEdit.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.modify') . ' "' . $title . '"',
                'slug' => $slug,
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }

//DELETE
    /**
     * @Route("/pages/delete/{page}",
     *      name="pageedit_delete",
     *      "requirements" ={
     *          "page": "^([a-z0-9\-\_]+)"
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
            preg_match('/pageedit_slug=\"(.*)\"/', $fileContent, $matches);
            $slug = $matches[1];

            //Defines form
            $pageEdit = new PageEdit('delete', $originalContent, $title, $slug);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Creates folders
                $deletedFolder = $folderPath . '/deleted';
                $fs->mkdir($deletedFolder, 0770);

                //Deletes file
                if ($fs->exists($filePath)) {
                    $fs->rename($filePath, $deletedFolder . '/' . $page . '.html.twig');
                }

                //Redirects to the page
                return $this->redirectToRoute('pageedit_display', array(
                    'page' => $pageEdit->getSlug(),
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/forms/pageDelete.html.twig', array(
                'form' => $form->createView(),
                'title' => $this->get('translator')->trans('label.delete') . ' "' . $title . '"',
                'slug' => $slug,
                'pageContent' => $originalContent,
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
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


    //Archives old file and writes new one
    public function writeFile($page, $originalContent, $formData)
    {
        //Gets the FileSystem
        $fs = new Filesystem();

        //Defines paths
        $folderPath = $this->getParameter('kernel.root_dir') . '/Resources/views/' . $this->getParameter('c975_l_page_edit.folderPages');
        $filePath = $folderPath . '/' . $page . '.html.twig';

        //Creates folders
        $archivesFolder = $folderPath . '/archives';
        $fs->mkdir($archivesFolder, 0770);

        //Gets the skeleton
        extract($this->getSkeleton());

        //Updates metadata
        $startSkeleton = preg_replace('/pageedit_title=\"(.*)\"/', 'pageedit_title="' . $formData->getTitle() . '"', $startSkeleton);
        $startSkeleton = preg_replace('/pageedit_slug=\"(.*)\"/', 'pageedit_slug="' . $formData->getSlug() . '"', $startSkeleton);

        //Concatenate skeleton + metadata + content
        $finalContent = $startSkeleton . "\n" . $formData->getContent() . "\n\t\t" . $endSkeleton;

        //Archives old file if content or metadata are different
        if ($fs->exists($filePath) && file_get_contents($filePath) !== $finalContent) {
            $fs->rename($filePath, $archivesFolder . '/' . $page . '-' . date('Y-m-d-H-i-s') . '.html.twig');
        }

        //Writes new file
        $newFilePath = $folderPath . '/' . $formData->getSlug() . '.html.twig';
        $fs->dumpFile($newFilePath, $finalContent);
        $fs->chmod($newFilePath, 0770);

        //Clears the cache otherwise changes will not be reflected
        $fs->remove($this->getParameter('kernel.cache_dir') . '/../prod/twig');
    }
}
