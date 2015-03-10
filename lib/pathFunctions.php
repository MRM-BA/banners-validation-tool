<?php
namespace lib;

class pathFunctions {
    static function cleanPath($path) {
        if ($path != '') {
            if (substr($path,-1,1) == '/') {
                $path = substr($path,0,-1);
            }
        }
        return $path;
    }


    static function getRequestUri() {
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }


    static function getParentUri() {
        $uri = self::getRequestUri();
        return substr($uri, 0, strrpos($uri, '/'));
    }
}
