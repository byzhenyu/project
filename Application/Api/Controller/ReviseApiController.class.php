<?php
/**
 * @desc 闪荐二期修改接口功能
 */
namespace Api\Controller;
use Common\Controller\ApiUserCommonController;

class ReviseApiController extends ApiUserCommonController{

    /**
     * @desc 更改求职状态
     * @param incumbency 1、接收推荐 0、不接受推荐
     * @extra TODO 短信发送
     */
    public function setIncumbency(){
        $user_model = D('Admin/User');
        $incumbency = I('incumbency', 0, 'intval');
        $save = array('is_incumbency' => $incumbency);
        $res = $user_model->where(array('user_id' => UID))->save($save);
        if(false !== $res){
            $this->apiReturn(V(1, '求职状态更改成功！'));
        }
        else{
            $this->apiReturn(V(0, '求职状态更改失败！'));
        }
    }

    /**
     * @desc 获取首页数据
     * @param position_id int 导航栏目位置 1、HR  0、求职者
     * @param ad_position int banner图位置 1、首页[默认] 2、合伙人
     * @extra nav:导航栏目 notice:公告列表 banner:banner轮播图
     */
    public function getHomeData(){
        $return_list = array();
        $return_list['nav'] = $this->navList();
        $return_list['notice'] = $this->noticeList();
        $return_list['banner'] = $this->bannerList();
        $this->apiReturn(V(1, '首页数据', $return_list));
    }

    /**
     * @desc 获取公告详情
     */
    public function getArticleInfo(){
        $article_id = I('id', 0, 'intval');
        $this->apiReturn(V(1, '', C('IMG_SERVER').'/index.php/Api/PublicApi/noticeInfo/id/'.$article_id));
    }

    /**
     * @desc 申请成为合伙人
     */
    public function applyPartner(){
        $data = I('post.', '');
        $model = D('Admin/Partner');
        $create = $model->create($data, 1);
        if(false !== $create){
            $res = $model->add($data);
            if($res){
                $this->apiReturn(V(1, '合伙人申请成功！'));
            }
        }
        $this->apiReturn(V(0, $model->getError()));
    }

    /**
     * @desc 每日任务页面
     * @extra task:任务列表 rank:HR排名信息/HR基本资料/HR公司上传logo month_amount:本月收益
     */
    public function getTaskHomeData(){
        $user_id = UID;
        $company_model = D('Admin/CompanyInfo');
        $accountLogModel = D('Admin/AccountLog');
        $return_list = array();
        $return_list['task'] = $this->taskList();
        $rank_list = $this->rankList();
        $company_logo = $company_model->getCompanyInfoField(array('user_id' => $user_id), 'company_logo');
        $rank_list['company_logo'] = $company_logo;
        $return_list['rank'] = $rank_list;
        $start_time = mktime(0,0,0,date('m'),1,date('Y'));
        $end_time = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $account_where = array('change_type' => array('in', array(2, 3, 6)), 'change_time' => array('between', array($start_time, $end_time)));
        $sum_money = $accountLogModel->getAccountLogMoneySum($account_where);
        $return_list['month_amount'] = fen_to_yuan($sum_money);
        $this->apiReturn(V(1, '任务首页内容', $return_list));
    }

    /**
     * @desc 每日任务列表
     * @return mixed
     */
    private function taskList(){
        $info = D('Admin/Task')->getTaskList();
        $task_log = D('Admin/TaskLog');
        $task_arr = array(1 => '每日限制', 0 => '永久限制', 2 => '每周限制', 3 => '每月限制');
        foreach($info as &$val){
            $val['reward'] = fen_to_yuan($val['reward']);
            $val['can'] = intval($val['can']);
            $t_n = $task_log->validTaskNumber($val['id'], UID, true);
            $val['type_number'] = $task_arr[$val['type']].$val['type_number'].'份/已完成'.$t_n;
        }
        unset($val);
        return $info;
    }

    /**
     * @desc HR排名信息 HR资料
     * @return array
     */
    private function rankList(){
        $user_id = UID;
        $user_model = D('Admin/User');
        $hr_resume = D('Admin/HrResume');
        $userRanking = $user_model->getUserRankingInfo($user_id);
        $hrResumeRanking = $hr_resume->getHrResumeRankingInfo($user_id);
        $userFields = $user_model->getUserInfo(array('user_id'=>UID), 'head_pic,nickname,user_name');
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
        $return_array = array('user_ranking' => $userRanking, 'resume_ranking' => $hrResumeRanking,'head_pic'=>$head_pic,'nickname'=>$nickname, 'resume_number' => $resume_number);
        return $return_array;
    }

    /**
     * @desc 获取导航栏目列表
     * @param 1、HR端 0、求职者端
     */
    private function navList(){
        $nav_model = D('Admin/Nav');
        $position = I('position', 1, 'intval');
        $nav_where = array('position' => $position);
        $field = 'link_type,img,title,id,sort';
        $list = $nav_model->navList($nav_where, $field, 'sort asc');
        return $list;
    }

    /**
     * @desc 获取公告列表
     */
    private function noticeList(){
        $model = D('Admin/Article');
        $notice_where = array('article_cat_id' => 6, 'display' => 1);
        $field = 'article_id,title';
        $list = $model->getArticleList($notice_where, $field, 'sort asc');
        return $list['articlelist'];
    }

    /**
     * @desc 轮播图列表
     */
    private function bannerList(){
        $model = D('Admin/Ad');
        $position = I('ad_position', 1, 'intval');
        $where = array('ad.position_id' => $position, 'display' => 1);
        $field = 'title,content';
        $list = $model->getAdlist($where, $field);
        return $list['info'];
    }
}