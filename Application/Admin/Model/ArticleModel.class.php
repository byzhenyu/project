<?php
/**
 * 文章内容模型类
 */
namespace Admin\Model;
/* use Think\Model;*/

use Think\Model\RelationModel;

/*class ArticleModel extends Model { */
class ArticleModel extends RelationModel {
    protected $insertFields = array('title', 'introduce','content', 'thumb_img','display','addtime','sort','keywords','click_count','article_cat_id', 'description', 'agent_id');
    protected $updateFields = array('article_id','title', 'introduce','content','thumb_img','display','addtime','sort','keywords','click_count','article_cat_id', 'description');
    protected $_validate = array(
        array('title', 'require', '标题不能为空！', 1, 'regex', 3),
        array('title', 'checkTitleLength', '标题不能超过30个字！', 2, 'callback', 3),
        array('title', 'checkTitle', '标题重复！', 1, 'callback', 3),
        array('content', 'require', '内容不能为空！', 1, 'regex', 3),
    );

    /**
     * 验证标题长度
     */
    protected function checkTitleLength($data) {
        $length = mb_strlen($data, 'utf-8');
        if ($length > 30) {
            return false;
        }
        return true;
    }
    protected function checkTitle($data) {
        $where['title'] = $data;
        $id = I('article_id',0,'intval');
        if ($id > 0) {
            $where['article_id'] = array('neq',$id);
        }
        $count = $this->where($where)->count();
        if ($count >0) {
            return false;
        }else {
            return true;
        }
    }
    /**
     * 获取文章列表
     * @param
     * @return array    ['attrlist']文章列表 ['page']分页数据
     */
    public function getArticleList($where){
        $count = $this->where($where)->count();
        $page = get_page($count);
        $articlelist =
            $this->where($where)
                ->limit($page['limit'])
                ->order('addtime desc')
                ->select();

        return array('articlelist'=>$articlelist,'page'=>$page['page']);
    }

    //添加操作前的钩子操作
    protected function _before_insert(&$data, $option){
        $data['addtime'] = NOW_TIME;
    }
    //更新操作前的钩子操作
    protected function _before_update(&$data, $option){
        $data['addtime'] = NOW_TIME;
    }
    //删除钩子操作
    public function _before_delete($option){
        $images = $this->field('thumb_img')->find($option['where']['article_id']);
        deleteImage($images);
    }

    /**
     * @desc 文章详情
     * @param $where
     * @param bool $field
     * @return mixed
     */
    public function getArticleInfo($where, $field = false){
        if(!$field) $field = '*';
        $res = $this->where($where)->field($field)->find();
        return $res;
    }

}