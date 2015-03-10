<?php
namespace classes;

class FilesManager {
    private $root;
    private $pathOfFile;


    public function __construct($root) {
        $this->root = $root;
    }


    private function exploreDirectory($dir, $includeFiles = false) {
        $return = array();
        $dir = new \DirectoryIterator($dir);
        foreach ($dir as $node) {
            if (!$node->isDot()) {
                if ($includeFiles) {
                    if ($filesExtensions && count($filesExtensions))
                    $return[] = $node->getFilename();
                } else if ($node->isDir()) {
                    $return[] = $node->getFilename();
                }
            }
        }
        return $return;
    }


    private function fillArrayWithFileNodes( \DirectoryIterator $dir ) {
      $data = array();
      foreach ($dir as $node) {
        if ($node->isDir() && !$node->isDot()) {
          $data[$node->getFilename()] = $this->fillArrayWithFileNodes( new \DirectoryIterator( $node->getPathname() ) );
        } else if ($node->isFile()) {
          $data[] = $node->getFilename();
        }
      }
      return $data;
    }


    private function concatDirectoriesOfArray($array, $previousPath = '', $includeFilesInCurrentDirectory = true, $includeFilesInChildDirectory = true, $return = array()) {
        $iterator = new \RecursiveArrayIterator($array);
        while ($iterator->valid()) {
            if($iterator->hasChildren()) {
                $prev = '';
                if ($previousPath) {
                    $prev .= $previousPath.'/';
                }
                $prev .= $iterator->key();
                $files = $this->concatDirectoriesOfArray($iterator->getChildren(), $prev, $includeFilesInChildDirectory, $includeFilesInChildDirectory);
                foreach ($files as $key => $value) {
                    if ($value && !in_array($value, $return)) {
                        $return[] = $value;
                    }
                }
            } else if ($includeFilesInCurrentDirectory) {
                $return[] = $previousPath.'/'.$iterator->current();
            } else {
                if ($previousPath != '') {
                    $return[] = $previousPath;
                }
            }
            $iterator->next();
        }
        return $return;
    }


