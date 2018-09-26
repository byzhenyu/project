<?php
/**
 * 统计模型类
 */
namespace Admin\Controller;
use Think\Controller;
class StatisticController extends CommonController {

    /**
     * @desc 统计列表
     */
    public function listStatistic(){
        $user_model = D('Admin/User');
        $user_statistic_help = array(1 => 'days', 2 => 'weeks', 3 => 'months', 4 => 'years');
        $user_statistic_keys = array_keys($user_statistic_help);
        $user_statistic = array();
        $where = array();
        foreach($user_statistic_keys as &$val){
            $t_register_time = $this->mk_time($val);
            $where['register_time'] = array('between', array($t_register_time['start'], $t_register_time['end']));
            $t_user_register = $user_model->userStatistic($where);
            $user_statistic[$user_statistic_help[$val]] = $t_user_register;
        }
        unset($val);
        unset($where);
        echo '用户注册<hr />';
        p($user_statistic);
        echo '城市统计<hr />';
        $where = array();
        $user_city_statistic = $user_model->userStatistic($where, false, 2);
        p($user_city_statistic);
        echo '悬赏城市<hr />';
        $recruit_sql = 'select count(1) as number,city_name from (SELECT a.id, SUBSTRING_INDEX(SUBSTRING_INDEX(a.job_area, \',\', b.help_topic_id + 1 ), \',\' ,- 1 ) AS city_name FROM ln_recruit a LEFT JOIN mysql.help_topic b ON b.help_topic_id < (LENGTH(a.job_area) - LENGTH(REPLACE(a.job_area, \',\', \'\')) + 1)) as tem group by city_name';
        $model = M();
        $recruit_statistic = $model->query($recruit_sql);
        p($recruit_statistic);
        echo '推荐统计<hr />';
        $recruit_resume_model = D('Admin/RecruitResume');
        $resume_statistic = array();
        foreach($user_statistic_keys as &$val){
            $t_resume_time = $this->mk_time($val);
            $where['add_time'] = array('between', array($t_resume_time['start'], $t_resume_time['end']));
            $t_resume_statistic_select = $recruit_resume_model->recruitResumeStatistic($where);
            $t_sel_arr = array();
            foreach($t_resume_statistic_select as &$sel) $t_sel_arr[] = $sel['id']; unset($sel);
            if(count($t_sel_arr) > 0) {
                $success_number = 1;
            }
            else{
                $success_number = 0;
            }
            $resume_statistic[$user_statistic_help[$val]] = count($t_resume_statistic_select);
        }
        unset($val);
        unset($where);
        p($resume_statistic);
    }

    public function mk_time($type){
        switch($type){
            case 1://本日
                $start = mktime(0,0,0,date('m'), date('d'), date('Y'));
                $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                break;
            case 2://本周
                $start = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
                $end = mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
                break;
            case 3://本月
                $start = mktime(0,0,0,date('m'),1,date('Y'));
                $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
                break;
            case 4://本年
                $start = mktime(0,0,0,1,1,date('Y'));
                $end = mktime(0,0,0,12,31,date('Y'));
                break;
            default:
                $start = mktime(0,0,0,date('m'), date('d'), date('Y'));
                $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                break;
        }
        return array('start' => $start, 'end' => $end);
    }
}