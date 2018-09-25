<?php
/**
 * 紧急联系人控制器
 */
namespace Admin\Controller;
use Think\Controller;
class ContactsController extends CommonController {

    //显示紧急联系人列表
    public function listContacts(){
        $keyword = I('keyword', '', 'trim');

        $model = D('Admin/Contacts');
        $where = array();
        //关键字查询
        if($keyword){
            $where['u.mobile|u.nickname'] = array('like', '%'. $keyword .'%');
        }
        $field = 'c.*,r.relation_name,u.nickname,u.mobile,u.user_name';
        $list = $model->getContactsList($where, $field);
        foreach($list['info'] as &$val){
            $val['user_name'] = !empty($val['nickname']) ? $val['nickname'] : $val['user_name'];
        }
        $this->keyword = $keyword;
        $this->assign('list', $list['info']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    public function del(){
        $this->_del('Contacts', 'id');
    }
}