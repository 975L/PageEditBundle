<?php

namespace c975L\PageEditBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditType;

class PageEditController extends Controller
{

    /**
     * @Route("/pages/edit/{page}",
     *      name = "975l_page_edit",
     *      requirements={
     *          "page": "^([a-z0-9\-]+)"
     *      })
     * )
     */
    public function pageEditAction(Request $request, $page)
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

            //Gets the template engine
            $parser = $this->get('templating.name_parser');
            $locator = $this->get('templating.locator');

            //Gets the skeleton
            $skeleton = file_get_contents($locator->locate($parser->parse('c975LPageEditBundle::skeleton.html.twig')));

            $startBlock = '{% block pageEdit %}';
            $endBlock = '{% endblock %}';

            $entryPoint = strpos($skeleton, $startBlock) + strlen($startBlock);
            $exitPoint = strpos($skeleton, $endBlock, $entryPoint);

            $startSkeleton = trim(substr($skeleton, 0, $entryPoint));
            $endSkeleton = trim(substr($skeleton, $exitPoint));

            //Gets the content
            $originalContent = null;
            if ($fs->exists($filePath)) {
                $fileContent = file_get_contents($filePath);

                $entryPoint = strpos($fileContent, $startBlock) + strlen($startBlock);
                $exitPoint = strpos($fileContent, $endBlock);

                $originalContent = trim(substr($fileContent, $entryPoint, $exitPoint - $entryPoint));
            }

            //Defines form
            $pageEdit = new PageEdit($originalContent);
            $form = $this->createForm(PageEditType::class, $pageEdit);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //Creates folders
                $archivesFolder = $folderPath . '/archives';
                $fs->mkdir($archivesFolder, 0770);

                //Gets data
                $newContent = $form->getData()->getContent();

                //Archives old file and writes new one if content has changed
                if ($originalContent !== null && $originalContent !== $newContent) {
                    $fs->rename($filePath, $archivesFolder . '/' . $page . '-' . date('Y-m-d-H-i-s') . '.html.twig');
                }

                //Writes new file
                $fs->dumpFile($filePath, $startSkeleton . "\n" . $newContent . "\n\t\t" . $endSkeleton);
                $fs->chmod($filePath, 0770);

                //Clears the cache otherwise changes will not be reflected
                $fs->remove($this->getParameter('kernel.cache_dir') . '/../prod/twig');

                //Redirects to the page
                return $this->redirectToRoute('975l_display_page', array(
                    'page' => $page,
                ));
            }

            //Returns the form to edit content
            return $this->render('@c975LPageEdit/pageEdit.html.twig', array(
                'form' => $form->createView(),
                'page' => $page,
                ));
        }

        //Access is denied
        throw $this->createAccessDeniedException();
    }
}
