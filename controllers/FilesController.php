<?php
namespace controllers;

require_once(__DIR__.'/../classes/filesmanager.class.php');
require_once(__DIR__.'/../classes/excelmanager.class.php');

class FilesController {
    private $root;
    private $template;
    private $filesmanager;
    private $excelmanager;


    public function __construct($root) {
        $this->root = $root;
        $loader = new \Twig_Loader_Filesystem('views');
        $this->template = new \Twig_Environment($loader);
        $this->template->addGlobal('base', ABS_ROOT);
        $this->filesmanager = new \classes\FilesManager($root);
    }


    public function index() {
        $projects = $this->filesmanager->getAllProjects();
        $this->template->display('Files/index.html', array('projects' => $projects));
    }
    
    /*
     * Checks how many fails has a project before rendering
     * @param array $project Project result set
     * @return array Returns fails and total items of the project
     * @author sebastian.perez 
     */
    
    private function statsProject($project){
      $total = 0;
      $fails = 0;
      if(is_array($project)):
       foreach($project as $item):
            foreach($item as $p):
              if(is_array($p)):
                foreach($p as $v):
                   $total++;
                   if(empty($v['uploaded'])):
                     $fails++;
                   endif;
                endforeach;
               endif;
            endforeach;
         endforeach;
      endif;
      
      $results = array('total' => $total, 'fails' => $fails);
      
      return $results;
      
    }
    
    public function project($path) {
        $absPath = realpath($this->root.$path);
        $excelFile = $this->filesmanager->haveExcelFile($absPath);
        if ($excelFile) {
            $this->excelmanager = new \classes\ExcelManager($excelFile);
            $files = $this->filesmanager->getProjectFiles($absPath);            
            $campaigns = $this->excelmanager->getCampaigns();
            $project = $this->filesmanager->validatedProject($campaigns,$files);            
            $statsProject = $this->statsProject($project);
             
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
        $back_link = \lib\pathFunctions::getParentUri();
        $absPath = realpath($this->root.$path);
        $excelFile = $this->filesmanager->getExcelForPiece($absPath);
        if ($excelFile) {
            $files = $this->filesmanager->getPieceFiles($absPath);
            rsort($files);
             
            $pieceName = $this->filesmanager->getPieceName($files);
            $this->excelmanager = new \classes\ExcelManager($excelFile);
            $pieceData = $this->excelmanager->getPieceData($pieceName);
            $filesValidated = $this->filesmanager->validatePiece($absPath,$files,$pieceData);
            $deliverablesValidated = $this->filesmanager->validateDeliverables($files,$pieceData);
           
            $this->template->display(
                    'Files/file.html',
                    array(
                        'back_link' => $back_link,
                        'title' => $pieceName,
                        'files' => $filesValidated,
                        'pieceData' => $pieceData,
                        'deliverables' => $deliverablesValidated
                    )
            );
        } else {
            header('Location: '.$back_link);
        }
    }
}
