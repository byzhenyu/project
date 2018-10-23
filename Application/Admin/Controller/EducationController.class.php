<?php
/**
 * 学历控制器
 */
namespace Admin\Controller;
use Think\Controller;
class EducationController extends CommonController {

    //学历关系添加/编辑
    public function editEducation(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Education');
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
        $edu = $model->getEducationInfo(array('id' => $id));
        $this->info = $edu;
        $this->display();
    }

    //学历列表
    public function listEducation(){
        $keyword = I('keyword', '', 'trim');
        $model = D('Admin/Education');
        $where = array();
        if($keyword){
            $where['education_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getEducationList($where);
        $this->keyword = $keyword;
        $this->list = $list;
        $this->display();
    }

    public function del(){
        $this->_del('Education', 'id');
    }
    // 删除图片
    public function delFile(){
        $this->_delFile();  //调用父类的方法
    }

    // 上传图片
    public function uploadImg(){
        $this->_uploadImg();  //调用父类的方法
    }
}