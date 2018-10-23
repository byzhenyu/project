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
        $res = $this->where(array('user_id' => $hr_id))->select();
        if(count($res) > 0){
            $hr_tags_pos = array();
            $hr_tags_area = array();
            foreach($res as &$val){
                $hr_tags_pos[] = $val['job_position'];
                $hr_tags_area[] = $val['job_area'];
            }
            unset($val);
            $hr_tags_pos = implode('|', $hr_tags_pos);
            $hr_tags_area = implode('|', $hr_tags_area);
            $hr_tags_area = explode('|', $hr_tags_area);
            $hr_tags_pos = explode('|', $hr_tags_pos);
        }
        else{
            $hr_tags_area = $res[0]['job_area'];
            $hr_tags_pos = $res[0]['job_position'];
            $hr_tags_pos = explode('|', $hr_tags_pos);
            $hr_tags_area = explode('|', $hr_tags_area);
        }
        $valid = $this->where(array('user_id' => $hr_id))->order('id desc')->limit(1)->find();
        if(!$valid){
            return $this->add(array('user_id' => $hr_id, 'job_area' => $args['job_area'], 'job_position' => $args['job_position']));
        }
        else{
            if(!in_array($args['job_area'], $hr_tags_area)){
                if((mb_strlen($valid['job_area'].$args['job_area']) + 1) > 255){
                    return $this->add(array('user_id' => $hr_id, 'job_area' => $args['job_area']));
                }
                else{
                    return $this->where(array('id' => $valid['id']))->save(array('job_area' => $valid['job_area'].'|'.$args['job_area']));
                }
            }
            if(!in_array($args['job_position'], $hr_tags_pos) && $args['job_position'] != 0){
                if((mb_strlen($valid['job_position'].$args['job_position']) + 1) > 255){
                    return $this->add(array('user_id' => $hr_id, 'job_position' => $args['job_position']));
                }
                else{
                    return $this->where(array('id' => $valid['id']))->save(array('job_position' => $valid['job_position'].'|'.$args['job_position']));
                }
            }
        }
    }

    public function getUserTags($hr_id){
        $res = $this->where(array('user_id' => $hr_id))->select();
        return $res;
    }
}