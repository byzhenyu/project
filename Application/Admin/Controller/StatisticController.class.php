<?php
/**
 * 统计模型类
 */
namespace Admin\Controller;
use Think\Controller;
class StatisticController extends CommonController {

    /**
     * @desc 统计列表
     * @param type 1、用户注册量柱形图统计/用户注册量城市分布统计 2、悬赏城市分布统计 3、推荐数量/推荐成交数量统计 4、 佣金统计
     */
    public function listStatistic(){
        $type = I('type', 1, 'intval');
        $tpl = array(1 => 'userStatistic' ,2 => 'recruitStatistic', 3 => 'resumeStatistic', 4 => 'commissionStatistic');
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
            //用户分布城市
            $user_city_return = $this->user_city_statistic();
            $this->result = json_encode($user_city_return);
        }

        //悬赏总计押金
        if(in_array($type, array(2, 4))){
            $account_model = D('Admin/AccountLog');
            $in_amount_where = array('change_type' => 4);
            $out_amount_where = array('change_type' => 5);
            $in_amount = $account_model->getAccountLogMoneySum($in_amount_where);
            $out_amount = $account_model->getAccountLogMoneySum($out_amount_where);
            $total_amount = fen_to_yuan($in_amount - $out_amount);
        }

        //悬赏城市分布统计
        if(2 == $type){
            $recruit_sql = 'select count(1) as number,city_name from (SELECT a.id, SUBSTRING_INDEX(SUBSTRING_INDEX(a.job_area, \'|\', b.help_topic_id + 1 ), \'|\' ,- 1 ) AS city_name FROM ln_recruit a LEFT JOIN mysql.help_topic b ON b.help_topic_id < (LENGTH(a.job_area) - LENGTH(REPLACE(a.job_area, \'|\', \'\')) + 1)) as tem group by city_name';
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
            $recruit_where = array('last_token' => array('gt', 0));
            $recruit_number = D('Admin/Recruit')->getRecruitCount($recruit_where);
            $this->recruit_number = $recruit_number;
            $this->recruit_amount = $total_amount;
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
            $hr_resume_model = D('Admin/HrResume');
            $hr_resume = $hr_resume_model->getHrResumeSel();
            $hr_number = count($hr_resume);
            $resume_total = 0;
            foreach($hr_resume as &$r_val) $resume_total += $r_val['number'];
            $avg = round($resume_total / $hr_number, 2);
            $this->avg = $avg;
            $this->resume_total = $resume_total;
            $total_interview_count = $interviewModel->getInterviewCount(array('i.state' => 1));
            $this->resume_number = $recruit_resume_model->getRecruitResumeNum();
            $this->number = $total_interview_count;
            $this->max = ceil($max_number);
            $this->success_statistic = json_encode($statistic_success);
            $this->total_statistic = json_encode($total_recruit_number);
            $this->statistic_name = json_encode($statistic_name);
        }

