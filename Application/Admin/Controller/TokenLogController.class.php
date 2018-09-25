<?php
/**
 * 赏金日志控制器
 */
namespace Admin\Controller;
use Think\Controller;
class TokenLogController extends CommonController {


    //赏金日志列表
    public function listTokenLog(){
        $keyword = I('keyword', '', 'trim');
        $recruit_id = I('recruit_id', 0, 'intval');
        $model = D('Admin/TokenLog');
        $where = array('l.recruit_id' => $recruit_id);
        if($keyword){
            $where['u.mobile|u.nickname'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getTokenLogList($where);
        foreach($list['info'] as &$val){
            $val['nickname'] = !empty($val['nickname']) ? $val['nickname'] : $val['user_name'];
            $val['type'] = token_log_type($val['type']);
        }
        unset($val);
        $this->keyword = $keyword;
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    public function del(){
        $this->_del('TokenLog', 'id');
    }
}