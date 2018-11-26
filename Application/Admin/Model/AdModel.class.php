<?php
/**
 * 广告管理模型类
 */
namespace Admin\Model;
use Think\Model;
class AdModel extends Model { 
    protected $insertFields = array('title','type','item_id','link_url','description','start_time','end_time','display','content','sort','position_id');
    protected $updateFields = array('ad_id','title','type','item_id','link_url','description','start_time','end_time','display','content','sort','position_id');
    protected $_validate = array(
        array('title', 'require', '广告名称不能为空！', 1, 'regex', 3),
        array('title', '1,45', '广告名称的值最长不能超过 45 个字符！', 1, 'length', 3),
        array('position_id', '1, 100000', '广告位置不能为空！', 1, 'between', 3),
        array('content', '1,150', '请上传广告图片', 1, 'length', 3),
        array('display', 'number', '是否启用 1：启用0：禁用必须是一个整数！', 2, 'regex', 3)
    );

    //获取广告列表
    public function getAdlist($where, $field = null, $order = 'ad_id desc'){
        $count = $this->alias('ad')
            ->join('__AD_POSITION__ pos on ad.position_id = pos.position_id','left')
            ->where($where)
            ->count('ad.ad_id');
        $page = get_web_page($count);

        $info = $this->alias('ad')
            ->join('__AD_POSITION__ pos on ad.position_id = pos.position_id','left')
            ->field($field)
            ->where($where)
            ->limit($page['limit'])
            ->order($order)
            ->select();
  
        return array(
            'info' => $info,
            'page' => $page['page']
        );
    }


    //广告添加之前的钩子操作
    protected function _before_insert(&$data, $option){

    }
    //修改广告的钩子操作
    protected function _before_update(&$data, $option){

    }
    //删除广告的钩子操作
    public function _before_delete($option){
        if(is_array($option['where']['ad_id'])){
            $this->error = '不支持批量删除';
            return false;
        }
        $images = $this->field('content')->find($option['where']['ad_id']);
        deleteImage($images);
    }
}
