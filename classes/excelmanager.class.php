<?php
namespace classes;

class ExcelManager {
    private $excelFile;

    public function __construct($excelFile) {
        $this->excelFile = array();
        $excelReader = \PHPExcel_IOFactory::createReaderForFile($excelFile);
        $excelReader->setReadDataOnly();
        //$loadSheets = array('Sheet1');
        //$excelReader->setLoadSheetsOnly($loadSheets);
        $excelObj = $excelReader->load($excelFile);
        $worksheetNames = $excelObj->getSheetNames($excelFile);
        foreach($worksheetNames as $key => $sheetName) {
            $excelObj->setActiveSheetIndexByName($sheetName);
            $this->excelFile = $excelObj->getActiveSheet()->toArray(null, true,true,true);
        }
    }


    public function getCampaigns() {
        $campaigns = array();
        foreach ($this->excelFile as $line => $row) {
            if ($line > 1) {
                $media = trim($row['A']);
                if ($media) {
                    if (!array_key_exists($media, $campaigns)) {
                        $campaigns[$media] = array();
                    }
                    $campaigns[$media][] = json_encode( array('name'=> trim($row['M']), 'size' => strtolower(trim($row['C']))) );
                }
            }
        }
        return $campaigns;
    }


    public function getPieceData($pieceNames) {
        $return = false;
        foreach ($this->excelFile as $line => $row) {
            if ($line > 1 && in_array(trim($row['M']), $pieceNames)) {
                $format = isset($row['B']) ? trim($row['B']) : '';
                $size = isset($row['C']) ? strtolower(trim($row['C'])) : '';
                $deliverables = isset($row['D']) ? array_map('trim', explode(',', $row['D'])) : '';
                $imageWeight = isset($row['E']) ? trim($row['E']) : null;
                $swfWeight = isset($row['F']) ? trim($row['F']) : null;
                $maxDuration = isset($row['G']) ? trim($row['G']) : null;
                $fps = isset($row['H']) ? trim($row['H']) : null;
                $flashVersion = isset($row['I']) ? trim($row['I']) : '';
                $asVersion = isset($row['J']) ? trim($row['J']) : '';
                $loops = isset($row['K']) ? trim($row['K']) : '';
                $clickTag = isset($row['L']) ? trim($row['L']) : '';
                $name = isset($row['M']) ? trim($row['M']) : '';
                $width = 0;
                $height = 0;
                // Validations
                $size = array_map('trim', explode('x', $size));
                if (count($size)) {
                    $width = isset($size[0]) && is_numeric($size[0]) ? $size[0] : $width;
                    $height = isset($size[1]) && is_numeric($size[1]) ? $size[1] : $height;
                }
                if (!is_numeric($imageWeight)) {
                    $imageWeight = null;
                }
                if (!is_numeric($swfWeight)) {
                    $swfWeight = null;
                }
                if (!is_numeric($maxDuration)) {
                    $maxDuration = null;
                }
                if (!is_numeric($fps)) {
                    $fps = null;
                }

                $return = array(
                    'format' => $format,
                    'width' => $width,
                    'height' => $height,
                    'deliverables' => $deliverables,
                    'imageWeight' => $imageWeight,
                    'swfWeight' => $swfWeight,
                    'maxDuration' => $maxDuration,
                    'fps' => $fps,
                    'flashVersion' => $flashVersion,
                    'asVersion' => $asVersion,
                    'loops' => $loops,
                    'clickTag' => $clickTag,
                    'name' => $name
                );
                break;
            }
        }
        return $return;
    }

}
