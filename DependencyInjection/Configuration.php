<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('c975_l_page_edit');

        $rootNode
            ->children()
                ->scalarNode('folderPages')
                    ->defaultValue('pages')
                ->end()
                ->scalarNode('roleNeeded')
                    ->defaultValue('ROLE_ADMIN')
                ->end()
                ->scalarNode('sitemapBaseUrl')
                ->end()
                ->arrayNode('sitemapLanguages')
                    ->prototype('scalar')->end()
                    ->defaultValue(array())
                ->end()
                ->scalarNode('tinymceLanguage')
                    ->defaultNull()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
