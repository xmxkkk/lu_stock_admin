<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 分类模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class LuStrategyModel extends Model{
	
	/* 自动验证规则 */
    protected $_validate = array(
        array('title', 'require', '名字不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('attr', 'require', '描述不能为空', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
    	array('img', 'require', '图片不能为空', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
    );

}