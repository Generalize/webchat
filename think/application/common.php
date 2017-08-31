<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function encrypt($data){  //对称加密
	
	$str =  openssl_encrypt($data, config('openssl.method'),config('openssl.key'),0,config('openssl.iv')); //openssl对称加密
	$asciistr = '';
	for($i = 0 ; $i < strlen($str) ; $i++){ //为了防止加密后的数据中出现 url不支持的字符  ascii编码
		if($i == strlen($str) - 1 ){
			$asciistr .=  ord($str[$i]);
		}else{
			$asciistr .=  ord($str[$i]) . 'SS';
		}
		
	}
	return $asciistr;
}

function decrypt($data){
	$arr = explode('SS',$data);
	$str = '';
	for($i = 0 ; $i < count($arr) ; $i++){  //ascii解码
		$str .= chr($arr[$i]);
	}
	return openssl_decrypt($str, config('openssl.method'),config('openssl.key'),0,config('openssl.iv'));  //openssl对称解密
}