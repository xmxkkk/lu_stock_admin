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

        $stockFilters=D('LuStrategyStockfilter')->select();
        $this->assign('stockFilters',$stockFilters);

        $filterMenus=array();

        for($i=0;$i<count($stockFilters);$i++){
            if($stockFilters[$i]['type']=='num_menu'){
                $filterMenus[$stockFilters[$i]['name']]=D('FilterMenu')->where(array('type'=>$stockFilters[$i]['name']))->order('ord')->select();
            }
        }
        $this->assign('filterMenus',$filterMenus);

    }
    private function combine(&$data){
        $data['type_name']=$this->types[$data['type']];
        $data['status_name']=$this->statuses[$data['status']];
        $data['run_status_name']=$this->run_statuses[$data['run_status']];

        $data['stock_num']=D('LuStrategyStock')->where(array('id'=>$data['id'],'status'=>1))->count();
        $data['stocks']=D('LuStrategyStock')->where(array('id'=>$data['id'],'status'=>1))->select();//print_r($data['stocks']);
        $data['filters']=D('LuStrategyFilter')->where(array('id'=>$data['id']))->select();
        $data['changeRate']=D("LuStrategyChangeRate")->where(array('id'=>$data['id']))->order("dt desc")->find();


        for($i=0;$i<count($data['filters']);$i++){
            $stockFilter=D('LuStrategyStockfilter')->where(array('name'=>$data['filters'][$i]['filter']))->find();
            $data['filters'][$i]['type']=$stockFilter['type'];
            $data['filters'][$i]['title']=$stockFilter['title'];
            $data['filters'][$i]['attr']=$stockFilter['attr'];

            if($data['filters'][$i]['type']=='num_menu'){
                $arr=explode(",",$data['filters'][$i]['condition']);
                $data['filters'][$i]['menu']=$arr[0];
                $data['filters'][$i]['condition']=$arr[1];
                $data['filters'][$i]['menus']=D('FilterMenu')->where(array('type'=>$stockFilter['name']))->order('ord')->select();//print_r($data['filters'][$i]['menus']);
            }else if($data['filters'][$i]['type']=='str'){
                if(start_with($data['filters'][$i]['condition'],'!IN')){
                    $data['filters'][$i]['bool']="false";
                    $data['filters'][$i]['condition']=mb_substr($data['filters'][$i]['condition'], 3);
                }else if(start_with($data['filters'][$i]['condition'],'IN')){
                    $data['filters'][$i]['bool']="true";
                    $data['filters'][$i]['condition']=mb_substr($data['filters'][$i]['condition'], 2);
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
        
        $list = $this->lists(D('LuStrategy'), $map,'id');

        $this->combines($list);

        $this->assign('list', $list);
        $this->meta_title = '管理';
        $this->display();
    }

    public function edit($id=0){
        $data=D('LuStrategy')->where(array('id' => $id))->find();
        $this->combine($data);
        $this->assign('data',$data);

        $this->assign('aciton','edit');
        $this->meta_title = '编辑策略';
        $this->display();
    }
    public function add(){
        $this->assign('aciton','add');
        $this->meta_title = '新增策略';
        $this->display("edit");
    }

    public function update(){
        $id=I('post.id');
        $filters=I('post.filters');
        $title=I('post.title');
        $attr=I('post.attr');
        $img=I('post.img');
        $type=I('post.type');
        $status=I('post.status');
        
        if(empty($title)){
            $this->error('名字不能为空');
        }
        if(empty($attr)){
            $this->error('描述不能为空');
        }
        if(empty($img)){
            $this->error('图片不能为空');
        }

        $arr=json_decode($filters);
        if(count($arr)<1){
            $this->error("过滤器不足");
        }

        if($status==1 && $id>0){
            $cnt=D("LuStrategyStock")->where(array('id'=>$id))->count();
            if($cnt==0){
                $this->error("没有运行不能设置为发布状态");
            }
        }
        
        $data=array(
            'title'=>$title,
            'attr'=>$attr,
            'img'=>$img,
            'type'=>$type,
            'status'=>$status
            );
        if(empty($id)){
            $id=D('LuStrategy')->data($data)->add();
        }else{
            D('LuStrategy')->where(array('id'=>$id))->data($data)->save();
        }

        D('LuStrategyFilter')->where(array('id'=>$id))->delete();

        for($i=0;$i<count($arr);$i++){
            $filter=$arr[$i]->filter;
            $condition=$arr[$i]->condition;

            D('LuStrategyFilter')->data(array(
                'id'        =>$id,
                'filter'    =>$filter,
                'condition' =>$condition
                ))->add();
        }

        $result=array();
        $result['info']   =   "更新成功";
        $result['status'] =   1;
        if(I('post.action')=='add'){
            $result['url']    =   U('index');
        }
        $result['id']     =   $id;
        $this->ajaxReturn($result);
    }
    public function run_filter(){
        $result=array();

        $id=I('post.id');

        $filters=null;
        if($id==""){
            $id=-1;
            $filters=I('post.filters');
            $filters=json_decode($filters);
            $result['test']=1;
        }else{
            $filters=M("LuStrategyFilter")->where(array("id"=>$id))->select();
             $result['test']=0;
        }
        if(count($filters)==0){
            $this->error("过滤器不足");
        }

        $filters=json_encode($filters);

        $md5=md5(rand(0,2000000000).":".rand(0,2000000000).":".rand(0,2000000000));

        $data=new \stdClass();
        $data->id=$md5;
        $data->filters=base64_encode($filters);

        //exec("/home/app/venus/run.sh ".$id." \"".addslashes(json_encode($data))."\"");

        if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
            //echo "java -jar D:\\workspace\\Venus\\target\\venus-0.0.1-SNAPSHOT.jar --command.lst.start=true --command.lst.id=".$id." --command.lst.json=\"".addslashes(json_encode($data))."\"";
            exec("java -jar D:\\workspace\\Venus\\target\\venus-0.0.1-SNAPSHOT.jar --command.lst.start=true --command.lst.id=".$id." --command.lst.json=\"".addslashes(json_encode($data))."\"");
        }else{
            //echo "/home/app/venus/run.sh ".$id." \"".addslashes(json_encode($data))."\"";
            exec("/home/app/venus/run.sh ".$id." \"".addslashes(json_encode($data))."\"");
        }

        $stocks=D('LuStrategyStock')->where(array("id"=>$id,"status"=>1))->select();

        $changeRate=D("LuStrategyChangeRate")->where(array("id"=>$id))->find();

        
        

        $result['status']=1;
        $result['info']="运行成功";
        if(I('post.action')=='add'){
            $result['url']    =   U('index');
        }
        
        $result['md5']=$md5;
        $result['stocks']=$stocks;
        $result['changeRate']=$changeRate;

        $this->ajaxReturn($result);
    }
    public function stocks(){
        $id=I('post.id');
        $id=-1;
        $stocks=D('LuStrategyStock')->where(array("id"=>$id))->select();

        $this->ajaxReturn($stocks);
    }
    public function del($id=0){
        D("LuStrategy")->where(array("id"=>$id))->delete();
        D("LuStrategyStock")->where(array("id"=>$id))->delete();
        
        $this->success('删除成功');
    }
    public function hangye(){
        $level1=D("StockCompanyHangye")->group('level1')->select();
        $level2=D("StockCompanyHangye")->group('level2')->select();
        $level3=D("StockCompanyHangye")->group('level3')->select();
        
        $this->assign('level1',$level1);
        $this->assign('level2',$level2);
        $this->assign('level3',$level3);//print_r($level3);
        
        $this->meta_title = '行业信息';
        $this->display();
    }
}
