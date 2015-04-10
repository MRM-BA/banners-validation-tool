<?php
namespace controllers;

require_once(__DIR__.'/../classes/filesmanager.class.php');
require_once(__DIR__.'/../classes/configmanager.class.php');
require_once(__DIR__.'/../classes/excelmanager.class.php');

class FilesController {
    private $root;
    private $currentDirectory;
    private $isClientReview;
    private $template;
    private $filesmanager;
    private $configmanager;
    private $excelmanager;


    public function __construct($root, $currentDirectory, $isClientReview) {
        $this->root = $root;
        $this->currentDirectory = $currentDirectory;
        $this->isClientReview = $isClientReview;
        $loader = new \Twig_Loader_Filesystem('views');
        $this->template = new \Twig_Environment($loader);
        $this->template->addExtension(new \plugins\encodeurl\EncodeUrlTwigExtension());
        $this->template->addExtension(new \plugins\slugify\SlugifyTwigExtension());
        $this->filesmanager = new \classes\FilesManager($this->root);
        $jsonFile = $this->filesmanager->getJsonFile($this->currentDirectory, $this->root);
        $this->configmanager = new \classes\ConfigManager($jsonFile);
        $this->template->addGlobal('base', \lib\pathFunctions::getBaseUri());
        $this->template->addGlobal('configuration', $this->configmanager);
        $this->template->addGlobal('isCR', $this->isClientReview);
        $this->template->addGlobal('CRUriSegment', ($this->isClientReview ? CLIENT_REVIEW_URI_SEGMENT.'/' : ''));
    }


    public function index($path) {
        $path = $path ? $path.'/' : '';
        $projects = $this->filesmanager->getCurrentFolderDirectories($this->currentDirectory);
        $this->template->display(
            'Files/index.html',
            array(
                'path' => $path,
                'projects' => $projects
            )
        );
    }


    public function project($path) {
        $excelFile = $this->filesmanager->haveExcelFile($this->currentDirectory);
        if ($excelFile) {
            $this->excelmanager = new \classes\ExcelManager($excelFile);
            $files = $this->filesmanager->getProjectFiles($this->currentDirectory);
            $campaigns = $this->excelmanager->getCampaigns();
            $project = $this->filesmanager->validatedProject($campaigns,$files);
            $statsProject = $this->filesmanager->statsProject($project);
            $this->template->display(
                'Files/project.html',
                array(
                    'path' => $path,
                    'project' => $project,
                    'stats' => $statsProject
                )
            );
        } else {
            header('Location: '.\lib\pathFunctions::getParentUri());
        }
    }


    public function file($path) {
        $back_link = \lib\pathFunctions::getUriRelativeToBase(\lib\pathFunctions::getParentUri());
        $excelFile = $this->filesmanager->getExcelForPiece(\lib\pathFunctions::cleanDirectory($this->currentDirectory));
        if ($excelFile) {
            $back_link = \lib\pathFunctions::getFileBackUri($excelFile);
            $files = $this->filesmanager->getPieceFiles($this->currentDirectory);
            rsort($files);
            $projectName = $this->filesmanager->getProjectName($excelFile);
            $pieceNames = $this->filesmanager->getPieceName($files);
            $this->excelmanager = new \classes\ExcelManager($excelFile);
            $pieceData = $this->excelmanager->getPieceData($pieceNames);
            $filesValidated = $this->filesmanager->validatePiece($this->currentDirectory,$files,$pieceData);
            $deliverablesValidated = $this->filesmanager->validateDeliverables($files,$pieceData);
            $hasErrors = $this->filesmanager->hasErrors($filesValidated,$deliverablesValidated);
            $this->template->display(
                    'Files/file.html',
                    array(
                        'back_link' => $back_link,
                        'project_name' => $projectName,
                        'title' => $pieceData['name'],
                        'files' => $filesValidated,
                        'pieceData' => $pieceData,
                        'deliverables' => $deliverablesValidated,
                        'hasErrors' => $hasErrors
                    )
            );
        } else {
            header('Location: '.$back_link);
        }
    }
}
