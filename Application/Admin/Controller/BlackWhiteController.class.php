<?php
/**
 * @desc 黑/白名单控制器
 */

namespace Admin\Controller;


class BlackWhiteController extends CommonController{

    //黑/白名单添加/编辑
    public function editBlackWhite(){
        $id = I('id', 0, 'intval');
        $type = I('type', 1, 'intval');
        $model = D('Admin/BlackWhite');
        if(IS_POST){
            $data = I('post.');
            if ($id > 0){
                if($model->create($data, 2)){
                    if ($model->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
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
        $dispose_type = black_white_type(0);
        $type_list = returnArrData($dispose_type);
        $where = array('id' => $id);
        $black_info = $model->getBlackWhiteInfo($where);
        $this->type = $type;
        $this->info = $black_info;
        $this->type_list = $type_list;
        $this->display();
    }

    /**
     * @desc 黑/白名单列表
     */
    public function listBlackWhite(){
        $keywords = I('keyword', '', 'trim');
        $type_id = I('type', 0, 'intval');
        $model = D('Admin/BlackWhite');
        $where = array('type' => $type_id);
        if($keywords) $where['dispose_value'] = array('like', '%'.$keywords.'%');
        $list = $model->getBlackWhiteList($where);
        foreach($list['info'] as &$val){
            $val['dispose_type'] = black_white_type($val['dispose_type']);
        }
        $this->keyword = $keywords;
        $this->type = $type_id;
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    public function del(){
        $this->_del('BlackWhite', 'id');
    }
}