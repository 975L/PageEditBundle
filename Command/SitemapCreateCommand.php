<?php
namespace c975L\PageEditBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Twig_Environment;
use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Service\PageEditServiceInterface;

/**
 * Console command to create sitemap of pages, executed with 'pageedit:createSitemap'
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2017 975L <contact@975l.com>
 */
class SitemapCreateCommand extends ContainerAwareCommand
{
    /**
     * Stores ConfigServiceInterface
     * @var ConfigServiceInterface
     */
    private $configService;

    /**
     * Stores ContainerInterface
     * @var ContainerInterface
     */
    private $container;

    /**
     * Stores PageEditServiceInterface
     * @var PageEditServiceInterface
     */
    private $pageEditService;

    /**
     * Stores Twig_Environment
     * @var Twig_Environment
     */
    private $templating;

    public function __construct(
        ConfigServiceInterface $configService,
        ContainerInterface $container,
        PageEditServiceInterface $pageEditService,
        Twig_Environment $templating
    )
    {
        parent::__construct();
        $this->configService = $configService;
        $this->container = $container;
        $this->pageEditService = $pageEditService;
        $this->templating = $templating;
    }

    protected function configure()
    {
        $this
            ->setName('pageedit:createSitemap')
            ->setDescription('Creates the sitemap of pages managed via PageEdit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Creates structure in case it not exists
        $fs = new Filesystem();
        $rootFolder = $this->container->getParameter('kernel.root_dir');
        $folderPages = $this->configService->getParameter('c975LPageEdit.folderPages');
        $folderPath = $rootFolder . '/Resources/views/' . $folderPages;
        $protectedFolderPath = $rootFolder . '/Resources/views/' . $folderPages . '/protected';
        $fs->mkdir(array(
            $folderPath,
            $protectedFolderPath,
        ) , 0770);

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
        $pages = array();
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
                    $pages[]= array(
                        'url' => $url,
                        'lastModified' => date('Y-m-d', $file->getMTime()),
                        'changeFrequency' => $changeFrequency,
                        'priority' => $priority,
                    );
                }
            } else {
                $url = $urlRoot;
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
        $sitemapContent = $this->templating->render('@c975LPageEdit/sitemap.xml.twig', array('pages' => $pages));
        $sitemapFile = '4' === substr(Kernel::VERSION, 0, 1) ? $rootFolder . '/../public/sitemap-' . $folderPages . '.xml' : $rootFolder . '/../web/sitemap-' . $folderPages . '.xml';
        file_put_contents($sitemapFile, $sitemapContent);

        //Ouputs message
        $output->writeln('Sitemap created!');
    }
}
