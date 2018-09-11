<?php
namespace Core\Controller;
use Common\Controller\ApiCommonController;
/**
 * 客户管理API
 * create by yangchunfu <QQ:779733435>
 */
class RegionApiController extends ApiCommonController {
    
    public function getRegionList(){
        $regionList = D('Core/Region')->getRegionNameByParentId();
  
        $this->apiReturn(V(1, '区域列表', $regionList));
    }

    /**
     * 获取按照abc排序的城市列表
     */
    public function getReginListForWx() {
        $regionList = D('Core/Region')->getRegionInfo();

        $this->apiReturn(V(1, '区域列表', $regionList));
    }
}