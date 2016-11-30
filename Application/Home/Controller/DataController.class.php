<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class DataController extends HomeController {

    /* 文档模型频道页 */
	public function core(){
		$id=intval(I('get.id'));


		$result=null;
		if($id==1){
			$luStrategys=M("LuStrategy")->where(array("status"=>1))->select();
			for($i=0;$i<count($luStrategys);$i++){
				$id=$luStrategys[$i]['id'];
				$picture=M("Picture")->where(array("id"=>$id))->find();
				$luStrategys[$i]['img']=$picture['path'];
			}

			$result=$luStrategys;
		}else if($id==2){
			$luStrategyStocks=M("LuStrategyStock")->where("status=1 and id>=0")->select();
			
			$result=$luStrategyStocks;
		}
		$this->ajaxReturn($result);
	}

}
