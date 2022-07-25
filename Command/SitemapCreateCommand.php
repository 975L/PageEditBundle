<?php
namespace c975L\PageEditBundle\Command;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Service\PageEditServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Environment;

/**
 * Console command to create sitemap of pages, executed with 'pageedit:createSitemap'
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
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

    protected function configure()
    {
        $this
            ->setName('pageedit:createSitemap')
            ->setDescription('Creates the sitemap of pages managed via PageEdit')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Creates structure in case it not exists
        $fs = new Filesystem();
        $root = $this->configService->getContainerParameter('kernel.project_dir');
        $folderPages = $this->configService->getParameter('c975LPageEdit.folderPages');
        $templatesFolder = str_starts_with(Kernel::VERSION, '3') ? '/Resources/views/' : '/templates/';
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
                    $pages[]= ['url' => $url, 'lastModified' => date('Y-m-d', $file->getMTime()), 'changeFrequency' => $changeFrequency, 'priority' => $priority];
                }
            } else {
                $url = $urlRoot;
                $url .= '/pages/' . str_replace('.html.twig', '', $file->getRelativePathname());
                $pages[]= ['url' => $url, 'lastModified' => date('Y-m-d', $file->getMTime()), 'changeFrequency' => $changeFrequency, 'priority' => $priority];
            }
        }

        //Writes file
        $sitemapContent = $this->environment->render('@c975LPageEdit/sitemap.xml.twig', ['pages' => $pages]);
        $sitemapFile = str_starts_with(Kernel::VERSION, '3') ? $root . '/../web/sitemap-' . $folderPages . '.xml' : $root . '/public/sitemap-' . $folderPages . '.xml';
        file_put_contents($sitemapFile, $sitemapContent);

        //Ouputs message
        $output->writeln('Sitemap created!');

        if (str_starts_with(Kernel::VERSION, '5')) {
            return Command::SUCCESS;
        }

        return 0;
    }
}
