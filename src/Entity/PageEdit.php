<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Entity;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity PageEdit (not linked to DB)
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEdit
{
    #[Assert\NotBlank]
    protected ?string $title = null;

    protected ?string $titleTranslated = null;

    #[Assert\NotBlank]
    protected ?string $slug = null;

    #[Assert\NotBlank]
    protected ?string $content = null;

    protected ?string $filePath = null;

    protected ?string $changeFrequency = null;

    protected ?int $priority = null;

    protected ?string $description = null;

    protected ?DateTime $modification = null;

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitleTranslated(?string $titleTranslated): self
    {
        $this->titleTranslated = $titleTranslated;

        return $this;
    }

    public function getTitleTranslated(): ?string
    {
        return $this->titleTranslated;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setChangeFrequency(?string $changeFrequency): self
    {
        $this->changeFrequency = $changeFrequency;

        return $this;
    }

    public function getChangeFrequency(): ?string
    {
        return $this->changeFrequency;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setModification(?DateTime $modification): self
    {
        $this->modification = $modification;

        return $this;
    }

    public function getModification(): ?DateTime
    {
        return $this->modification;
    }
}