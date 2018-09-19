<?php
// +----------------------------------------------------------------------
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

    /**
     * @desc 获取地区信息
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getRegionInfo($where, $field = false){
        if(!$field) $field = '';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

    /**
     * @desc 获取地区列表
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getRegionList($where, $field = false){
        if(!$field) $field = '*';
        $list = $this->where($where)->field($field)->select();
        return $list;
    }

}