<?php
/**
 * 文章分类操作类
 */
namespace Admin\Controller;
use Think\Controller;

class ArticleCategoryController extends CommonController {

    //编辑文章分类
    public function editArticleCategory(){
        $article_cat_id = I('article_cat_id', 0, 'intval');

        $categoryModel = D('Admin/ArticleCategory');
        if(IS_POST){
            // p($_POST);
            if ($article_cat_id > 0) {
                if($categoryModel->create(I('post.'), 2)){
                    if($categoryModel->save() !== false){
                        $this->ajaxReturn(V(1, '修改成功!'));
                    }
                }
            } else {
                if($categoryModel->create(I('post.'), 1)){
                    if($categoryModel->add() !== false){
                        $this->ajaxReturn(V(1, '保存成功!'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $categoryModel->getError()));
        }
        $catInfo = $categoryModel->find($article_cat_id);
        //p($catInfo);

        $this->assign('catInfo', $catInfo);
        $this->display();
    }

    // 删除
    public function del(){
        $this->_del('article_category', 'article_cat_id');  //调用父类的方法
    }

    //显示文章分类
    public function listArticleCategory(){
        $keyword = I('keyword', '');
        $where = '';
        if ($keyword != '') {
            $where['cat_name'] = array('like', '%'. $keyword .'%');
        }

        $categoryModel = D('Admin/ArticleCategory');
        $catlist = $categoryModel->getArticleCategoryList($where);
        $this->keyword = $keyword;
        $this->assign('catlist',$catlist);
        $this->assign('count',count($catlist));
        $this->display();
    }

}