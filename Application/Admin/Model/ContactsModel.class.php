<?php
/**
 * @desc 联系人模型
 */
namespace Admin\Model;
use Think\Model;
class ContactsModel extends Model
{
    protected $insertFields = array('relation_id', 'contact_name', 'contact_mobile', 'contact_email', 'user_id');
    protected $updateFields = array('relation_id', 'contact_name', 'contact_mobile', 'contact_email', 'user_id');
    protected $selectFields = array('relation_id', 'contact_name', 'contact_mobile', 'contact_email', 'user_id', 'id');
    protected $_validate = array(
        array('contact_name', 'require', '联系人姓名不能为空！', 1, 'regex', 3),
        array('contact_name', '0,12', '联系人姓名长度0-12位！', 1, 'length', 3),
        array('relation_id', 'require', '联系人关系不能为空！', 1, 'regex', 3),
        array('contact_mobile', 'isMobile', '请输入正确的联系人手机号格式！', 1, 'function', 3),
        array('contact_email', 'is_email', '请输入正确的邮箱格式！', 1, 'function', 3)
    );

    /**
     * @desc 联系人列表
     * @param $where
     * @param bool $field
     * @param string $order
     * @return array
     */
    public function getContactsList($where, $field = false, $order = ''){
        if(!$field) $field = 'c.*,r.relation_name,r.relation_img';
        $number = $this->alias('c')->where($where)->join('__CONTACTS_RELATION__ as r on c.relation_id = r.id', 'LEFT')->count();
        $page = get_web_page($number);
        $list = $this->alias('c')->where($where)->join('__CONTACTS_RELATION__ as r on c.relation_id = r.id', 'LEFT')->field($field)->order($order)->limit($page['limit'])->select();
        foreach($list as &$val){
            $val['relation_name'] = strval($val['relation_name']);
            $val['relation_img'] = strval($val['relation_img']);
        }
        return array(
            'info' => $list,
            'page' => $page['page']
        );
    }

    /**
     * @desc 紧急联系人删除
     * @param $where
     * @return mixed
     */
    public function delContacts($where){
        $res = $this->where($where)->delete();
        return $res;
    }

    /**
     * @desc 紧急联系人详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getContactsInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        if(!$res) return false;
        $relation = D('Admin/ContactsRelation')->getContactsRelationInfo(array('id' => $res['relation_id']));
        $res['relation_name'] = $relation['relation_name'];
        return $res;
    }

    public function _before_insert(&$data, $option){
    }

    public function _before_update(&$data, $option){
    }
}