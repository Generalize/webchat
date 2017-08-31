<?php
namespace app\behavior;

class Json{
	public function run(&$input){
		if(is_array($input->getData())){
			$json = json_encode($input->getData());
			$input->data($json);
		}
	}
}