<?php
namespace Admin\Model;
use Think\Model;
class UserTagsModel extends Model{

    protected function _before_insert(&$data, $option){
    }

    protected function _before_update(&$data, $option){
    }

    public function refreshJobArgs($hr_id, $args){
        if(is_array($hr_id)){
            foreach($hr_id as &$value){
                $this->singleOperate($value, $args);
            }
            return true;
        }
        else{
            return $this->singleOperate($hr_id, $args);
        }
    }

    private function singleOperate($hr_id, $args){
        $valid = $this->where(array('user_id' => $hr_id, 'job_area' => $args['job_area']))->order('id desc')->limit(1)->find();
        if(!$valid){
            return $this->add(array('user_id' => $hr_id, 'job_area' => $args['job_area'], 'job_position' => $args['job_position']));
        }
        else{
            $t_position = $valid['job_position'].'|'.$args['job_position'];
            if(strlen($t_position) > 255){
                return $this->add(array('user_id' => $hr_id, 'job_area' => $args['job_area'], 'job_position' => $args['job_position']));
            }
            else{
                return $this->where(array('id' => $valid['id']))->save(array('job_position' => $t_position));
            }
        }
    }

    public function getUserTags($hr_id){
        $res = $this->where(array('user_id' => $hr_id))->select();
        return $res;
    }
}