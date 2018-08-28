<?php
// +----------------------------------------------------------------------
// | Author: liuyang 594353482@qq.com
// +----------------------------------------------------------------------
namespace Admin\Model;
use Think\Model;

class RegionModel extends Model {

    //获取省市县
    public function getThreLevelName(){

    	//获取缓存中的省市县数据
    	$citys = S('reginData');
    	if (!$citys) {
    		$where['is_display'] = array('eq',0);
    		$res = $this->where($where)->getField('id, region_name', true);
    		S('reginData', $res);
    	} else {
    		$res = $citys;
    	}

    	return $res;
    }

}