<?php
namespace Invite\Controller;
use Common\Controller\CommonController;

class InviteController extends CommonController {

    public function index(){
        $hr_id = I('hr_id', 0, 'intval');
        $resume_id = I('resume_id', 0, 'intval');
        $true_name = I('true_name', '', 'trim');
        $company_name = I('company_name', '', 'trim');
        $return_arr = array(
            'hr_id' => $hr_id,
            'resume_id' => $resume_id,
            'true_name' => $true_name,
            'company_name' => $company_name
        );
        $this->info = $return_arr;
        $this->display();
    }
}
