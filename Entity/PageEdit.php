<?php
/*
 * (c) 2017: 975L <contact@975l.com>
 * (c) 2017: Laurent Marquet <laurent.marquet@laposte.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace c975L\PageEditBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entity PageEdit (not linked to DB)
 * @author Laurent Marquet <laurent.marquet@laposte.net>
 * @copyright 2018 975L <contact@975l.com>
 */
class PageEdit
{
    /**
     * Stores title
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * Stores titleTranslated
     * @var string
     */
    protected $titleTranslated;

    /**
     * Stores slug
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * Stores content
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * Stores changeFrequency
     * @var string
     */
    protected $changeFrequency;

    /**
     * Stores priority
     * @var int
     */
    protected $priority;

    /**
     * Stores description
     * @var string
     */
    protected $description;

    /**
     * Stores modification
     * @var \DateTime
     */
    protected $modification;


    /**
     * Set title
     * @param string
     * @return PageEdit
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set titleTranslated
     * @param string
     * @return PageEdit
     */
    public function setTitleTranslated($titleTranslated)
    {
        $this->titleTranslated = $titleTranslated;

        return $this;
    }

    /**
     * Get titleTranslated
     * @return string
     */
    public function getTitleTranslated()
    {
        return $this->titleTranslated;
    }

    /**
     * Set slug
     * @param string
     * @return PageEdit
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content
     * @param string
     * @return PageEdit
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set changeFrequency
     * @param string
     * @return PageEdit
     */
    public function setChangeFrequency($changeFrequency)
    {
        $this->changeFrequency = $changeFrequency;

        return $this;
    }

    /**
     * Get changeFrequency
     * @return string
     */
    public function getChangeFrequency()
    {
        return $this->changeFrequency;
    }

    /**
     * Set priority
     * @param int
     * @return PageEdit
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set description
     * @param string
     * @return PageEdit
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set modification
     * @param \DateTime
     * @return PageEdit
     */
    public function setModification($modification)
    {
        $this->modification = $modification;

        return $this;
    }

    /**
     * Get modification
     * @return \DateTime
     */
    public function getModification()
    {
        return $this->modification;
    }
}