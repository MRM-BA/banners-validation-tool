<?php
namespace lib;

class pathFunctions {
    
    static function getUploadsDirectory() {
        //return 'C:\xampp\htdocs\banners-validation-tool\uploads\'';
        return  realpath(__DIR__.'/../'.PROJECTS_FOLDER).DS;
    }


    static function cleanPath($path) {
        if ($path != '') {
            if (substr($path,-1,1) == '/') {
                $path = substr($path,0,-1);
            }
        }
        return $path;
    }


    static function cleanDirectory($dir) {
        if (substr($dir, -1) == DS) {
            $dir = substr($dir,0,strrpos($dir, DS));
        }
        return $dir;
    }


    static function getRequestUri() {
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }


    static function getParentUri() {
        $uri = self::getRequestUri();
        return substr($uri, 0, strrpos($uri, '/'));
    }
}
