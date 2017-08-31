<?php
namespace app\fast\model;

class Http
{
	public $url = 'http://0.0.0.0:8988';
	/**
     * 收到信息
     * @param $connection
     * @param $data
     */
	
	
	
	public function onMessage($connection, $data)
    {
		
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {	
        echo "http已连接\n";
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        echo "http已断开连接\n";
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "http error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
		//echo "服务已经启动\n";
    }
}
