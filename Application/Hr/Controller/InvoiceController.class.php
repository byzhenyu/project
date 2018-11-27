<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 *
 * @Description    InvoiceController
 * @Author         (wangzhenyu/byzhenyu@qq.com)
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2018/11/24 0024 10:39
 * @CreateBy       PhpStorm
 */

namespace Hr\Controller;
use Common\Controller\HrCommonController;
class InvoiceController extends HrCommonController {
    protected function _initialize() {
        $this->Invoice = D("Hr/Invoice");
        $this->User = D("Hr/User");
    }
    /**
    * @desc  可以申请发票的额度
    * @param
    * @return mixed
    */
    public function userInvoice(){
        $userInfo =  $this->User->where(array( 'user_id' => HR_ID))->find();
        $this->userInfo = $userInfo;
        $this->display();
    }
    /**
    * @desc 发票详情
    * @param HR_ID
    * @return mixed
    */
    public function invoiceList(){
        $where['i.hr_user_id'] = HR_ID;
        $invoiceList = $this->Invoice->invoiceList($where);
        $this->list = $invoiceList['list'];
        $this->page = $invoiceList['page'];
        $this->display();
    }
    /**
    * @desc  申请发票
    * @param  data
    * @return mixed
    */
    public function  addInvoice(){
        $id = I('id', 0 ,'intval');
        $userInvoice_amount = $this->User->where(array( 'user_id' => HR_ID))->getField('invoice_amount');
        $info = $this->Invoice->where(array('id' => $id))->find();
        if(IS_POST){
            $data = I('post.');
            $data['hr_user_id'] = HR_ID;
            $data['invoice_amount'] = yuan_to_fen($data['invoice_amount']);
            if($id > 0){
                  if($data['invoice_amount'] > $info['invoice_amount']){
                      $invoice_amount = $data['invoice_amount'] - $info['invoice_amount'];
                      $changeInvoice_amount = array(
                          'invoice_amount' => array('exp','invoice_amount - '.$invoice_amount)
                      );
                      if($userInvoice_amount < $invoice_amount){
                          $this->ajaxReturn(V(0,'可开的发票余额不足'));
                      }
                  }elseif($data['invoice_amount'] < $info['invoice_amount']){
                      $invoice_amount  = $info['invoice_amount'] - $data['invoice_amount'];
                      $changeInvoice_amount = array(
                          'invoice_amount' => array('exp','invoice_amount + '.$invoice_amount)
                      );
                  }
                M()->startTrans();
                $invoiceRes = $this->Invoice->where(array('id' => $data['id']))->save($data);
                if($data['invoice_amount'] != $info['invoice_amount']){
                    $hrRes  = $this->User->where(array( 'user_id' => HR_ID))->save($changeInvoice_amount);
                }else{
                    $hrRes = true;
                }
                if($invoiceRes && $hrRes){
                    M()->commit();
                    $this->ajaxReturn(V(1, '修改成功'));
                }else{
                    M()->rollback();
                    $this->ajaxReturn(V(0, '修改失败'));
                }
            }else{
                if($userInvoice_amount < $data['invoice_amount']){
                    $this->ajaxReturn(V(0,'可开的发票余额不足'));
                }
            }
            if($this->Invoice->create($data) !== false){
                  M()->startTrans();
                  $invoiceRes = $this->Invoice->add($data);
                  $hrRes  = $this->User->where(array( 'user_id' => HR_ID))->setDec('invoice_amount',$data['invoice_amount']);
                  if($invoiceRes && $hrRes){
                      M()->commit();
                      $this->ajaxReturn(V(1, '添加成功'));
                  }else{
                      M()->rollback();
                      $this->ajaxReturn(V(0, '添加失败'));
                  }
            }else{
                $this->ajaxReturn(V(0, $this->Invoice->getError()));
            }
        }
        $this->userInvoice_amount = $userInvoice_amount;
        $this->info = $info;
        $this->display();
    }

}