<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description    银行卡号管理
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/27 0027 9:48
 * @CreateBy       PhpStorm
 */

namespace Admin\Controller;
use Think\Controller;
class SysBankController extends CommonController {
    protected function _initialize() {
        $this->SysBank = D("Admin/SysBank");
    }
    /**
    * @desc  查看银行列表
    * @param
    * @return mixed
    */
    public function getBankList(){
        $keyword = I('keyword', '');
        if ($keyword) {
            $where['bank_name'] = array('like','%'.$keyword.'%');
        }
        $list = $this->SysBank->getBankList($where);
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }
    /**
    * @desc 编辑银行信息
    * @param  id
    * @return mixed
    */
    public  function editBank(){
        $id = I('id', 0, 'intval');
        $where['id'] = $id;
        if (IS_POST) {
            if ($this->SysBank->create() === false) {
                $this->ajaxReturn(V(0,$this->SysBank->getError()));
            }
            if ($id) {
                if ($this->SysBank->save() !== false) {
                    $this->ajaxReturn(V(1, '编辑成功'));
                }
            } else {
                if ($this->SysBank->add() !== false) {
                    $this->ajaxReturn(V(1, '添加成功'));
                }
            }
            $this->ajaxReturn(V(0, $this->SysBank->getDbError()));
        }
        $bankInfo =  $this->SysBank->where(array('id' => $id))->find();
        $this->Info = $bankInfo;
        $this->display();
    }
    /*删除*/
    public function recycle() {
        $this->_recycle('SysBank','id');
    }
    public function del(){
        $this->_del('SysBank', 'id');
    }
}