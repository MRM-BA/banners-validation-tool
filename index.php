<?php
require_once('config/config.php');
require_once('lib/pathFunctions.php');
require_once('controllers/FilesController.php');
require_once('vendor/autoload.php');
require_once('plugins/slugify/SlugifyTwigExtension.php');

//$root = __DIR__.DS.PROJECTS_FOLDER.DS;
$root = \lib\pathFunctions::getUploadsDirectory();
$path = isset($_GET['path']) ? $_GET['path'] : '';
$path = \lib\pathFunctions::cleanPath($path);
if ($absPath = realpath($root.$path)) {
    $absPath .= DS;
} else {
    header('Location: '.\lib\pathFunctions::getParentUri());
    exit();
}


$router = new \controllers\FilesController($root, $absPath);

if ($path != '') {
    require_once('classes/filesmanager.class.php');
    $filesmanager = new \classes\FilesManager($root);
    if ($filesmanager->haveFilesOnly($absPath)) {
        $router->file($path);
    } else {
        $router->project($path);
    }
} else {
    $router->index();
}





