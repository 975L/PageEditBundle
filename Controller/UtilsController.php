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
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Service\PageEditServiceInterface;
use c975L\PageEditBundle\Service\File\PageEditFileInterface;
use c975L\PageEditBundle\Service\Slug\PageEditSlugInterface;

/**
 * UtilsController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UtilsController extends Controller
{
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
     * Provides a list of pages to link to in Tinymce
     * @return JSON
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/links",
     *      name="pageedit_links")
     * @Method({"GET", "HEAD"})
     */
    public function links(PageEditServiceInterface $pageEditService)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-links', null);

        //Returns the collection in json format
        return $this->json($pageEditService->getLinks());
    }

//UPLOAD IMAGE
    /**
     * Uploads the image defined
     * @return JSON|false
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/upload/{page}",
     *      name="pageedit_upload",
     *      requirements={"page": "^[a-zA-Z0-9\-\/]+"},
     *      defaults={"page": "new"})
     * @Method({"POST"})
     */
    public function upload(Request $request, ConfigServiceInterface $configService, PageEditFileInterface $pageEditFile, $page)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-upload', null);

        //Checks uploaded file
        $file = $request->files->get('file');
        if (is_uploaded_file($file)) {
            //Checks extension
            $extension = strtolower($file->guessExtension());
            if (in_array($extension, array('jpeg', 'jpg', 'png', 'gif'))) {
                //Moves file
                if (false !== strpos($page, '/')) {
                    $page = substr($page, strrpos($page, '/') + 1);
                }
                $folderPath = $pageEditFile->getImagesFolder();
                $now = \DateTime::createFromFormat('U.u', microtime(true));
                $filename = $page . '-' . $now->format('Ymd-His-u') . '.' . $extension;
                move_uploaded_file($file->getRealPath(), $folderPath . $filename);

                //Returns JSON to the successful upload
                $location = str_replace('/app_dev.php', '', $request->getUriForPath('/images/' . $configService->getParameter('c975LPageEdit.folderPages') . '/' . $filename));

                return $this->json(array('location' => $location));
            }
        }

        return false;
    }

//SLUG
    /**
     * Slugs the provided text
     * @return JSON
     * @throws AccessDeniedException
     *
     * @Route("/pageedit/slug/{text}",
     *      name="pageedit_slug")
     * @Method({"POST"})
     */
    public function slug(PageEditSlugInterface $pageEditSlug, $text)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-slug', null);

        return $this->json(array('a' => $pageEditSlug->slugify($text, true)));
    }
}
