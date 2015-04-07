<?php
namespace lib;

class pathFunctions {
    
    static function getUploadsDirectory() {
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


    static function convertDirectoryToPath($dir) {
        return str_replace(DS, '/', $dir);
    }


    static function getRequestUri() {
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }


    static function getParentUri() {
        $uri = self::getRequestUri();
        return substr($uri, 0, strrpos($uri, '/'));
    }


    static function getFileBackUri($excelPath) {
        $uri = str_replace(self::getUploadsDirectory(), '', dirname($excelPath));
        $uri = self::convertDirectoryToPath($uri);
        return $uri;
    }


    static function getBaseUri() {
        return ABS_ROOT;
    }


    static function getUriRelativeToBase($uri) {
        return str_replace(self::getBaseUri(), '', $uri);
    }


    static function encodeUrl($uri) {
        $segments = explode('/', $uri);
        $segments = array_map('rawurlencode', $segments);
        $uri = implode('/', $segments);
        return $uri;
    }


    static function decodeUrl($uri) {
        return rawurldecode($uri);
    }


    static function slugify($text) {
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
