<?php
/**
 * Created by liuniukeji.com
 * 文章相关接口
 * @author xuqinlong 706431591
*/
namespace Api\Controller;
use Common\Controller\ApiCommonController;
class ArticleApiController extends ApiCommonController{

	/**
     * 显示分类
     */
    public function articleList(){
        $article_model = D('Home/Article');
        $cat_id = I('cat_id', 0, 'intval');
        $where['article_cat_id'] = $cat_id;
        $where['display'] = 1;
        if (!in_array($cat_id, array(1, 2))) {
            $this->apiReturn(V(0, '参数错误'));
        }
        $field = 'title, addtime, article_id, introduce';
        $list = $article_model->getArticleList($where, $field);
        foreach ($list['info'] as $key => $value) {
            $list['info'][$key]['addtime'] = time_format($value['addtime']);
        }
        $this->apiReturn(V(1, '文章列表', $list['info']));
    }
    /**
     * 显示文章
     */
    public function articleInfo(){
    	$article_model = D('Home/Article');
        $article_id = I('article_id', '', 'intval');
        $where['article_id'] = $article_id;
        $field = 'title, content, addtime';
        $info = $article_model->getArticleContent($where, $field);
        $info['add_time'] = time_format($info['addtime']);
        $this->info = $info;
        $this->display('Home@Index/web');
    }


    public function articleDetail(){
        $article_model = D('Home/Article');
        $cat_id = I('cat_id', 0, 'intval');
        $where['article_cat_id'] = $cat_id;
        $where['display'] = 1;
        if (!in_array($cat_id, array(4,5,6,7,9))) {
            $this->apiReturn(V(0, '参数错误'));
        }

        $field = 'title, content, addtime';
        $result = $article_model->getArticleContent($where, $field);
        if (!empty($result)){
            $result['addtime'] = time_format($result['addtime'], 'Y-m-d');
        }
        $this->apiReturn(V(1, '文章详情', $result));
    }

    /**
     * @desc 系统公告推送详情
     */
    public function getSysPushContent(){
        $id = I('id', '');
        $where['id'] = $id;
        $info = D('Home/Push')->field('id, title, add_time, content')->where($where)->find();
        $info['add_time'] = time_format($info['add_time']);
        $this->info = $info;
        $this->display('Home@Index/web');
    }

    /**
     * @desc 系统公告推送详情
     */
    public function aboutus(){
        $article_model = D('Home/Article');
        $where['article_id'] = 18;
        $field = 'title, content, addtime';
        $result = $article_model->getArticleContent($where, $field);
        $this->info = $result;
        $this->display('Home@Index/about');
    }

}
