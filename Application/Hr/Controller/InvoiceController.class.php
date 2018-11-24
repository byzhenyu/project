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
        $this->Help = D("Hr/Invoice");
        $this->User = D("Hr/User");
    }
    /**
    * @desc  可以申请发票的额度
    * @param
    * @return mixed
    */
    public function userInvoice(){
        $invoice_amount =  $this->User->where(array( 'user_id' => HR_ID))->getField('invoice_amount');
        $this->invoice_amount = $invoice_amount;
        $this->display();
    }
    /**
    * @desc  申请发票
    * @param  data
    * @return mixed
    */
    public function  addInvoice(){

    }

}