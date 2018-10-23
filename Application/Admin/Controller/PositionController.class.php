<?php
/**
 * 职位管理类
 */
namespace Admin\Controller;
use Think\Controller;
class PositionController extends CommonController {

    //职位添加/编辑
    public function editPosition(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Position');
        if(IS_POST){
            $data = I('post.');
            if ($id > 0){
                if($model->create($data, 2)){
                    if ($model->where(array('id' => $id))->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
            } else {
                if($model->create($data, 1)){
                    if ($model->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                    else{
                        $this->ajaxReturn(V(0, $model->getError()));
                    }
                }
            }

            $this->ajaxReturn(V(0, $model->getError()));
        }
        $industryModel = D('Admin/Industry');
        $industryList = $industryModel->getIndustryList();
        $position = M('Position')->find($id);
        $this->info = $position;
        $this->industry = $industryList;
        $this->display();
    }

    //显示职位列表
    public function listPosition(){
        $keyword = I('keyword', '', 'trim');
        $industry_id = I('industry_id', 0, 'intval');
        $model = D('Admin/Position');
        if(!$industry_id) $industry_id = 1;
        $where = array('industry_id' => $industry_id);
        //关键字查询
        if($keyword){
            $where['position_name'] = array('like', '%'. $keyword .'%');
        }
        if($industry_id) $where['industry_id'] = $industry_id;
        $list = $model->getTree($where);
        $industryModel = D('Admin/Industry');
        $industryList = $industryModel->getIndustryList();
        $returnList = array();
        foreach($list as &$val){
            if(in_array($val['level'], array(0, 1))) $returnList[] = $val;
        }
        unset($val);
        $this->keyword = $keyword;
        $this->industry = $industryList;
        $this->list = $list;
        $this->industry = $industryList;
        $this->industry_id = $industry_id;
        $this->display();
    }

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

    /**
     * @desc 职业信息导入
     */
    public function add_more_position(){
        if(IS_POST){
            $excelController = A('Excel');
            $upload = $excelController->uploadPosition();
            $this->ajaxReturn($upload);
        }
        $this->display('add_more_position');
    }

    // 放入回收站
    public function del(){
        $this->_del('Position', 'id');//调用父类的方法
    }
}