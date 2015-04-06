<?php
namespace plugins\slugify;

class SlugifyTwigExtension extends \Twig_Extension
{
    
    public function getName()
    {
        return 'Slugify Twig Extension';
    }


    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('slugify', array($this, 'slugify')),
        );
    }


    public function slugify($text)
    {
        return \lib\pathFunctions::slugify($text);
    }
    
}
