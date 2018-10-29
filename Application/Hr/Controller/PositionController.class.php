<?php
/**
 * 职位管理类
 */
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class PositionController extends HrCommonController {

    /**
     * @desc 联动-获取职位信息列表
     */
    public function getPositionList(){
        $industry_id = I('industry_id', 0, 'intval');
        $position_list = D('Admin/Position')->getIndustryPositionList(array('industry_id' => $industry_id));
        $this->ajaxReturn(V(1, '', $position_list));
    }
    public function getPositionChildrenList(){
        $position_id = I('position_id', 0, 'intval');
        if($position_id > 0){
            $where = array('parent_id' => $position_id);
            $position_list = D('Admin/Position')->getPositionList($where, false, 'sort', false);
            $this->ajaxReturn(V(1, '', $position_list));
        }
    }
}