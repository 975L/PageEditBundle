<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service\Slug;

/**
 * Interface to be called for DI for PageEdit Slug related services
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
interface PageEditSlugInterface
{
    /**
     * Checks if slug already exists
     * @return bool
     */
    public function exists(string $slug);

    /**
     * Checks unicity of slugged text against pages collection
     * @return string
     */
    public function slugify(string $text, bool $keepSlashes = false);
}
