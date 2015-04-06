<?php
namespace plugins\encodeurl;

class EncodeUrlTwigExtension extends \Twig_Extension
{
    
    public function getName()
    {
        return 'Encode URL Twig Extension';
    }


    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('encodeurl', array($this, 'encodeUrl')),
        );
    }


    public function encodeUrl($text)
    {
        return \lib\pathFunctions::encodeUrl($text);
    }
    
}
