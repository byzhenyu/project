<?php
namespace Admin\Controller;

/**
 * 后台配置控制器
 */
class ConfigController extends CommonController {

    /**
     * 配置管理
     */
    public function listConfig(){
        $group_id   = I('group_id', -1, 'intval');
        $keyword = I('keyword', '', 'trim');
        /* 查询条件初始化 */
        $where = array();
        $where['state'] = 0;
        if($group_id != -1) {
            $where['group'] = $group_id;
        }
        //关键字查询
        if($keyword != '') {
            $where['key|name'] = array('like', '%' . $keyword . '%');
        }
        $list = $this->lists('Config', $where,'id desc');
        $this->assign('list', $list);        
        $this->assign('group', C('CONFIG_GROUP_LIST'));
        $this->assign('group_id', $group_id);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    /**
     * 编辑配置
     */
    public function editConfig(){
        $id = I('id', 0, 'intval');

        $Config = D('Config');
        if(IS_POST){
            if ($id > 0){
                if($Config->create(I('post.'), 2)){
                    if ($Config->save() !== false) {
                        S('DB_CONFIG_DATA',null);
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($Config->create(I('post.'), 1)){
                    if ($Config->add() !== false) {
                        S('DB_CONFIG_DATA',null);
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $Config->getError()));
        }

        /* 获取数据 */
        $config = $Config->field(true)->find($id);

        $this->assign('config', $config);
        $this->display();
    }

    /**
     * 批量保存配置
     */
    public function save($config){
        if($config && is_array($config)){
            $Config = M('Config');
            foreach ($config as $name => $value) {
                $map = array('key' => $name);
                $Config->where($map)->setField('value', $value);
            }
        }
        S('DB_CONFIG_DATA',null);
        $this->ajaxReturn(V(1, '保存成功！'));
    }

    /**
     * [group 获取某个标签的配置参数]
     */
    public function group() {
        $id     =   I('get.id',1);
        $type   =   C('CONFIG_GROUP_LIST');
        $list   =   M("Config")->where(array('state'=>0,'group'=>$id))->field('id,key,name,extra,value,desc,type,sort')->order('sort')->select();

        $this->assign('list', $list);
        $this->assign('id', $id);
        $this->display();
    }    

    /**
     * 删除配置
     */
    public function del(){
        S('DB_CONFIG_DATA',null);
        $this->_del('config', 'id');
    }

}