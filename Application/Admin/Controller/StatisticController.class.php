<?php
/**
 * 统计模型类
 */
namespace Admin\Controller;
use Think\Controller;
class StatisticController extends CommonController {

    /**
     * @desc 统计列表
     * @param type 1、用户注册量柱形图统计/用户注册量城市分布统计 2、悬赏城市分布统计 3、推荐数量/推荐成交数量统计
     */
    public function listStatistic(){
        $type = I('type', 1, 'intval');
        $tpl = array(1 => 'userStatistic' ,2 => 'recruitStatistic', 3 => 'resumeStatistic');
        $user_model = D('Admin/User');
        $user_statistic_help = array(1 => 'days', 2 => 'weeks', 3 => 'months', 4 => 'years');
        $user_statistic_keys = array_keys($user_statistic_help);
        $where = array();

        //用户日/周/月/年注册统计/城市注册量分布统计
        if(1 == $type){
            $user_statistic = array();
            foreach($user_statistic_keys as &$val){
                $t_register_time = $this->mk_time($val);
                $where['register_time'] = array('between', array($t_register_time['start'], $t_register_time['end']));
                $t_user_register = $user_model->userStatistic($where);
                $user_statistic[$user_statistic_help[$val]] = $t_user_register;
            }
            unset($val);
            unset($where);
            $statistic_x = array_keys($user_statistic);
            $statistic_y = array_values($user_statistic);
            $this->statistic_x = json_encode($statistic_x);
            $this->statistic_y = json_encode($statistic_y);
            $where = array();
            $user_city_statistic = $user_model->userStatistic($where, false, 2);
            $city_name_arr = array();
            foreach($user_city_statistic as &$val){
                $city_name_arr[] = $val['city_name'];
            }
            unset($val);
            $user_city_return = array();
            $user_city_return['name'] = $city_name_arr;
            $user_city_data_array = array();
            foreach($city_name_arr as &$city_val){
                foreach($user_city_statistic as &$statistic_val){
                    if($statistic_val['city_name'] == $city_val){
                        $user_city_data_array[] = array(
                            'name' => $city_val,
                            'value' => $statistic_val['user_statistic']
                        );
                        break;
                    }
                }
            }
            $user_city_return['data'] = $user_city_data_array;
            $this->result = json_encode($user_city_return);
        }

        //悬赏城市分布统计
        if(2 == $type){
            $recruit_sql = 'select count(1) as number,city_name from (SELECT a.id, SUBSTRING_INDEX(SUBSTRING_INDEX(a.job_area, \',\', b.help_topic_id + 1 ), \',\' ,- 1 ) AS city_name FROM ln_recruit a LEFT JOIN mysql.help_topic b ON b.help_topic_id < (LENGTH(a.job_area) - LENGTH(REPLACE(a.job_area, \',\', \'\')) + 1)) as tem group by city_name';
            $model = M();
            //悬赏统计
            $recruit_statistic = $model->query($recruit_sql);
            $city_name_arr = array();
            foreach($recruit_statistic as &$val){
                $city_name_arr[] = $val['city_name'];
            }
            unset($val);
            $recruit_statistic_result = array();
            $recruit_statistic_result['name'] = $city_name_arr;
            $recruit_data_array = array();
            foreach($city_name_arr as &$city_val){
                foreach($recruit_statistic as &$statistic_val){
                    if($statistic_val['city_name'] == $city_val){
                        $recruit_data_array[] = array('value' => $statistic_val['number'], 'name' => $city_val);
                        break;
                    }
                }
            }
            unset($city_val);
            unset($statistic_val);
            $recruit_statistic_result['data'] = $recruit_data_array;
            $this->result = json_encode($recruit_statistic_result);
        }

        //推荐统计/推荐成功数量
        if(3 == $type){
            $recruit_resume_model = D('Admin/RecruitResume');
            $interviewModel = D('Admin/Interview');
            $resume_statistic = array();
            foreach($user_statistic_keys as &$val){
                $t_resume_time = $this->mk_time($val);
                $where['add_time'] = array('between', array($t_resume_time['start'], $t_resume_time['end']));
                $t_resume_statistic_select = $recruit_resume_model->recruitResumeStatistic($where);
                $t_sel_arr = array();
                foreach($t_resume_statistic_select as &$sel) $t_sel_arr[] = $sel['id']; unset($sel);
                if(count($t_sel_arr) > 0) {
                    $interview_where = array('i.state' => 1, 'i.recruit_resume_id' => array('in', $t_sel_arr));
                    $success_number = $interviewModel->getInterviewCount($interview_where);
                }
                else{
                    $success_number = 0;
                }
                $resume_statistic[] = array(
                    'keys' => $user_statistic_help[$val],
                    $user_statistic_help[$val] => count($t_resume_statistic_select),
                    'success_number' => $success_number
                );
            }
            unset($val);
            unset($where);
            $max_number = 0;
            $statistic_success = array();
            $total_recruit_number = array();
            $statistic_name = array_values($user_statistic_help);
            foreach($statistic_name as &$val){
                foreach($resume_statistic as &$resume_val){
                    if($val == $resume_val['keys']){
                        $statistic_success[] = $resume_val['success_number'];
                        $total_recruit_number[] = $resume_val[$val];
                    }
                    if('years' == $val) $max_number = $resume_val[$val];
                }
            }
            unset($val);
            unset($resume_val);
            $max_number *= 1.3;
            $this->max = ceil($max_number);
            $this->success_statistic = json_encode($statistic_success);
            $this->total_statistic = json_encode($total_recruit_number);
            $this->statistic_name = json_encode($statistic_name);
        }
        $this->display($tpl[$type]);
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