        //悬赏佣金统计
        if(4 == $type){
            //已结算佣金
            $account_statistic = array();
            foreach($user_statistic_keys as &$val){
                $t_account_time = $this->mk_time($val);
                $t_account_where = array('change_time' => array('between', array($t_account_time['start'], $t_account_time['end'])), 'change_type' => array('in', array(2, 3, 4, 5)));
                $t_plat_money = 0;
                $t_recruit_money = 0;
                $t_back_money = 0;
                $t_share_money = 0;
                $t_account_list = $account_model->getAccountLogSum($t_account_where);
                foreach($t_account_list as &$list_val){
                    if(0 == $list_val['user_id'] && in_array($list_val['change_type'], array(2, 3))){
                        $t_plat_money += $list_val['user_money'];
                        continue;
                    }
                    if(in_array($list_val['change_type'], array(2, 3))) {
                        $t_share_money += $list_val['user_money'];
                        continue;
                    }
                    if(4 == $list_val['change_type']){
                        $t_recruit_money += $list_val['user_money'];
                        continue;
                    }
                    if(5 == $list_val['change_type']){
                        $t_back_money += $list_val['user_money'];
                        continue;
                    }
                }
                $t_total_money = $t_recruit_money - $t_back_money;
                $t_plat_money = fen_to_yuan($t_plat_money);
                $t_share_money = fen_to_yuan($t_share_money);
                $t_total_money = fen_to_yuan($t_total_money);
                $account_statistic[] = array(
                    'keys' => $user_statistic_help[$val],
                    $user_statistic_help[$val] => $t_total_money,
                    'plat' => $t_plat_money,
                    'share' => $t_share_money
                );
            }
            unset($list_val);
            unset($val);
            $statistic_y = array_values($user_statistic_help);
            $plat_arr = array();
            $share_arr = array();
            $total_arr = array();
            foreach($statistic_y as &$d_val){
                foreach($account_statistic as &$val){
                    if($val['keys'] == $d_val){
                        $plat_arr[] = $val['plat'];
                        $total_arr[] = $val[$d_val];
                        $share_arr[] = $val['share'];
                        continue;
                    }
                }
            }
            unset($d_val);
            unset($val);
            //待结算佣金
            $recruit_model = D('Admin/Recruit');
            $pending_where = array('last_token' => array('gt', 0));
            $pending_field = 'recruit_num,sum(commission) as commission,sum(get_resume_token) as resume_token,sum(entry_token) as entry_token';
            $pending_y = array();
            $pending_plat = array();
            $pending_total = array();
            $pending_share = array();
            $recruit_radio = C('RATIO');
            $max_resume = C('MAX_RESUME');
            foreach($user_statistic_keys as &$val){
                $t_recruit_time = $this->mk_time($val);
                $pending_where['add_time'] = array('between', array($t_recruit_time['start'], $t_recruit_time['end']));
                $pendingSel = $recruit_model->getRecruitPendingSel($pending_where, $pending_field);
                $pending_y[] = $user_statistic_help[$val];
                $t_pending_total = 0;
                $t_pending_plat = 0;
                $t_pending_share = 0;
                foreach($pendingSel as &$sel_val){
                    $t_share = $max_resume * ($sel_val['resume_token']) + ($sel_val['recruit_num'] * $sel_val['entry_token']);
                    $t_pending_total += fen_to_yuan($sel_val['commission']);
                    $t_pending_share += fen_to_yuan($t_share);
                    $t_pending_plat += fen_to_yuan($t_share * $recruit_radio / 100);
                }
                $pending_total[] = $t_pending_total;
                $pending_plat[] = $t_pending_plat;
                $pending_share[] = $t_pending_share;
            }
            unset($sel_val);
            unset($val);

            $this->pending_y = json_encode($pending_y);
            $this->pending_total = json_encode($pending_total);
            $this->pending_share = json_encode($pending_share);
            $this->pending_plat = json_encode($pending_plat);
            $this->total_statistic = $total_amount;
            $this->statistic_y = json_encode($statistic_y);
            $this->plat_s = json_encode($plat_arr);
            $this->share_s = json_encode($share_arr);
            $this->total_s = json_encode($total_arr);
        }
        $this->display($tpl[$type]);
    }

    /**
     * @desc 统计下载
     * @extra 1、用户分布城市数量导出 2、悬赏统计导出 3、推荐统计导出 4、佣金统计导出
     */
    public function statisticExport(){
        $type = I('type', 1, 'intval');
        if(1 == $type){
            $user_city_statistic = $this->user_city_statistic();
            $name = $user_city_statistic['name'];
            $title_array = array('城市名称', '注册数量');
            $arr = array();
            $name_keys = array_keys($name);
            foreach($name_keys as &$val){
                $arr[] = array($name[$val], $user_city_statistic['data'][$val]['value']);
            }
            unset($val);
            array_unshift($arr, $title_array);
            create_xls($arr, '闪荐科技用户分布城市统计.xls', '闪荐科技用户分布城市统计', '闪荐科技用户分布城市统计', array('A', 'B'));
        }
    }

    /**
     * @desc 用户分布城市数量统计
     * @return array
     */
    private function user_city_statistic(){
        $user_model = D('Admin/User');
        $where = array();
        $user_city_statistic = $user_model->userStatistic($where, false, 2);
        $city_name_arr = array();
        foreach($user_city_statistic as &$val){
            if(!$val['city_name']) continue;
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
        return $user_city_return;
    }

    /**
     * @desc 时间生成
     * @param $type 1、本日 2、本周 3、本月 4、本年
     * @return array
     */
    public function mk_time($type){
        switch($type){
            case 1://本日
                $start = mktime(0,0,0,date('m'), date('d'), date('Y'));
                $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                break;
            case 2://本周
                $date_w = date('w');
                if($date_w == 0) $date_w = 7;
                $start = mktime(0,0,0,date('m'),date('d')-$date_w+1,date('Y'));
                $end = mktime(23,59,59,date('m'),date('d')-$date_w+7,date('Y'));
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
                $t = $this->mk_time(1);
                $start = $t['start'];
                $end = $t['end'];
                break;
        }
        return array('start' => $start, 'end' => $end);
    }
}