    public function haveFilesOnly($path) {
        $return = true;
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $node) {
            if (!$node->isDot() && $node->isDir()) {
                $return = false;
            }
        }
        return $return;
    }


    public function haveExcelFile($path) {
        $return = false;
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $node) {
            if (!$node->isDot() && $node->isFile()) {
                /*
                if (strtolower($node->getExtension()) == 'xls' || strtolower($node->getExtension()) == 'xlsx') {
                    $return = $path.DS.$node->getFilename();
                }
                */
                $extension = pathinfo($node->getFilename(), PATHINFO_EXTENSION);
                if (strtolower($extension) == 'xls' || strtolower($extension) == 'xlsx') {
                    $return = $path.DS.$node->getFilename();
                }
            }
        }
        return $return;
    }


    public function getAllProjects() {
        $return = $this->exploreDirectory($this->root, false);
        return $return;
    }


    public function getProjectSubdirectories($pathOfProjectFolder) {
        $tree = $this->fillArrayWithFileNodes(new \DirectoryIterator($pathOfProjectFolder));
        $files = $this->concatDirectoriesOfArray($tree, '', false, false);
        return $files;
    }


    public function getProjectFiles($pathOfProjectFolder) {
        $tree = $this->fillArrayWithFileNodes(new \DirectoryIterator($pathOfProjectFolder));
        $files = $this->concatDirectoriesOfArray($tree, '', false, true);
        return $files;
    }


    public function getPieceFiles($path) {
        $tree = $this->fillArrayWithFileNodes(new \DirectoryIterator($path));
        $files = $this->concatDirectoriesOfArray($tree, '', true, false);
        return $files;
    }


    public function validatedProject($campaigns,$files) {
        $project = array();
        $aux = array();
        $array_keys = array_keys($campaigns);
        foreach ($array_keys as $key => $value) {
            $project[strtolower($value)] = array('media' => $value, 'pieces' => array());
            $aux[strtolower($value)] = array('media' => $value, 'pieces' => array(), 'directory' => array());
        }
        foreach ($files as $key => $value) {
            $media = strtolower(substr($value, 0, strpos($value, '/')));
            $directory = substr($value, 0, strrpos($value, '/'));
            $file = substr($value, strrpos($value, '/')+1);
            $name = substr($file,0,strrpos($file, '.'));
            if (!in_array($name,$aux[$media]['pieces'])) {
                $aux[$media]['pieces'][] = $name;
                $aux[$media]['directory'][] = $directory;
            }
        }
        foreach ($campaigns as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $uploaded = array_search($value2, $aux[strtolower($key)]['pieces']);
                $project[strtolower($key)]['pieces'][] = array(
                                                        'name' => $value2,
                                                        'uploaded' => ($uploaded !== false ? true : false),
                                                        'link' => $aux[strtolower($key)]['directory'][$uploaded]
                                                    );
            }
            
        }
        return $project;
    }


    public function getExcelForPiece($path) {
        $excelFile = false;
        while (!$excelFile && $path != $this->root) {
            $excelFile = $this->haveExcelFile($path);
            if (!$excelFile) {
                $path = substr($path, 0, strrpos($path, DS));
            }
        }
        return $excelFile;
    }


    public function getPieceName($files) {
        $name = '';
        foreach ($files as $file) {
            $name = substr($file,1,strrpos($file, '.')-1);
        }
        return $name;
    }


    public function validatePiece($directory, $files, $pieceData) {
        $validated = array();
        foreach ($files as $key => $value) {
            $extension = strtolower(substr($value, strrpos($value, '.')+1));
            if ($extension == 'swf') {
                $validated[] = $this->validateSwf($directory,$value,$pieceData);
            } else if (in_array($extension, array('jpg','jpeg','png','gif'))) {
                $validated[] = $this->validateImage($directory,$value,$pieceData);
            } else {
                $validated[] = $this->validateFile($directory,$value,$pieceData);
            }
        }
        return $validated;
    }


    private function validateSwf($directory, $file, $pieceData) {
        require_once(__DIR__.'/../vendor/swfheader/swfheader.class.php');
        $swfheader = new \swfheader();
        $filePath = realpath($directory.$file);
        $info = $swfheader->getDimensions($filePath);
        $return = array();
        $return['type'] = 'swf';
        $return['extension'] = 'swf';
        $return['name'] = substr($file,1);
        $return['path'] = str_replace(DS, '/', substr($filePath, strpos($filePath, PROJECTS_FOLDER)));
        $return['modified'] = date('F d Y h:i:s A', filemtime($filePath));
        $return['weight'] = round(@filesize($filePath)/1024, 2);
        $return['weight_valid'] = ($return['weight'] > $pieceData['swfWeight'] ? false : true);
        $return['flashVersion'] = $swfheader->version;
        $return['flashVersion_valid'] = ($return['flashVersion'] == $pieceData['flashVersion'] ? true : false);
        $return['width'] = $swfheader->width;
        $return['width_valid'] = ($return['width'] == $pieceData['width'] ? true : false);
        $return['height'] = $swfheader->height;
        $return['height_valid'] = ($return['height'] == $pieceData['height'] ? true : false);
        $return['frames'] = $swfheader->frames;
        $return['fps'] = null;
        foreach ($swfheader->fps as $value) {
            if ($value > 0) {
                $return['fps'] = $value;
                break;
            }
        }
        $return['fps_valid'] = ($return['fps'] == $pieceData['fps'] ? true : false);
        return $return;
    }


    private function validateImage($directory, $file, $pieceData) {
        $filePath = realpath($directory.$file);
        $return = array();
        $size = getimagesize($filePath);
        $return['type'] = 'image';
        $return['extension'] = strtolower(substr($file, strrpos($file, '.')+1));
        $return['name'] = substr($file,1);
        $return['path'] = str_replace(DS, '/', substr($filePath, strpos($filePath, PROJECTS_FOLDER)));
        $return['modified'] = date('F d Y h:i:s A', filemtime($filePath));
        $return['weight'] = round(@filesize($filePath)/1024, 2);
        $return['weight_valid'] = ($pieceData['imageWeight'] && $return['weight'] > $pieceData['imageWeight'] ? false : true);
        $return['width'] = (is_array($size) && isset($size[0]) ? $size[0] : null);
        $return['width_valid'] = (!$return['width'] || $return['width'] == $pieceData['width'] ? true : false);
        $return['height'] = (is_array($size) && isset($size[1]) ? $size[1] : null);
        $return['height_valid'] = (!$return['height'] || $return['height'] == $pieceData['height'] ? true : false);
        return $return;
    }

    private function validateFile($directory, $file, $pieceData) {
        $filePath = realpath($directory.$file);
        $return = array();
        $size = getimagesize($filePath);
        $return['type'] = 'file';
        $return['extension'] = strtolower(substr($file, strrpos($file, '.')+1));
        $return['name'] = substr($file,1);
        $return['path'] = str_replace(DS, '/', substr($filePath, strpos($filePath, PROJECTS_FOLDER)));
        $return['modified'] = date('F d Y h:i:s A', filemtime($filePath));
        $return['weight'] = round(@filesize($filePath)/1024, 2);
        return $return;
    }

}
