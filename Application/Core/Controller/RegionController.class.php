<?php
namespace Core\Controller;
use Think\Controller;
class RegionController extends Controller {

    public function getDataByParentId() {
    	$regionList = D('Core/Region')->getRegionNameByParentId();

        $this->ajaxReturn($regionList);
    }
    
    //清空省市县缓存
	public function clearAllRegionData() {
    	for ($i = 0; $i < 6000; $i++) {
    		S('parent_id'.$i,null);
    	}
    	p("清理成功!");die;
    }

    public function getRegionData(){
        $keywords = I('key', '', 'trim');
        $adCode = I('code', 0, 'intval');
        $data = D('Core/Region')->getDistrictListBaseGD($keywords, $adCode);
        $this->ajaxReturn($data);
    }

}