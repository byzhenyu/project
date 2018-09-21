<?php
/**
 * HR公司信息
 */
namespace Admin\Controller;
use Think\Controller;
class CompanyInfoController extends CommonController {

    //HR公司详情
    public function companyInfoDetail(){
        $user_id = I('user_id', 0, 'intval');
        $model = D('Admin/CompanyInfo');
        $where = array('user_id' => $user_id);
        $res = $model->getCompanyInfoInfo($where);
        if($res['company_pic']){
            $res['company_pic'] = explode(',', $res['company_pic']);
            $res['company_pic'] = returnArrData($res['company_pic']);
        }
        $this->info = $res;
        $this->display();
    }

    public function del(){
        $this->_del('CompanyInfo', 'id');
    }
}