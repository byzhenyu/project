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
    /**
     * 意见反馈分页数据
     * @param $where
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
}