<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Controller;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Service\File\PageEditFileInterface;
use c975L\PageEditBundle\Service\PageEditServiceInterface;
use c975L\PageEditBundle\Service\Slug\PageEditSlugInterface;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * UtilsController class
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class UtilsController extends AbstractController
{
//LIST FOR URL LINKING
    /**
     * Provides a list of pages to link to in Tinymce
     * @return JSON
     * @throws AccessDeniedException
     */
    #[Route(
        '/pageedit/links',
        name: 'pageedit_links',
        methods: ['GET']
    )]
    public function links(PageEditServiceInterface $pageEditService)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-links', null);

        //Returns the collection in json format
        return $this->json($pageEditService->getLinks());
    }

//UPLOAD IMAGE
    /**
     * Uploads the image defined
     * @throws AccessDeniedException
     */
    #[Route(
        '/pageedit/upload/{page}',
        name: 'pageedit_upload',
        requirements: [
            'page' => '^([a-zA-Z0-9\-\/]+)'
        ],
        defaults: ['page' => 'new'],
        methods: ['POST']
    )]
    public function upload(Request $request, ConfigServiceInterface $configService, PageEditFileInterface $pageEditFile, $page): \JSON|false
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-upload', null);

        //Checks uploaded file
        $file = $request->files->get('file');
        if (is_uploaded_file($file)) {
            //Checks extension
            $extension = strtolower((string) $file->guessExtension());
            if (in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
                //Moves file
                if (str_contains((string) $page, '/')) {
                    $page = substr((string) $page, strrpos((string) $page, '/') + 1);
                }
                $folderPath = $pageEditFile->getImagesFolder();
                $now = DateTime::createFromFormat('U.u', microtime(true));
                $filename = $page . '-' . $now->format('Ymd-His-u') . '.' . $extension;
                move_uploaded_file($file->getRealPath(), $folderPath . $filename);

                //Returns JSON to the successful upload
                $location = str_replace('/app_dev.php', '', $request->getUriForPath('/images/' . $configService->getParameter('c975LPageEdit.folderPages') . '/' . $filename));

                return $this->json(['location' => $location]);
            }
        }

        return false;
    }

//SLUG
    /**
     * Slugs the provided text
     * @return JSON
     * @throws AccessDeniedException
     */
    #[Route(
        '/pageedit/slug/{text}',
        name: 'pageedit_slug',
        methods: ['POST']
    )]
    public function slug(PageEditSlugInterface $pageEditSlug, $text)
    {
        $this->denyAccessUnlessGranted('c975LPageEdit-slug', null);

        return $this->json(['a' => $pageEditSlug->slugify($text, true)]);
    }
}