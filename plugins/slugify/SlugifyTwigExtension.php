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
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv'))
        {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }
    
}
