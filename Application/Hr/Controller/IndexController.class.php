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
        $job_area = $tags['area'];
        $position = $tags['pos'];
        $where1 = array();
        if($job_area){
            foreach($job_area as &$val){
                $where1[] = '`job_area` = \''.$val.'\'';
            }
            unset($val);
        }
        $where2 = array();
        if($position){
            foreach($position as &$val){
                $where2[] = '`position_id` = '.$val;
            }
            unset($val);
        }
        $position_string = implode(' or ', $where2);
        $area_string = implode(' or ', $where1);
        $map = '('.$position_string.') and ('.$area_string.')';
        if(count($where1) == 0) $map = $position_string;
        if(count($where2) == 0) $map = $area_string;
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