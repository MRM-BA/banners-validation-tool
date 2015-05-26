<?php
require_once('config/config.php');
require_once('lib/pathFunctions.php');
require_once('controllers/FilesController.php');
require_once('vendor/autoload.php');
require_once('plugins/encodeurl/EncodeUrlTwigExtension.php');
require_once('plugins/slugify/SlugifyTwigExtension.php');
require_once('classes/filesmanager.class.php');

$root = \lib\pathFunctions::getUploadsDirectory();
$path = isset($_GET['path']) ? \lib\pathFunctions::decodeUrl($_GET['path']) : '';
$path = \lib\pathFunctions::cleanPath($path);
$isClientReview = \lib\pathFunctions::isClientReview($path);
if ($absPath = realpath($root.$path)) {
    $absPath .= DS;
} else {
    header('Location: '.\lib\pathFunctions::getParentUri());
    exit();
}

$router = new \controllers\FilesController($root, $absPath, $isClientReview);
$filesmanager = new \classes\FilesManager($root);

if ($path == '') {
    $router->index($path);
} else if ($filesmanager->haveFilesOnly($absPath)) {
    $router->file($path);
} else {
    if ($filesmanager->getExcelFileInParentDirectories($absPath, $root)) {
        $router->project($path);
    } else {
        $router->index($path);
    }
}
