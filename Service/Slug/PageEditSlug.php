<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service\Slug;

use c975L\PageEditBundle\Service\File\PageEditFileInterface;
use Cocur\Slugify\Slugify;

/**
 * Services related to PageEdit Slug
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditSlug implements PageEditSlugInterface
{
    /**
     * Stores PageEditFileInterface
     * @var PageEditFileInterface
     */
    private $pageEditFile;

    public function __construct(PageEditFileInterface $pageEditFile)
    {
        $this->pageEditFile = $pageEditFile;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $slug)
    {
        $pages = $this->pageEditFile->getPages();

        foreach ($pages as $page) {
            if ($slug === str_replace(array('protected/', '.html.twig'), '', $page->getRelativePathname())) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function slugify(string $text, bool $keepSlashes = false)
    {
        $slugify = new Slugify();
        if ($keepSlashes) {
            $slugify->addRule('/', '-thereisaslash-');
        }
        $slug = str_replace('-thereisaslash-', '/', $slugify->slugify($text));

        //Checks unicity of slug
        $finalSlug = $slug;
        $slugExists = true;
        $i = 1;
        do {
            $slugExists = $this->exists($finalSlug);
            if ($slugExists) {
                $finalSlug = $slug . '-' . $i++;
            }
        } while (false !== $slugExists);

        return $finalSlug;
    }
}
