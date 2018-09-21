<?php
/**
 * 公司列表控制器
 */
namespace Admin\Controller;
use Think\Controller;
class CompanyController extends CommonController {



    //显示公司列表
    public function listCompany(){
        $keyword = I('keyword', '', 'trim');

        $model = D('Admin/Company');
        $where = array();
        //关键字查询
        if($keyword){
            $where['company_name'] = array('like', '%'. $keyword .'%');
        }
        $list = $model->getCompanyList($where);


        $this->keyword = $keyword;
        $this->list = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('Company', 'id');  //调用父类的方法
    }
}