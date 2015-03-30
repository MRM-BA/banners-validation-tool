<?php
namespace controllers;

require_once(__DIR__.'/../classes/filesmanager.class.php');
require_once(__DIR__.'/../classes/configmanager.class.php');
require_once(__DIR__.'/../classes/excelmanager.class.php');

class FilesController {
    private $root;
    private $currentDirectory;
    private $template;
    private $filesmanager;
    private $configmanager;
    private $excelmanager;


    public function __construct($root, $currentDirectory) {
        $this->root = $root;
        $this->currentDirectory = $currentDirectory;
        $loader = new \Twig_Loader_Filesystem('views');
        $this->template = new \Twig_Environment($loader);
        $this->filesmanager = new \classes\FilesManager($this->root);
        $jsonFile = $this->filesmanager->getJsonFile($this->currentDirectory, $this->root);
        $this->configmanager = new \classes\ConfigManager($jsonFile);
        $this->template->addGlobal('base', ABS_ROOT);
        $this->template->addGlobal('configuration', $this->configmanager);
    }


    public function index() {
        $projects = $this->filesmanager->getAllProjects();
        $this->template->display('Files/index.html', array('projects' => $projects));
    }


    public function project($path) {
        $excelFile = $this->filesmanager->haveExcelFile($this->currentDirectory);
        if ($excelFile) {
            $this->excelmanager = new \classes\ExcelManager($excelFile);
            $files = $this->filesmanager->getProjectFiles($this->currentDirectory);
            $campaigns = $this->excelmanager->getCampaigns();
            $project = $this->filesmanager->validatedProject($campaigns,$files);
            $this->template->display(
                'Files/project.html', 
                array(
                    'path' => $path,
                    'project' => $project
                )
            );
        } else {
            header('Location: '.\lib\pathFunctions::getParentUri());
        }
    }


    public function file($path) {
        $back_link = \lib\pathFunctions::getParentUri();
        $excelFile = $this->filesmanager->getExcelForPiece($this->currentDirectory);
        if ($excelFile) {
            $files = $this->filesmanager->getPieceFiles($this->currentDirectory);
            rsort($files);
            $pieceName = $this->filesmanager->getPieceName($files);
            $this->excelmanager = new \classes\ExcelManager($excelFile);
            $pieceData = $this->excelmanager->getPieceData($pieceName);
            $filesValidated = $this->filesmanager->validatePiece($this->currentDirectory,$files,$pieceData);
            $this->template->display(
                    'Files/file.html',
                    array(
                        'back_link' => $back_link,
                        'title' => $pieceName,
                        'files' => $filesValidated,
                        'pieceData' => $pieceData
                    )
            );
        } else {
            header('Location: '.$back_link);
        }
    }
}
