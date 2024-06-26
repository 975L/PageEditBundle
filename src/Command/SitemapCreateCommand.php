<?php
namespace c975L\PageEditBundle\Command;

use Twig\Environment;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Service\PageEditServiceInterface;

/**
 * Console command to create sitemap of pages, executed with 'pageedit:createSitemap'
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
#[AsCommand(
    name: 'pageedit:createSitemap',
    description: 'Creates the sitemap of pages managed viaPageEdit'
)]
class SitemapCreateCommand extends Command
{
    public function __construct(
        /**
         * Stores ConfigServiceInterface
         */
        private readonly ConfigServiceInterface $configService,
        /**
         * Stores PageEditServiceInterface
         */
        private readonly PageEditServiceInterface $pageEditService,
        /**
         * Stores Environment
         */
        private readonly Environment $environment
    )
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //Creates structure in case it not exists
        $fs = new Filesystem();
        $root = $this->configService->getContainerParameter('kernel.project_dir');
        $folderPages = $this->configService->getParameter('c975LPageEdit.folderPages');
        $templatesFolder = '/templates/';
        $folderPath = $root . $templatesFolder . $folderPages;
        $protectedFolderPath = $root . $templatesFolder . $folderPages . '/protected';
        $fs->mkdir([$folderPath, $protectedFolderPath] , 0770);

        //Gets pages
        $finder = new Finder();
        $finder
            ->files()
            ->in($folderPath)
            ->in($protectedFolderPath)
            ->depth('== 0')
            ->name('*.html.twig')
            ->sortByName()
        ;

        //Defines data related to pages
        $pages = [];
        $languages = $this->configService->getParameter('c975LPageEdit.sitemapLanguages');

        $urlRoot = $this->configService->getParameter('c975LPageEdit.sitemapBaseUrl');
        foreach ($finder as $file) {
            $fileContent = $file->getContents();
            $changeFrequency = $this->pageEditService->getChangeFrequency($fileContent);
            $priority = $this->pageEditService->getPriority($fileContent);

            //Defines data
            if (!empty($languages)) {
                foreach ($languages as $language) {
                    $url = $urlRoot;
                    $url .= '/' . $language;
                    $url .= '/pages/' . str_replace('.html.twig', '', $file->getRelativePathname());
                    $url = $url === $urlRoot . '/' . $language . "/pages/home" ? $urlRoot . '/' . $language . '/' : $url;
                    $pages[]= ['url' => $url, 'lastModified' => date('Y-m-d', $file->getMTime()), 'changeFrequency' => $changeFrequency, 'priority' => $priority];
                }
            } else {
                $url = $urlRoot;
                $url .= '/pages/' . str_replace('.html.twig', '', $file->getRelativePathname());
                $url = $url === $urlRoot . "/pages/home" ? $urlRoot : $url;
                $pages[]= ['url' => $url, 'lastModified' => date('Y-m-d', $file->getMTime()), 'changeFrequency' => $changeFrequency, 'priority' => $priority];
            }
        }

        //Writes file
        $sitemapContent = $this->environment->render('@c975LPageEdit/sitemap.xml.twig', ['pages' => $pages]);
        $sitemapFile = $root . '/public/sitemap-' . $folderPages . '.xml';
        file_put_contents($sitemapFile, $sitemapContent);

        //Ouputs message
        $output->writeln('Sitemap created!');

        if (str_starts_with(Kernel::VERSION, '5')) {
            return Command::SUCCESS;
        }

        return Command::SUCCESS;
    }
}
