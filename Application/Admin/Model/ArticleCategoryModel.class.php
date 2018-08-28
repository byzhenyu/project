<?php
/**
 * 文章分类模型类
 */
namespace Admin\Model;
use Think\Model;
class ArticleCategoryModel extends Model {
    protected $insertFields = array('cat_name','sort', 'display');
    protected $updateFields = array('article_cat_id','cat_name','sort', 'display');
    protected $_validate = array(
        array('cat_name', 'require', '分类名称不能为空！', 1, 'regex', 3),
        array('cat_name', '1,30', '分类名称的值最长不能超过 30 个字符！', 1, 'length', 3),
        array('cat_name', 'checkTitle', '名称重复！', 1, 'callback', 3),
    );
    protected function checkTitle($data) {
        $where['cat_name'] = $data;
        $id = I('article_cat_id',0,'intval');
        if ($id > 0) {
            $where['article_cat_id'] = array('neq',$id);
        }
        $count = $this->where($where)->count();
        if ($count >0) {
            return false;
        }else {
            return true;
        }
    }
    //提取全部文章分类数据并作排序
    public function getArticleCategoryList($where=array()){
        $data = $this->where($where)->order('sort, article_cat_id')->select();
        return $data;
    }

    //删除分类前的钩子操作
    public function _before_delete($option){
        // 查找当前栏目ID下的子分类
        $article_cat_id = $option['where']['article_cat_id'];
        $where['article_cat_id'] = array('in', $article_cat_id);
        $children = M('Article')->where($where)->count();
        if ($children > 0 ) {
            $this->error = '当前分类不是末级分类或者此分类下还存在有商品,您不能删除';
            return false;
        }
    }

    //修改分类前的钩子操作
    public function _before_update(&$data, $option){
    }
}