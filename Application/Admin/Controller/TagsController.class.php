<?php
/**
 * 标签类型
 */
namespace Admin\Controller;
use Think\Controller;
class TagsController extends CommonController {

    //标签添加/编辑
    public function editTags(){
        $id = I('id', 0, 'intval');
        $model = D('Admin/Tags');
        if(IS_POST) {
            $data = I('post.');
            if ($id > 0) {
                if ($model->create($data, 2)) {
                    if ($model->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if ($model->create($data, 1)) {
                    if ($model->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }

            $this->ajaxReturn(V(0, $model->getError()));
        }

        $tags_type = C('TAGS_TYPE');
        $tags_type = returnArrData($tags_type);
        $tags_where = array('id' => $id);
        $tags = $model->getTagsInfo($tags_where);
        $this->tags_type = $tags_type;
        $this->assign('info', $tags);
        $this->display();
    }

    //显示标签列表
    public function listTags(){
        $keyword = I('keyword', '', 'trim');
        $type_id = I('tags_type', 0, 'intval');
        $model = D('Admin/Tags');
        $where = array();
        if($keyword){
            $where['tags_name'] = array('like', '%'. $keyword .'%');
        }
        if($type_id) $where['tags_type'] = $type_id;
        $list = $model->getTagsList($where, '*');
        $tags_type = C('TAGS_TYPE');
        foreach ($list as &$val){
            $val['tags_type'] = $tags_type[$val['tags_type']];
        }
        unset($val);
        $tags_type = returnArrData($tags_type);
        $this->type_id = $type_id;
        $this->tags_type = $tags_type;
        $this->keyword = $keyword;
        $this->list = $list;
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('Tags', 'id');  //调用父类的方法
    }
}