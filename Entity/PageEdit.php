<?php

namespace c975L\PageEditBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PageEdit
{

    /**
     * @Assert\NotBlank()
     */
    protected $content;


    public function __construct($content)
    {
        $this->setContent($content);
    }



    public function getContent()
    {
        return $this->content;
    }


    public function setContent($content)
    {
        $this->content = $content;
    }

}
