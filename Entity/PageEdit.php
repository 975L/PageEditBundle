<?php

namespace c975L\PageEditBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PageEdit
{

    /**
     * @Assert\NotBlank()
     */
    protected $content;
    /**
     * @Assert\NotBlank()
     */
    protected $title;
    /**
     * @Assert\NotBlank()
     */
    protected $slug;
    protected $action;


    public function __construct($action = null, $content = null, $title = null, $slug = null)
    {
        $this->setAction($action);
        $this->setContent($content);
        $this->setTitle($title);
        $this->setSlug($slug);
    }


    /**
     * Set action
     *
     * @return PageEdit
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
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
}
