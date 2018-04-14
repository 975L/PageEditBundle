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

class PageEdit
{
    /**
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @Assert\NotBlank()
     */
    protected $content;

    protected $changeFrequency;
    protected $priority;
    protected $description;


    public function __construct($content = null, $title = null, $slug = null, $changeFrequency = null, $priority = null, $description = null)
    {
        $this->setContent($content);
        $this->setTitle($title);
        $this->setSlug($slug);
        $this->setChangeFrequency($changeFrequency);
        $this->setPriority($priority);
        $this->setDescription($description);
    }


    /**
     * Set title
     *
     * @return PageEdit
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @return PageEdit
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content
     *
     * @return PageEdit
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set changeFrequency
     *
     * @return PageEdit
     */
    public function setChangeFrequency($changeFrequency)
    {
        $this->changeFrequency = $changeFrequency;

        return $this;
    }

    /**
     * Get changeFrequency
     *
     * @return string
     */
    public function getChangeFrequency()
    {
        return $this->changeFrequency;
    }

    /**
     * Set priority
     *
     * @return PageEdit
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set description
     *
     * @return PageEdit
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}