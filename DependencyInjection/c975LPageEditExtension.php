<?php
/*
 * (c) 2017: 975l <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class c975LPageEditExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('c975_l_page_edit.folderPages', $processedConfig['folderPages']);
        $container->setParameter('c975_l_page_edit.roleNeeded', $processedConfig['roleNeeded']);
        $container->setParameter('c975_l_page_edit.sitemapBaseUrl', $processedConfig['sitemapBaseUrl']);
        $container->setParameter('c975_l_page_edit.sitemapLanguages', $processedConfig['sitemapLanguages']);
        $container->setParameter('c975_l_page_edit.tinymceApiKey', $processedConfig['tinymceApiKey']);
        $container->setParameter('c975_l_page_edit.tinymceLanguage', $processedConfig['tinymceLanguage']);
        $container->setParameter('c975_l_page_edit.signoutRoute', $processedConfig['signoutRoute']);
        $container->setParameter('c975_l_page_edit.dashboardRoute', $processedConfig['dashboardRoute']);
    }
}
