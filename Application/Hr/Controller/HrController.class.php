<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class HrController extends HrCommonController {

    public function setHr(){
        $user_id = HR_ID;
        $where = array('user_id' => $user_id);
        if(IS_POST){
            $data = I('post.');
            $model = D('Admin/User');
            if(!isMobile($data['mobile'])) $this->ajaxReturn(V(0, '请输入合法的手机号码！'));
            if($data['password']){
                if(!$data['new_password']) $this->ajaxReturn(V(0, '请再次输入新密码'));
                if($data['new_password'] != $data['password']) $this->ajaxReturn(V(0, '新密码两次输入不一致！'));
                if(strlen($data['password']) < 6 || strlen($data['password']) > 18) $this->ajaxReturn(V(0, '密码长度控制在6-18位！'));
            }
            $mobile_info = $model->getUserInfo(array('mobile' => $data['mobile'], 'status' => 1, 'user_type' => 1, 'user_id' => array('neq', HR_ID)));
            if($mobile_info) $this->ajaxReturn(V(0, '手机号已经被绑定！'));
            $res = $model->saveUserData($where,$data);
            if(false !== $res){
                $this->ajaxReturn(V(1, '保存成功！'));
            }
            else{
                $this->ajaxReturn(V(0, '保存失败！'));
            }
        }
        $info = D('Admin/User')->getUserInfo($where, '*');
        $like_tags = D('Admin/Tags')->getTagsList(array('id' => array('in', $info['like_tags'])));
        $tags = '';
        foreach($like_tags as &$val){
            $tags .= $val['tags_name'].',';
        }
        unset($val);
        $info['like_tags'] = rtrim($tags, ',');
        if(!$info['like_tags']) $info['like_tags'] = '尚未填写';
        $info['city_name'] = M('CompanyInfo')->where(array('user_id' => HR_ID))->getField('company_address');
        $this->info = $info;
        $this->display();
    }

    public function setCompany(){
        $company_model = D('Admin/CompanyInfo');
        $hr_id = HR_ID;
        $company_where = array('user_id' => $hr_id);
        $company_info = $company_model->getCompanyInfoInfo($company_where);
        if(IS_POST){
            $data = I('post.');
            if($company_info){
                $data['company_address'] = $data['province'].','.$data['city'].','.$data['county'].' '.$data['company_address'];
                $create = $company_model->create($data, 2);
                if(false !== $create){
                    $res = $company_model->where($company_where)->save($data);
                    if(false !== $res){
                        $this->ajaxReturn(V(1, '保存成功！'));
                    }
                }
            }
            else{
                $create = $company_model->create($data, 1);
                if(false !== $create){
                    $res = $company_model->add($data);
                    if($res){
                        $this->ajaxReturn(V(1, '保存成功！'));
                    }
                }
            }
            $this->ajaxReturn(V(0, $company_model->getError()));
        }
        $address = explode(' ' ,$company_info['company_address']);
        $company_info['company_address_p'] = $address[0];
        unset($address[0]);
        $company_info['company_address'] = str_replace($company_info['company_address_p'], '', $company_info['company_address']);
        $company_info['company_address'] = ltrim($company_info['company_address'], ' ');
        $_p_c_c = explode(',', $company_info['company_address_p']);
        $company_info['province'] = $_p_c_c[0];
        $company_info['city'] = $_p_c_c[1];
        $company_info['county'] = $_p_c_c[2];
        $company_size = returnArrData(C('COMPANY_SIZE'));
        $nature_list = D('Admin/CompanyNature')->getCompanyNatureList();
        $industry = D('Admin/Industry')->getIndustryList();
        $this->industry = $industry;
        $this->company_nature = $nature_list;
        $this->company_size = $company_size;
        $this->info = $company_info;
        $this->display();
    }

    public function uploadImg(){
        $this->companyUpload();
    }

    public function delFile(){
        $this->_delFile();
    }

    public function companyUpload(){
        $config = array(
            'rootPath' => '.'.C('UPLOAD_PICTURE_ROOT').'/Company/',
            'savePath' => HR_ID.'/',
            'maxSize' => C('UPLOAD_SIZE'),
            'exts' => 'jpg,jpeg,png,gif',
        );
        $Upload = new \Think\Upload($config);
        $info = $Upload->upload();

        if ($info === false) {
            $this->ajaxReturn(array('status' => 0, 'msg' => $Upload->getError()));
        } else {
            vendor('Alioss.autoload');
            $config=C('AliOss');

            $oss=new \OSS\OssClient($config['accessKeyId'],$config['accessKeySecret'],$config['endpoint']);
            $bucket=$config['bucket'];

            // 返回成功信息
            foreach($info as $file){
                $path = '.'.C('UPLOAD_PICTURE_ROOT').'/Company/'.$file['savepath'].$file['savename'];

                $oss_path = trim($path, './');
                $local_path = trim($path, '.');
                $oss->uploadFile($bucket,$oss_path,$path);
                unlink('.'.C('UPLOAD_PICTURE_ROOT').'/Company/'.$file['savepath'].$file['savename']);
                $data['status'] = 1;
                $data['src'] ='http://'.$bucket.'.'.$config['endpoint'].'/'.$oss_path;
                $data['name'] =$local_path;

            }

            $this->ajaxReturn($data);
        }
    }

}