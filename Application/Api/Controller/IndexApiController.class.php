<?php
/**
 * Created by liuniukeji.com
 * 主页相关接口
*/
namespace Api\Controller;
use Common\Controller\ApiCommonController;
use Think\Verify;

class IndexApiController extends ApiCommonController{
    protected $agent_id = 0;
    public function __construct() {
        parent::__construct();
        $address = I('address', '');
        $agent_info = D('Home/Agent')->getAgentId($address);
        if ($agent_info['status'] == 0) {
            $this->apiReturn($agent_info);
        }
        $this->agent_id = $agent_info;
    }

    /**
     * 获取首页数据
     */
    public function getHomeData(){
        //首页导航栏目表10个
        $navModel = D('Home/Nav');
        $where['type'] = 0;
        $data['navList'] = $navModel->getNavList($where);

        //代理商轮播
        $data['bannerList'] = $this->adAgentBannerList();
        //通栏广告
        $data['agentBannerList'] = $this->adSys();
        //悬浮广告
        $data['downAgentBannerList'] = $this->adAgentBannerDown();
        //限时特惠
        $data['activityList'] = $this->adLimitedFavour();
        //热卖精品
        $data['recommendList'] = $this->getRecommendGoods();
        //代理公告
        $data['agentNoticeList'] = $this->getAgentNoticeList();
        $this->apiReturn(V(1, '首页数据', $data));
    }

    public function agentNotice(){
        $list = $this->getNotice($this->agent_id);
        $this->apiReturn(V(1, '公告列表', $list));
    }

    //热卖推荐
    protected function getRecommendGoods() {
        $couponModel = D('Api/Coupon');
        $goodsModel = D('Home/Goods');
        $where['is_recommend'] = array('eq', 1); //热卖推荐
        $order = 'update_time desc';
        $where['is_on_sale'] = array('eq', 1);
        $where['agent_id'] = $this->agent_id;
        $field = 'goods_id, goods_name, goods_img, shop_price, shop_id, shop_name, unit';
        $data = $goodsModel->getGoodsListByPage($where, $field, $order, 20);
        unset($where);
        $express_amount = C('EACH_ORDER_EXPRESS_COST');
        $goods_list = $data['info'];
        if (!empty($goods_list)) {
            $shop_id = array();
            foreach ($goods_list as $v) {
                $shop_id[] = $v['shop_id'];
            }
            array_unique($shop_id);
            $where['shop_id'] = array('in', $shop_id);
            $shop_list = M('shop')->where($where)->getField('shop_id, full_mail_amount', true);
        }
        foreach ($goods_list as $key => $value) {
            $goods_list[$key]['shop_price'] = fen_to_yuan($value['shop_price']);
            $goods_list[$key]['goods_img'] = $value['goods_img'];
            $goods_list[$key]['express_amount'] = $express_amount;
            $goods_list[$key]['full_mail_amount'] = fen_to_yuan($shop_list[$value['shop_id']]);
            $shop_coupon = $couponModel->getShopCouponCount($value['shop_id']);
            $goods_list[$key]['is_coupon'] = $shop_coupon > 0 ? 1 : 0;
        }
        return $goods_list;
    }

    //代理商轮播
    protected function adAgentBannerList() {
        $where['agent_id'] = $this->agent_id;
        $where['position_id'] = 9;
        $field = 'ad_id, title, link_url, content, type, item_id';
        $data = D('Home/Ad')->getAdList($where, $field);
        return $data;
    }

    //平台通栏广告
    protected function adSys(){
        $where['agent_id'] = 0;
        $where['position_id'] = 8;
        $field = 'ad_id, title, link_url, content, type, item_id';
        $data = D('Home/Ad')->getAdInfo($where, $field);
        return $data;
    }

    //代理商悬浮广告
    protected function adAgentBannerDown(){
        $where['agent_id'] = $this->agent_id;
        $where['position_id'] = 10;
        $field = 'ad_id,title,link_url,content,type,item_id';
        $data = D('Home/Ad')->getAdInfo($where, $field);
        return $data;
    }

    //获取限时特惠信息
    protected function adLimitedFavour() {
        $activity_model = D('Home/Activity');
        //今日特惠
        $where['end_time'] = array('egt', NOW_TIME);
        $where['start_time'] = array('elt', NOW_TIME);
        $where['is_finished'] = 0;
        $where['a.agent_id'] = $this->agent_id;
        $goods_list = $activity_model->getActivitylist($where);
        foreach($goods_list as &$val){
            $val['act_price'] = fen_to_yuan($val['act_price']);
            $val['shop_price'] = fen_to_yuan($val['shop_price']);
            $val['goods_img'] = getImgThumb($val['goods_img']);
        }

        return $goods_list;
    }

    //公告
    public function getAgentNoticeList()
    {
        $list = D('Home/Notice')->where('agent_id='.$this->agent_id)->field('title, shop_id')->select();
        return $list;
    }

    
}
