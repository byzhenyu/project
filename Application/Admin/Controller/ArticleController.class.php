<?php
/**
 * 文章分类操作类
 */
namespace Admin\Controller;
use Think\Controller;
class ArticleController extends CommonController {

    //编辑文章
    public function editArticle(){
        $article_id = I('article_id', 0, 'intval');
        $model = D('Admin/Article');
        if(IS_POST){
            if ($article_id > 0){
                if($model->create(I('post.'), 2)){
                    if ($model->save() !== false) {
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($model->create(I('post.'), 1)){                  
                    if ($model->add() !== false) {
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }

            $this->ajaxReturn(V(0, $model->getError()));
        } 
        $articleInfo = M('Article')->find($article_id);
        $categoryData = D('ArticleCategory')->getArticleCategoryList();
        $this->assign('articleInfo', $articleInfo);
        $this->assign('categoryData',$categoryData);     
        $this->display();
    }

    //显示文章列表
    public function listArticle(){
    	$article_cat_id = I('article_cat_id', -1, 'intval');
        $keyword = I('keyword', '', 'trim');

        $articleModel = D('Admin/Article');
        if ($article_cat_id != -1) {
            $where['article_cat_id'] = $article_cat_id;
        }
        //关键字查询
        if($keyword){
            $where['title'] = array('like', '%'. $keyword .'%');
            // $where['_complex'] = $map;
        }
        $articlelist = $articleModel->getArticleList($where);

        $categoryData = D('ArticleCategory')->getArticleCategoryList();

        $this->article_cat_id = $article_cat_id;
        $this->keyword = $keyword;
        $this->assign('catlist', $categoryData);
        $this->assign('articlelist', $articlelist['articlelist']);
        $this->assign('page', $articlelist['page']);
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('article');  //调用父类的方法
    }

    // 删除图片
    public function delFile(){
        $this->_delFile();  //调用父类的方法
    }

    // 上传图片
    public function uploadImg(){
        $this->_uploadImg();  //调用父类的方法
    }
}