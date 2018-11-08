<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class IndexController extends HrCommonController {

    public function index(){
        $this->menu_list = $this->getMenu();
        $this->display();
    }

    private function getMenu() {
        require_once(APP_PATH . '/Hr/Conf/menu.php');
        $menus = array();
        foreach($modules as $key => $val){
                $menus[$key]['label'] = $val['label'];
                foreach($val['items'] as $skey => $sval){
                     $menus[$key]['items'][$skey]['label'] = $sval['label'];
                     $menus[$key]['items'][$skey]['action'] = $sval['action'];
                }
        }
        return $menus;
    }

    public function welcome(){
        $user_id = HR_ID;
        $recruit_model = D('Admin/Recruit');
        $tags = user_tags($user_id);
        $map = '';
        if(count($tags) > 0){
            $where1 = array();
            foreach($tags as &$val){
                $val['job_area'] = rtrim($val['job_area'], ',');
                if(false !== strpos($val['job_position'], '|')){
                    $pos = 'in ('.str_replace('|', ',', $val['job_position']).')';
                }
                else{
                    $pos = '= '.$val['job_position'];
                }
                $where1[] = ' (`job_area` like \''.$val['job_area'].'%\' and `position_id` '.$pos.') ';
            }
            unset($val);
            $map = implode(' or ', $where1);
        }
        if($map) $where['_string'] = $map;
        if(!$map) $where['_string'] = 'hr_user_id = 0';//无符合条件人选
        $where['hr_user_id'] = array('neq', $user_id);
        if($map) $where['_string'] = $map;
        if(!$map) $where['_string'] = 'hr_user_id = 0';//无符合条件人选
        $where['hr_user_id'] = array('neq', $user_id);

        $where['status'] = 1;
        $where['is_post'] = array('lt', 2);
        $list = $recruit_model->getRecruitList($where,'id, position_name, recruit_num, commission, add_time, job_area, position_name, welfare');
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }
}