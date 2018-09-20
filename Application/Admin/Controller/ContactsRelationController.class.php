<?php
/**
 * 紧急联系人联系关系控制器
 */
namespace Admin\Controller;
use Think\Controller;
class ContactsRelationController extends CommonController {

    //联系关系添加/编辑
    public function editContactsRelation(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/ContactsRelation');
        if(IS_POST){
            $data = I('post.');
            if ($id > 0){
                if($model->create($data, 2)){
                    if ($model->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
            }
            else{
                if($model->create($data, 1)){
                    if ($model->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $model->getError()));
        }
        $relation = $model->getContactsRelationInfo(array('id' => $id));
        $this->assign('info', $relation);
        $this->display();
    }

    //显示紧急联系人联系关系列表
    public function listContactsRelation(){
        $keyword = I('keyword', '', 'trim');
        $model = D('Admin/ContactsRelation');
        $where = array();
        //关键字查询
        if($keyword){
            $where['relation_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getContactsRelationList($where);
        $this->keyword = $keyword;
        $this->assign('list', $list['info']);
        $this->assign('page', $list['page']);
        $this->display();
    }

    public function del(){
        $this->_del('ContactsRelation', 'id');
    }
    public function delFile(){
        $this->_delFile();
    }

    public function uploadImg(){
        $this->_uploadImg();
    }
}