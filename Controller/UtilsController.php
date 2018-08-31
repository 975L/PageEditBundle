<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UtilsController extends Controller
{
    private $pageEditService;

    public function __construct(\c975L\PageEditBundle\Service\PageEditService $pageEditService)
    {
        $this->pageEditService = $pageEditService;
    }

//REMOVE TRAILING SLASH
    /**
    * @Route("/{url}",
    *       name="remove_trailing_slash",
    *       requirements={"url": "^.*\/$"})
    * @Method({"GET", "HEAD"})
    */
    public function removeTrailingSlash(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);
        return $this->redirect($url);
    }

//LIST FOR URL LINKING
    /**
     * @Route("/pages/links",
     *      name="pageedit_links")
     * @Method({"GET", "HEAD"})
     */
    public function links(Request $request)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-links', null);

        //Returns the collection in json format
        return $this->json($this->pageEditService->getLinks());
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
    public function upload(Request $request, \Symfony\Component\Asset\Packages $assetsManager, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-upload', null);

        //Defines path
        $folderPath = $this->pageEditService->getImagesFolder();

        //Checks origin - https://www.tinymce.com/docs/advanced/php-upload-handler/
        if (null !== $request->server->get('HTTP_ORIGIN')) {
            throw $this->createAccessDeniedException();
        }

        //Checks uploaded file
        $file = $request->files->get('file');
        if (is_uploaded_file($file)) {
            //Checks extension
            $extension = strtolower($file->guessExtension());
            if (in_array($extension, array('jpeg', 'jpg', 'png'))) {
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
    public function slug($text)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-slug', null);

        return $this->json(array('a' => $this->pageEditService->slugify($text)));
    }
}
