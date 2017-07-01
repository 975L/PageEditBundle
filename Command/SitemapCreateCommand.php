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

        //Defines path
        $folderPath = $container->getParameter('kernel.root_dir') . '/Resources/views/' . $container->getParameter('c975_l_page_edit.folderPages');

        //Gets pages
        $finder
            ->files()
            ->in($folderPath)
            ->depth('== 0')
            ->name('*.html.twig')
            ->sortByName()
            ;

        //Defines data related to pages
        $pages = array();
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
            $pages[]= array(
                'url' => $container->getParameter('c975_l_page_edit.sitemapBaseUrl') . '/pages/' . str_replace('.html.twig', '', $file->getRelativePathname()),
                'lastModified' => date('Y-m-d', $file->getMTime()),
                'changeFrequency' => $changeFrequency,
                'priority' => $priority,
            );
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
