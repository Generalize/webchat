<?php
namespace app\index\model;

class App extends \think\Model
{
	private static $miniProgram = "";
	protected $table = 'user';
	protected static function init(){
		vendor("EasyWeChat");
		$options = [
			'mini_program' => [
				'debug'  => true,
				'app_id' => config('wx.little_program')['app_id'],
				'secret' => config('wx.little_program')['app_secret'],
				'token'  => 'tttd',
				'aes_key' => config('wx.little_program')['aes_key'], // 可选
				'log' => [
					'level' => 'debug',
					'file'  => '/tmp/easywechat.log', // XXX: 绝对路径！！！！
				],
			],
			//...
		];
		self::$miniProgram = (new \EasyWeChat\Foundation\Application($options))->mini_program;
	}
	
	public function registry(){
		$input = [];
		$input['code'] = input('get.code');
		$input['iv'] = input('get.iv');
		$input['encryptedData'] = input('get.encryptedData');
		$sessionKey = self::$miniProgram->sns->getSessionKey($input['code'])['session_key'];
		$userData = self::$miniProgram->encryptor->decryptData($sessionKey, $input['iv'], $input['encryptedData']);
		unset($userData['watermark']);
		$exists = \think\Db::table('user')->
				where(['openId' => $userData['openId']])->
				find();
		if($exists){
			$uid = $exists['id'];
			$this->save([
				'last_login_time' => date('YmdHis')
			],['id' => $uid]);
		}else{
			$file = file_get_contents($userData['avatarUrl']);
			$fi = new \finfo(FILEINFO_MIME_TYPE);
			$ymd = date('Ymd');
			$saveName = ROOT_PATH . 'public/uploads/images/';
			$fileName = $ymd . DS . md5($userData['avatarUrl']) . '.' . explode(DS,$fi->buffer($file))[1] ;
			if(!file_exists($saveName . $ymd)){
				mkdir( $saveName . $ymd, 0755 , true);
			}
			if(file_put_contents($saveName .  $fileName , $file)){
				$userData['avatarUrl'] = $fileName;
			}
			$userData['registry_time'] = time();
			$this->data($userData);
			$this->save();
			$uid = $this->id;
			 \think\Db::table('room_user_real')->
					data(['uid' => $uid,'rid' => 0])->
					insert();
			$frendsName = 'frends_'.$uid;
			$createUserFrendsTable = "create table $frendsName(
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`fuid` INT(10) UNSIGNED NOT NULL COMMENT '好友ID',
				`del` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否被删除',
				`black` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否被拉黑',
				`speak_count` INT(10) UNSIGNED NOT NULL COMMENT '说话次数',
				`add_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加这个好友时间',
				PRIMARY KEY (`id`)
			)
			COMMENT='用户好友表'
			COLLATE='utf8mb4_general_ci'
			ENGINE=InnoDB
			;
			"; //用户好友表
			\think\Db::execute($createUserFrendsTable);
			$msgName = 'msg_'.$uid;
			$createUserMsgTable = "create table $msgName(
				`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
				`sid` INT(10) UNSIGNED NOT NULL COMMENT '未读消息ID',
				`rid` INT(10) UNSIGNED NOT NULL COMMENT '房间ID',
				`read` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否已读',
				`add_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发言时间',
				PRIMARY KEY (`id`)
			)
			COMMENT='未读消息表'
			COLLATE='utf8mb4_general_ci'
			ENGINE=InnoDB
			;
			"; //用户未读消息表
			\think\Db::execute($createUserMsgTable);
		}
		return encrypt($uid);
	}
	
	public function saveFile(){
		$token = decrypt(input('post.token'));
		$rid = decrypt(input('post.r'));
		
		$user = \think\Db::table('user')->
				where('id',$token)->
				find();
		$room = \think\Db::table('room')->
				where('id',$rid)->
				find();
		
		if(empty($user) || empty($room)){
			$msg = ['m' => 'nothing'];
			throw new \app\common\Ex($msg);
		}
		
		if(request()->file('image')){
			$file = request()->file('image');
			$saveType = 'images';
		}else if(request()->file('audio')){
			$file = request()->file('audio');
			$saveType = 'audio';
		}
		$pattern = '/.*\.php/'; //判断上传的文件是否是php后缀名防止http注入
		if(preg_match($pattern,$file->getInfo()['name'])){
			$msg = ['m' => 'nothing'];
			throw new \app\common\Ex($msg);
		}
		$info = $file->move(ROOT_PATH . 'public/uploads/' . $saveType);
		if($file->getError()){
			$msg = ['m' => '上传错误'];
			return $msg;
		}
		switch ($saveType){
			case 'images':
				return $info->getSaveName();
				break;
			case 'audio':
				$convert = '/home/www/think/public/uploads/audio/' . $info->getSaveName();
				$sh = '/bin/sh /home/www/silk/converter.sh ' . $convert . ' mp3';
				exec($sh,$output);
				$grep = '/\..*/';
				$covertedSaveName = preg_replace($grep , '.mp3' ,$info->getSaveName());
				return $covertedSaveName;
				break;
		}
	}
}
