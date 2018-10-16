<?php
/**
 * Created by PhpStorm.
 * User: dl
 * Date: 2017/6/26
 * Time: 10:59
 */

namespace Admin\Model;


use Think\Model;

class FeedBackModel extends Model
{
    protected $selectFields = array('id', 'comment', 'user_id', 'mobile', 'create_time');

    protected $_validate = array(
        array('comment', 'require', '反馈内容不能为空！', 1, 'regex', 3),
        array('comment', '5,1000', '反馈内容必须大于5个字！', 1, 'length', 3),
        array('mobile', 'require', '联系方式不能为空！', 1, 'regex', 3),
        array('mobile', '1,50', '联系方式长度不能大于50', 1, 'length', 3)
    );

    /**
     * @param $where
     * @param null $field
     * @param string $order
     * @return array
     */
    public function getFeedBackByPage($where, $field = null, $order = 'create_time desc'){

        if(is_null($field)){
            $field = $this->selectFields;
        }

        $count = $this->where($where)->count();
        $page = get_page($count);

        $info = $this->field($field)->where($where)->limit($page['limit'])->order($order)->select();

        return array(
            'info' => $info,
            'page' => $page['page'],
        );
    }

    public function _before_insert(&$data, $option){
        $data['create_time'] = NOW_TIME;
    }
}