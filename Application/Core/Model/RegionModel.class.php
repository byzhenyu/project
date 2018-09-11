<?php
// +----------------------------------------------------------------------
// | Author: liuyang 594353482@qq.com
// +----------------------------------------------------------------------
namespace Core\Model;
use Think\Model;

class RegionModel extends Model {
    
    public function getRegionNameById($id = 0){
        $where['id'] = $id;
        $regionName = $this->where($where)->getField('region_name');
        return $regionName;
    }

    //update by yangchunfu 
    public function getRegionNameByParentId(){
    	$where = array();
    	$parent_id = I('parent_id',-1,'intval');
    	if (!empty($parent_id) && $parent_id != -1) {
    		$where['parent_id'] = array('eq',$parent_id);
    	} else {
    		$where['parent_id'] = array('eq',1);
    	}
    	//获取缓存中的省市县数据
    	$citys = S('parent_id'.$parent_id);
    	if (!$citys) {
    		$where['is_display'] = array('eq',0);
    		$res = M('Region')->where($where)->field('id,region_name')->order('id,region_order')->select();
    		S('parent_id'.$parent_id,$res);
    	} else {
    		$res = $citys;
    	}

    	return $res;
    }

    /**
     * @desc 根据传入参数获取行政区划列表
     * @param $keywords string 关键词 默认获取省列表
     * @param $subDistrict int 0、不显示子行政区 1、显示一级 2、显示2级 3、 返回3级
     * @param $filter int adcode用于行政区下精确查找,如在临沂的河东区下查找需要传入临沂adcode 371300
     * @return mixed
     */
    public function getDistrictListBaseGD($keywords = '中国', $filter = '', $subDistrict = 1){
        if(empty($keywords)) $keywords = '中国';
        $url = 'http://restapi.amap.com/v3/config/district?key=395293e2d3bcbbaabbc3aae0e9244177&keywords='.$keywords.'&subdistrict='.$subDistrict.'&extensions=base&filter='.$filter;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        $output = curl_exec($ch);
        if($output === FALSE ){
            return false;
        }
        curl_close($ch);
        $data = json_decode($output, 'array');
        $list = $data['districts'][0]['districts'];
        $returnData = array();
        foreach($list as &$val){
            $returnData[] = array(
                'adcode' => $val['adcode'],
                'name' => $val['name'],
                'level' => $val['level'],
                'center' => $val['center']
            );
        }
        return $returnData;
    }

    /**
     * 获取按照abc排序的城市列表
     */
    public function getRegionInfo() {

        $citys = S('all_citys');

        if (!$citys) {
            $data = M('Region')->field('id, name, level, first_code')->where(array('level'=>2))->order('first_code, id asc')->select();
            $citys = array();
            foreach ($data as $key => $value) {
                $citys[$value['first_code']][] = $value;

            }
            S('all_citys', $citys);
        }

        return $citys;
    }

}