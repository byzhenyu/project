<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;

class ExcelController extends Controller{

	//excel表格上传功能
	public function uploadExcel(){
	}

	public function uploadPosition(){
		$info = I('attach_path','');
		if (empty($info)) {
			return V(0, "请上传Excel文件");
		}
		$ext = substr(strrchr($info, '.'), 1);
		if ($ext!='xls' && $ext!='xlsx') {
			return V(0, "上传的文件格式不正确");
		}
		if(!file_exists('.'.$info)){
			return V(0, "Excel文件不存在");
		}
		$array = $this->importExecl('.'.$info);
		if($array['error'] == 0) return V(0, $array['message']);
		$content = $array['data'][0]['Content'];
		if(!$content){
			return V(0, '请在Excel中填写需要上传的数据');
		}
		unset($content[1]);
        $serializeArrs = array_map('serialize',$content);
        $uniqueArrs = array_unique($serializeArrs);
        $unserializeArrs = array_map('unserialize',$uniqueArrs);
        $arr = array();
        $model = D('Admin/Position');
        foreach($unserializeArrs as &$val){
            $industry_info = $this->getIndustryInfo($val[0]);
            if(!$industry_info) continue;
            $strpos_valid = strpos($val[2], ',');
            if(false !== $strpos_valid){
                $temp_arr = explode(',', $val[2]);
                $sort = 1;
                foreach($temp_arr as &$t){
                    $position_info = $this->getPositionInfo(array('industry_id' => $val[0], 'parent_id' => $val[1], 'position_name' => $t));
                    if($position_info) continue;
                    $arr[] = array('industry_id' => $val[0], 'parent_id' => $val[1], 'position_name' => $t, 'sort' => $sort++);
                }
                unset($t);
            }
            else{
                $position_info = $this->getPositionInfo(array('industry_id' => $val[0], 'parent_id' => $val[1], 'position_name' => $val[2]));
                if($position_info) continue;
                $arr[] = array('industry_id' => $val[0], 'parent_id' => $val[1], 'position_name' => $val[2], 'sort' => 10);
            }
        }
        unset($val);
        if(count($arr) == 0) return V(0, '未找到任何可匹配项！');
        $msg = '已经成功将数据上传';
        $result = $model->addAll($arr);
		if(false !== $result) return V(1, $msg);
	    return V(0, '输入录入失败！');
	}

	private function getIndustryInfo($industry_id){
	    $res = D('Admin/Industry')->getIndustryInfo(array('id' => $industry_id));
	    return $res;
    }

    private function getPositionInfo($where){
	    $res = D('Admin/Position')->getPositionInfo($where);
	    return $res;
    }

	public function importExecl($file){
		if(!file_exists($file)){
			return array("error"=>0,'message'=>'file not found!');
		}
		Vendor("PHPExcel.PHPExcel.IOFactory");
        ini_set('max_execution_time', '0');
        try{
            $extension = strtolower( pathinfo($file, PATHINFO_EXTENSION) );
            if ($extension =='xlsx') {
                $objReader = new \PHPExcel_Reader_Excel2007();
                $PHPReader = $objReader ->load($file);
            } else if ($extension =='xls') {
                $objReader = new \PHPExcel_Reader_Excel5();
                $PHPReader = $objReader ->load($file);
            }
        }catch(Exception $e){}
		if(!isset($PHPReader)) return array("error"=>0,'message'=>'read error!');
		$allWorksheets = $PHPReader->getAllSheets();
		$i = 0;
		foreach($allWorksheets as $objWorksheet){
			$sheetname=$objWorksheet->getTitle();
			$allRow = $objWorksheet->getHighestRow();//how many rows
			$highestColumn = $objWorksheet->getHighestColumn();//how many columns
			$allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);

			$arr = array();
			$isMergeCell = array();
			foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
				foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
					$isMergeCell[$cellReference] = true;
				}
			}
			for($currentRow = 1 ;$currentRow<=$allRow;$currentRow++){
				$row = array();
				for($currentColumn=0;$currentColumn<$allColumn;$currentColumn++){;
				$cell =$objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
				$afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn+1);
				$bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn-1);
				$col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
				$address = $col.$currentRow;
				$value = $objWorksheet->getCell($address)->getValue();
				if(is_object($value))  $value= $value->__toString();
				if(substr($value,0,1)=='='){
					return array("error"=>0,'message'=>'can not use the formula!');
					exit;
				}
				if($cell->getDataType()==\PHPExcel_Cell_DataType::TYPE_NUMERIC){
					$cellstyleformat=$cell->getParent()->getStyle( $cell->getCoordinate() )->getNumberFormat();
					$formatcode=$cellstyleformat->getFormatCode();
					if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
						$value=gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
					}else{
						$value=\PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
					}
				}
				if($cell->getDataType()==\PHPExcel_Cell_DataType::TYPE_NUMERIC){
					$cellstyleformat=$cell->getParent()->getStyle( $cell->getCoordinate() )->getNumberFormat();
					$formatcode=$cellstyleformat->getFormatCode();
					if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
						$value=gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
					}else{
						$value=\PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
					}
				}
				if($isMergeCell[$col.$currentRow]&&$isMergeCell[$afCol.$currentRow]&&!empty($value)){
					$temp = $value;
				}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$col.($currentRow-1)]&&empty($value)){
					$value=$arr[$currentRow-1][$currentColumn];
				}elseif($isMergeCell[$col.$currentRow]&&$isMergeCell[$bfCol.$currentRow]&&empty($value)){
					$value=$temp;
				}
				$row[$currentColumn] = $value;
				}
				$arr[$currentRow] = $row;
			}
			$array[$i]["Content"] = $arr;
			$i++;
		}
		// spl_autoload_register(array('Think','autoload'));//must, resolve ThinkPHP and PHPExcel conflicts
		unset($objWorksheet);
		unset($PHPReader);
		unset($PHPExcel);
		unlink($file);

		return array("error"=>1,"data"=>$array);
	}

	private function deldir($path){
	    //给定的目录不是一个文件夹
	    if(!is_dir($path))
	        return null;
	    $fh = opendir($path);
	    while(($row = readdir($fh)) !== false){
	        //过滤掉虚拟目录
	        if($row == '.' || $row == '..'){
	            continue;
	        }
	        if(!is_dir($path.'/'.$row)){
	            unlink($path.'/'.$row);
	        }
	        $this->deldir($path.'/'.$row);
	    }
	    //关闭目录句柄，否则出Permission denied
	    closedir($fh);
	    //删除文件之后再删除自身
	    rmdir($path) ;
	}

}