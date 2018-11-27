<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description    发票管理控制器
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/26 0026 11:09
 * @CreateBy       PhpStorm
 */
namespace Admin\Controller;
use Think\Controller;
class InvoiceController extends CommonController{
    protected function _initialize() {
        $this->Invoice = D("Hr/Invoice");
    }
    /**
    * @desc  发票列表
    * @param
    * @return mixed
    */
    public function invoiceList(){
        $invoiceList = $this->Invoice->invoiceList();
        $this->list = $invoiceList['list'];
        $this->page = $invoiceList['page'];
        $this->display();
    }
    public function invoiceDetail(){
        $id = I('id', 0, 'intval');
        $where['i.id'] = $id;
        if(IS_POST){
            $data = I('post.');
            if($data['status'] ==  0){
                $this->ajaxReturn(V(0, '请更改发票状态'));
            }else if($data['status']  == 1 && $data['invoice_type']  == 1){
                  if(empty($data['express_name']) || empty($data['express_no'])){
                      $this->ajaxReturn(V(0, '请填写快递名称或单号'));
                  }
            }else{
                 /*拒绝开票增加用户的开票额度*/
                 D('HR/User')->where(array('user_id'=> $data['user_id']))->setInc('invoice_amount',$data['invoice_amount']);
            }
            $invoiceRes  = $this->Invoice->where(array('id' => $data['id']))->save($data);
            if($invoiceRes){
                $this->ajaxReturn(V(1, '操作成功'));
            }else{
                $this->ajaxReturn(V(0, $this->Invoice->getError()));
            }
        }
        $info = $this->Invoice->getInvoiceInfo($where);
        $this->info = $info;
        $this->display();
    }
    public function del() {
        $this->_del('Invoice', 'id');
    }
}