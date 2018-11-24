<?php
/**
 * 合伙人模型类
 */
namespace Admin\Model;
use Think\Model;
class PartnerModel extends Model {
    protected $insertFields = array('partner_name', 'partner_mobile', 'partner_address', 'partner_position', 'parent_id', 'partner_level', 'audit_status', 'add_time');
    protected $updateFields = array('partner_name', 'partner_mobile', 'partner_address', 'partner_position', 'parent_id', 'partner_level', 'audit_status', 'add_time', 'id');
    protected $_validate = array(
        array('partner_name', 'require', '合伙人名称不能为空！', 1, 'regex', 3),
        array('partner_name', '1,20', '名称长度不能超过20', 1, 'length', 3),
        array('partner_mobile', 'isMobile', '请输入合法的手机号！', 1, 'function', 3),
        array('partner_address', 'require', '合伙人所在地不能为空！', 1, 'regex', 3),
        array('partner_address', '1,255', '合伙人所在地超出限制长度！', 1, 'length', 3),
        array('partner_position', 'require', '目前职业不能为空！', 1, 'regex', 3),
        array('partner_position', '1,100', '目前职业超出限制长度！', 1, 'length', 3)
    );

    /**
     * @desc 合伙人列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getPartnerList($where, $field = false, $order = 'add_time desc')
    {
        $count = $this->where($where)->count();
        $page = get_page($count);
        $info = $this->field($field)->where($where)->limit($page['limit'])->order($order)->select();
        return array(
            'info' => $info,
            'page' => $page['page']
        );
    }


    protected function _before_insert(&$data, $option){
        $data['add_time'] = NOW_TIME;

    }
    protected function _before_update(&$data, $option){

    }
    public function _before_delete($option){
    }
}
