<?php

namespace c975L\PageEditBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller
{

    /**
     * @Route("/pages/{page}",
     *      name="975l_display_page",
     *      requirements={
     *          "page": "^([a-z0-9\-]+)"
     *      })
     * @Method({"GET", "HEAD"})
     */
    public function displayPageAction($page)
    {
        $filePath = $this->getParameter('c975_l_page_edit.folderPages') . '/' . $page . '.html.twig';

        if (!$this->get('templating')->exists($filePath)) {
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
}
