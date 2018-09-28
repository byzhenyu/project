<?php
/**
 * 资金记录模型
 */
namespace Admin\Model;

use Think\Model;

class AccountLogModel extends Model {
    protected $insertFields = array('user_id', 'user_money', 'frozen_money', 'pay_points','change_time','change_desc','change_type','order_sn');
    protected $updateFields = array('user_id', 'user_money', 'frozen_money', 'pay_points','change_time','change_desc','change_type','order_sn');
    protected $selectFields = array('log_id','user_id', 'user_money', 'frozen_money', 'pay_points','change_time','change_desc','change_type','order_sn');
    protected $_validate = array(

    );

    /**
     * @param $where
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getAccountLogByPage($where,$field='',$order="log_id desc") {
        if (!$field) {
            $field = $this->selectFields;
        }
        $count = $this->where($where)->count();
        $page = get_web_page($count);
        $info = $this->field($field)
            ->where($where)
            ->order($order)
            ->limit($page['limit'])
            ->select();
        foreach ($info as $k=>$v) {
            $info[$k]['change_time'] = time_format($v['change_time']);
            $info[$k]['user_money'] = fen_to_yuan($v['user_money']);
        }
        return array(
            'info'=>$info,
            'page'=>$page['page']
        );
    }

    protected function _before_insert(&$data, $option){
    }

    /**
     * @desc 资金总计
     * @param $where
     * @return mixed
     */
    public function getAccountLogSum($where){
        $res = $this->where($where)->field('user_money,user_id,change_type')->select();
        return $res;
    }

    /**
     * @desc 资金统计
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getAccountLogMoneySum($where, $field = false){
        if(!$field) $field = 'user_money';
        $res = $this->where($where)->sum($field);
        return $res;
    }
}