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


    public function __construct($content = null, $title = null, $slug = null)
    {
        $this->setContent($content);
        $this->setTitle($title);
        $this->setSlug($slug);
    }


    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

}
