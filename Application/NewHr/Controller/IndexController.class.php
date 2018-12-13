<?php
namespace NewHr\Controller;
use Common\Controller\HrCommonController;
class IndexController extends HrCommonController{
    public function Index(){
        require_once(APP_PATH . '/NewHr/Conf/menu.php');
        $menus = array();
        foreach($modules as $key => $val){
            $menus[$key]['label'] = $val['label'];
            foreach($val['items'] as $skey => $sval){
                $menus[$key]['items'][$skey]['label'] = $sval['label'];
                $menus[$key]['items'][$skey]['action'] = $sval['action'];
                $menus[$key]['items'][$skey]['class'] = $sval['class'];
            }
        }

        $user_id = HR_ID;
        $user_model = D('Admin/User');
        $company_model = D('Admin/CompanyInfo');
        $userFields = $user_model->getUserInfo(array('user_id' => $user_id), 'head_pic,nickname,user_name');
        if($userFields['head_pic']) {
            $head_pic = $userFields['head_pic'];
        } else {
            $head_pic = 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png';
        }
        if(!empty($userFields['nickname'])) {
            $nickname = $userFields['nickname'];
        } else {
            $nickname = $userFields['user_name'];
        }
        $company_info = $company_model->getCompanyInfoInfo(array('user_id' => $user_id));
        $returnArray = array('nickname' => $nickname, 'head_pic' => $head_pic, 'company_logo' => $company_info['company_logo'], 'company_name' => $company_info['company_name']);
        $this->info = $returnArray;
        $this->menu_list = $menus;
        $this->display();
    }
    public function welcome(){
        $user_id = HR_ID;
        $recruit_model = D('Admin/Recruit');
        //$tags = user_tags($user_id);

        $user_model = D('Admin/User');
        $hr_resume = D('Admin/HrResume');
        $accountLogModel = D('Admin/AccountLog');
        $company_model = D('Admin/CompanyInfo');
        $company_info = $company_model->getCompanyInfoInfo(array('user_id' => $user_id));
        $userRanking = $user_model->getUserRankingInfo($user_id);
        $hrResumeRanking = $hr_resume->getHrResumeRankingInfo($user_id);
        $userFields = $user_model->getUserInfo(array('user_id' => $user_id), 'head_pic,nickname,user_name');
        if($userFields['head_pic']) {
            $head_pic = $userFields['head_pic'];
        } else {
            $head_pic = 'https://shanjian.oss-cn-hangzhou.aliyuncs.com/nopic.png';
        };
        if(!empty($userFields['nickname'])) {
            $nickname = $userFields['nickname'];
        } else {
            $nickname = $userFields['user_name'];
        }
        $resume_number = $hr_resume->getHrResumeCount(array('hr_user_id' => $user_id));
        if(!$hrResumeRanking) $hrResumeRanking = '999+';
        $_time = time_list(3);
        $start_time = $_time['start'];
        $end_time = $_time['end'];
        $account_where = array('change_type' => array('in', array(2, 3, 6)), 'change_time' => array('between', array($start_time, $end_time)));
        $sum_money = $accountLogModel->getAccountLogMoneySum($account_where);
        $return_array = array('user_ranking' => $userRanking, 'resume_ranking' => $hrResumeRanking,'head_pic'=>$head_pic,'nickname'=>$nickname, 'resume_number' => $resume_number, 'month_amount' => fen_to_yuan($sum_money));

        $string_where = D('Admin/HrResume')->getHrTags($user_id, '');
        if(is_array($string_where)){
            $map = false;
        }
        else{
            $map = $string_where;
        }
        /*$map = '';
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
        }*/
        if($map) $where['_string'] = $map;
        if(!$map) $where['_string'] = 'hr_user_id = 0';//无符合条件人选
        $where['hr_user_id'] = array('neq', $user_id);

        $where['status'] = 1;
        $where['is_post'] = array('lt', 2);
        $where['is_shelf'] = 1;
        $list = $recruit_model->getRecruitList($where,'id, position_name, recruit_num, commission, add_time, job_area, position_name, welfare, nature, language_ability, last_token');
        $this->company_info = $company_info;
        $this->ranking = $return_array;
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }
}