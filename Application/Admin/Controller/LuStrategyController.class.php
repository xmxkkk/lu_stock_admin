<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Page;

/**
 * 后台内容控制器
 * @author huajie <banhuajie@163.com>
 */
class LuStrategyController extends AdminController {
    protected $types=array('JIAZHI'=>'价值','JISHU'=>'技术');
    protected $statuses=array(0=>'未发布',1=>'发布');
    protected $run_statuses=array(0=>'未运行',1=>'已运行');
    public function _initialize(){
        parent::_initialize();
        $this->assign('types',$this->types);
        $this->assign('statuses',$this->statuses);
        $this->assign('run_statuses',$this->run_statuses);

        $stockFilters=M('LuStrategyStockfilter')->select();
        $this->assign('stockFilters',$stockFilters);
    }
    private function combine(&$data){
        $data['type_name']=$this->types[$data['type']];
        $data['status_name']=$this->statuses[$data['status']];
        $data['run_status_name']=$this->run_statuses[$data['run_status']];

        $data['stock_num']=M('LuStrategyStock')->where(array('id'=>$data['id'],'status'=>1))->count();
        $data['stocks']=M('LuStrategyStock')->where(array('id'=>$data['id']))->select();
        $data['filters']=M('LuStrategyFilter')->where(array('id'=>$data['id']))->select();
        for($i=0;$i<count($data['filters']);$i++){
            $stockFilter=M('LuStrategyStockfilter')->where(array('name'=>$data['filters'][$i]['filter']))->find();
            $data['filters'][$i]['type']=$stockFilter['type'];
            $data['filters'][$i]['title']=$stockFilter['title'];
            $data['filters'][$i]['attr']=$stockFilter['attr'];

            if($data['filters'][$i]['type']=='num_menu'){
                $arr=explode(",",$data['filters'][$i]['condition']);
                $data['filters'][$i]['menu']=$arr[0];
                $data['filters'][$i]['condition']=$arr[1];
            }else if($data['filters'][$i]['type']=='str'){
                if(start_with($data['filters'][$i]['condition'],'!IN')){
                    $data['filters'][$i]['bool']="false";
                    $data['filters'][$i]['condition']=substr($data['filters'][$i]['condition'], 3);
                }else if(start_with($data['filters'][$i]['condition'],'IN')){
                    $data['filters'][$i]['bool']="true";
                    $data['filters'][$i]['condition']=substr($data['filters'][$i]['condition'], 4);
                }
            }else if($data['filters'][$i]['type']=='num_day1'){
                $arr=explode(",",$data['filters'][$i]['condition']);
                $data['filters'][$i]['day1']=$arr[0];
                $data['filters'][$i]['condition']=$arr[1];
            }else if($data['filters'][$i]['type']=='num_day2'){
                $arr=explode(",",$data['filters'][$i]['condition']);
                $data['filters'][$i]['day1']=$arr[0];
                $data['filters'][$i]['day2']=$arr[1];
                $data['filters'][$i]['condition']=$arr[2];
            }


        }

    }
    private function combines(&$list){
        for($i=0;$i<count($list);$i++){
            $this->combine($list[$i]);
        }
    }

    public function index(){
    /* 查询条件初始化 */
        $map = array();

        $list = $this->lists('LuStrategy', $map,'id');

        $this->combines($list);

        $this->assign('list', $list);
        $this->meta_title = '管理';
        $this->display();
    }

    public function edit($id=0){
        $data=M('LuStrategy')->where(array('id' => $id))->find();
        $this->combine($data);

        $this->assign('data',$data);

        $this->display();
    }

    public function update(){
        $id=I('post.id');
        $filters=I('post.filters');
        $title=I('post.title');
        $attr=I('post.attr');
        $img=I('post.img');
        $type=I('post.type');
        $status=I('post.status');
        
        $arr=json_decode($filters);
        if(count($arr)<1){
            $this->error("过滤器不足");
        }

        M('LuStrategyFilter')->where(array('id'=>$id))->delete();

        for($i=0;$i<count($arr);$i++){
            $filter=$arr[$i]->filter;
            $condition=$arr[$i]->condition;

            M('LuStrategyFilter')->data(array(
                'id'        =>$id,
                'filter'    =>$filter,
                'condition' =>$condition
                ))->add();
        }

        $data=array(
            'title'=>$title,
            'attr'=>$attr,
            'img'=>$img,
            'type'=>$type,
            'status'=>$status
            );
        M('LuStrategy')->where(array('id'=>$id))->data($data)->save();

        $this->success("更新成功");
    }
    public function run(){
        $id=I('get.id');
        echo $id;
    }
}
