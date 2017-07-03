<?php
namespace c975L\PageEditBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class SitemapCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pageedit:createSitemap')
            ->setDescription('Creates the sitemap of pages managed via PageEdit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Gets the container
        $container = $this->getContainer();

        //Gets the Finder
        $finder = new Finder();

        //Defines paths
        $folderPath = $container->getParameter('kernel.root_dir') . '/Resources/views/' . $container->getParameter('c975_l_page_edit.folderPages');
        $protectedFolderPath = $container->getParameter('kernel.root_dir') . '/Resources/views/' . $container->getParameter('c975_l_page_edit.folderPages') . '/protected';

        //Creates structure in case it not exists
        $fs = new Filesystem();
        $fs->mkdir($folderPath, 0770);
        $fs->mkdir($protectedFolderPath, 0770);

        //Gets pages
        $finder
            ->files()
            ->in($folderPath)
            ->in($protectedFolderPath)
            ->depth('== 0')
            ->name('*.html.twig')
            ->sortByName()
            ;

        //Defines data related to pages
        $pages = array();
        $languages = $container->getParameter('c975_l_page_edit.sitemapLanguages');

        foreach ($finder as $file) {
            //Gets changeFrequency
            preg_match('/pageedit_changeFrequency=\"(.*)\"/', $file->getContents(), $matches);
            if (!empty($matches)) $changeFrequency = $matches[1];
            else $changeFrequency = 'weekly';

            //Gets priority
            preg_match('/pageedit_priority=\"(.*)\"/', $file->getContents(), $matches);
            if (!empty($matches)) $priority = $matches[1] / 10;
            else $priority = '0.8';

            //Defines data
            if (!empty($languages)) {
                foreach($languages as $language) {
                    $url = $container->getParameter('c975_l_page_edit.sitemapBaseUrl');
                    $url .= $language != '' ? '/' . $language : '';
                    $url .= '/pages/' . str_replace('.html.twig', '', $file->getRelativePathname());
                    $pages[]= array(
                        'url' => $url,
                        'lastModified' => date('Y-m-d', $file->getMTime()),
                        'changeFrequency' => $changeFrequency,
                        'priority' => $priority,
                    );
                }
            }
            else {
                $url = $container->getParameter('c975_l_page_edit.sitemapBaseUrl');
                $url .= '/pages/' . str_replace('.html.twig', '', $file->getRelativePathname());
                $pages[]= array(
                    'url' => $url,
                    'lastModified' => date('Y-m-d', $file->getMTime()),
                    'changeFrequency' => $changeFrequency,
                    'priority' => $priority,
                );
            }
        }

        //Writes file
        $sitemapContent = $container->get('templating')->render(
            '@c975LPageEdit/sitemap.xml.twig',
            array('pages' => $pages)
        );
        $sitemapFile = $container->getParameter('kernel.root_dir') . '/../web/sitemap-' . $container->getParameter('c975_l_page_edit.folderPages') . '.xml';
        file_put_contents($sitemapFile, $sitemapContent);

        //Ouputs message
        $output->writeln('Sitemap created!');
    }
}
