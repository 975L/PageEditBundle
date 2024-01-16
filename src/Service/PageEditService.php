<?php
/*
 * (c) 2018: 975L <contact@975l.com>
 * (c) 2018: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Service;

use c975L\ConfigBundle\Service\ConfigServiceInterface;
use c975L\PageEditBundle\Entity\PageEdit;
use c975L\PageEditBundle\Form\PageEditFormFactoryInterface;
use c975L\PageEditBundle\Service\File\PageEditFileInterface;
use c975L\PageEditBundle\Service\Slug\PageEditSlugInterface;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Main services related to PageEdit
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEditService implements PageEditServiceInterface
{
    /**
     * Stores current Request
     */
    private readonly ?\Symfony\Component\HttpFoundation\Request $request;

    public function __construct(
        /**
         * Stores AuthorizationCheckerInterface
         */
        private readonly AuthorizationCheckerInterface $authChecker,
        /**
         * Stores ConfigServiceInterface
         */
        private readonly ConfigServiceInterface $configService,
        /**
         * Stores PageEditFormFactoryInterface
         */
        private readonly PageEditFormFactoryInterface $pageEditFormFactory,
        /**
         * Stores PageEditFileInterface
         */
        private readonly PageEditFileInterface $pageEditFile,
        /**
         * Stores PageEditSlugInterface
         */
        private readonly PageEditSlugInterface $pageEditSlug,
        RequestStack $requestStack,
        /**
         * Stores Environment
         */
        private readonly Environment $environment,
        /**
         * Stores TranslatorInterface
         */
        private readonly TranslatorInterface $translator
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function cloneObject(PageEdit $pageEdit)
    {
        $pageEditClone = clone $pageEdit;
        $pageEditClone->setSlug(null);

        return $pageEditClone;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(string $name, PageEdit $pageEdit)
    {
        return $this->pageEditFormFactory->create($name, $pageEdit);
    }

    /**
     * {@inheritdoc}
     */
    public function definePagesSlugTitle(Finder $finder)
    {
        $pages = [];
        $view = $this->request->get('v');

        foreach ($finder as $file) {
            $slug = str_replace('.html.twig', '', $file->getRelativePathname());

            $title = $this->getTitle($file->getContents(), $slug);
            $titleTranslated = $this->getTitleTranslated($title);

            //Defines status of page
            if (str_contains($file->getPath(), 'protected')) {
                $status = 'protected';
            } elseif (null === $view) {
                $status = 'current';
            } else {
                $status = $view;
            }

            //Adds page to array
            $pages[] = ['slug' => $slug, 'title' => $titleTranslated, 'status' => $status];
        }

        return $pages;
    }

    //Defines toolbar
    public function defineToolbar(string $kind, string $page)
    {
        $toolbar = null;

        if ($this->authChecker->isGranted($this->configService->getParameter('c975LPageEdit.roleNeeded'))) {
            $tools = $this->environment->render('@c975LPageEdit/tools.html.twig', ['type' => $kind, 'object' => $page]);
            $toolbar = $this->environment->render('@c975LToolbar/toolbar.html.twig', ['tools' => $tools, 'size' => 'md', 'alignment' => 'center']);
        }

        return $toolbar;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $page, bool $archive)
    {
        $this->pageEditFile->delete($page, $archive);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangeFrequency(string $fileContent)
    {
        $changeFrequency = 'monthly';

        preg_match('/pageedit_changeFrequency=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $changeFrequency = $matches[1];
        }

        return $changeFrequency;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(string $fileContent)
    {
        //Kept block pageEdit for compatibility with files not modified with new skeleton (06/03/2018)
        $startBlock = str_contains($fileContent, '{% block pageedit_content %}') ? '{% block pageedit_content %}' : '{% block pageEdit %}';
        $endBlock = '{% endblock %}';
        if (str_contains($fileContent, $startBlock)) {
            $entryPoint = strpos($fileContent, $startBlock) + strlen($startBlock);
            $exitPoint = strpos($fileContent, $endBlock, $entryPoint);

            return trim(substr($fileContent, $entryPoint, $exitPoint - $entryPoint));
        }

        return $fileContent;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(string $page): PageEdit|false
    {
        $filePath = $this->pageEditFile->getPath($page);

        $fs = new Filesystem();
        if ($fs->exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $title = $this->getTitle($fileContent, str_replace( [$this->pageEditFile->getPagesFolder(), '.html.twig'], '', $filePath));

            $modification = new DateTime();
            $pageEdit = new PageEdit();
            $pageEdit
                ->setChangeFrequency($this->getChangeFrequency($fileContent))
                ->setModification($modification->setTimestamp(filemtime($filePath)))
                ->setDescription($this->getDescription($fileContent))
                ->setContent($this->getContent($fileContent))
                ->setFilePath($filePath)
                ->setPriority($this->getPriority($fileContent))
                ->setSlug($page)
                ->setTitle($title)
                ->setTitleTranslated($this->getTitleTranslated($title))
            ;

            return $pageEdit;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $fileContent)
    {
        $description = null;

        preg_match('/pageedit_description=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $description = $matches[1];
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getLinks()
    {
        //Defines paths
        $folderPath = $this->pageEditFile->getPagesFolder();
        $protectedFolderPath = $folderPath . 'protected';

        //Finds pages
        $finder = new Finder();
        $finder
            ->files()
            ->in($folderPath)
            ->in($protectedFolderPath)
            ->depth('== 0')
            ->name('*.html.twig')
            ->sortByName()
            ;

        //Defines title and link value
        $pages = [];
        foreach ($finder as $file) {
            $slug = str_replace('.html.twig', '', $file->getRelativePathname());
            $title = $this->getTitle($file->getContents(), $slug);
            $titleTranslated = $this->getTitleTranslated($title);

            $pages[] = ['title' => $titleTranslated, 'value' => "{{ path('pageedit_display', {'page': '" . $slug . "'}) }}"];
        }

        return $pages;
    }

    /**
     * {@inheritdoc}
     */
    public function getPages()
    {
        $finder = $this->pageEditFile->getPages();

        return $this->definePagesSlugTitle($finder);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(string $fileContent)
    {
        $priority = 5;

        preg_match('/pageedit_priority=\"(.*)\"/', $fileContent, $matches);
        if (!empty($matches)) {
            $priority = (int) $matches[1];
        }

        return $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(string $fileContent, string $slug)
    {
        preg_match('/pageedit_title=\"(.*)\"/', $fileContent, $matches);

        //Plain title
        if (!empty($matches)) {
            $title = str_replace('\"', '"', (string) $matches[1]);
        //Title is using Twig code to translate it
        } else {
            preg_match('/pageedit_title=(.*)\%\}/', $fileContent, $matches);
            if (!empty($matches)) {
                $title = trim((string) $matches[1]);
            //Title not found
            } else {
                $title = $this->translator->trans('label.title_not_found', [], 'pageedit') . ' (' . $slug . ')';
            }
        }

        return $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitleTranslated(string $title)
    {
        $titleTranslated = $title;

        if (str_contains($title, '|trans')) {
            $translateLabel = trim(substr($title, 0, strpos($title, '|trans')), "'");
            $translateDomain = 'messages';
            if (str_contains($title, '|trans(')) {
                $translateDomain = trim(trim(substr($title, strpos($title, '}') + 2)), "'");
                $translateDomain = substr($translateDomain, 0, strlen($translateDomain) - 2);
            }
            $titleTranslated = $this->translator->trans($translateLabel, [], $translateDomain);
        }

        return $titleTranslated;
    }

    /**
     * {@inheritdoc}
     */
    public function register(string $page, Form $form, $user)
    {
        $formData = $form->getData();

        //New page or modification of the slug
        if ('createNewPageEdit' === $page || $page !== $formData->getSlug()) {
            $slug = $this->pageEditSlug->slugify($form->getData()->getSlug(), true);
            //Archives and redirects the file if slug has changed
            if ($page !== $formData->getSlug()) {
                $this->pageEditFile->archive($page, $user);
                $this->pageEditFile->redirect($page, $slug);
            }
        //Slug has not changed
        } else {
            $slug = $formData->getSlug();
        }

        $this->pageEditFile->write($slug, $form, $user);

        return $slug;
    }
}
