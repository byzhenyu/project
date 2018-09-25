<?php
/**
 * 悬赏令牌获取表模型
 */
namespace Admin\Model;

use Think\Model;

class TokenLogModel extends Model {
    protected $insertFields = array('user_id', 'token_num', 'recruit_id', 'type','add_time');
    protected $updateFields = array();
    protected $_validate = array(
        array('user_id', 'require', '用户id不能为空', 1, 'regex', 3),
        array('token_num', 'number', '获取令牌个数有误', 1, 'regex', 3),
        array('recruit_id', 'require', '悬赏id不能为空', 1, 'regex', 3),
        array('type', array(0,1), '获取类型字段有误', 1, 'in', 3),
    );

    protected function _before_insert(&$data, $option) {
        $data['add_time'] = NOW_TIME;
    }

    /**
     * @desc 获取悬赏赏金令牌列表
     * @param $where
     * @param string $field
     * @param string $order
     * @return array
     */
    public function getTokenLogList($where,$field='',$order='l.id desc') {
        if(!$field) $field = 'l.*,u.user_name,u.nickname,u.mobile';
        $number = $this->alias('l')->join('__USER__ as u on u.user_id = l.user_id')->count();
        $page = get_web_page($number);
        $list = $this->alias('l')->join('__USER__ as u on u.user_id = l.user_id')->where($where)->field($field)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     *  是否可查看简历
     */
    public function getResumeCountRes($where) {
        $max_count = C('MAX_RESUME');
        $number = $this->where($where)->count();
        if ($max_count > $number) {
            return V(1, '通过');
        }
        return V(0, '最多可查看'.$max_count.'份简历');
    }
}