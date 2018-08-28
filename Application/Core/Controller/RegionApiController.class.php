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
}