<?php
/**
 * 过滤词模型类
 */
namespace Admin\Model;

use Think\Model;

class ContrabandModel extends Model {
    protected $insertFields = array('contraband');
    protected $updateFields = array('contraband', 'id');
    protected $_validate = array(
        array('contraband', 'require', '过滤词不能为空！', 1, 'regex', 3),
        array('contraband', 'checkContrabandLength', '过滤词不能超过100字！', 2, 'callback', 3),
    );

    /**
     * 验证过滤词长度
     */
    protected function checkContrabandLength($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 30) {
            return false;
        }
        return true;
    }
    /**
     * 获取过滤词列表
     * @param
     * @return mixed
     */
    public function getContrabandList($where){
        $count = $this->where($where)->count();
        $page = get_web_page($count);
        $list = $this->where($where)->limit($page['limit'])->order('id desc')->select();

        return array('info' => $list, 'page' => $page['page']);
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
    }

}