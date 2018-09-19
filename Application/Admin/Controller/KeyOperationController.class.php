<?php
/**
 * @desc 操作日志控制器
 */

namespace Admin\Controller;


class KeyOperationController extends CommonController{

    /**
     * @desc 用户操作日志列表
     */
    public function listKeyOperation(){
        $keywords = I('keyword', '', 'trim');
        $operate_type = I('operate_type', 0, 'intval');
        $min = I('min', '', 'trim');
        $max = I('max', '', 'trim');
        $where = array();
        $model = D('Admin/KeyOperation');
        if($operate_type) $where['k.operate_type'] = $operate_type;
        if($keywords) $where['u.user_name|u.mobile'] = array('like', '%'.$keywords.'%');
        if($min && $max){
            $min = strtotime($min);
            $max = strtotime($max);
            $where['k.operate_time'] = array('between', array($min, $max));
        }
        $list = $model->getKeyOperationList($where);
        $type_string = C('OPERATE_TYPE');
        foreach($list['info'] as &$val){
            $val['operate_time'] = time_format($val['operate_time'], 'Y-m-d H:i');
            $val['nickname'] = empty($val['nickname']) ? $val['user_name'] : $val['nickname'];
            $val['operate_type'] = $type_string[$val['operate_type']];
        }
        unset($val);
        $type_list = returnArrData($type_string);
        $this->type_list = $type_list;
        $this->keyword = $keywords;
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    public function del(){
        $this->_del('KeyOperation', 'id');  //调用父类的方法
    }
}