<?php
/*
 * 用于解决PHP在并发时候的锁控制，不同的锁之间并行执行，类似mysql innodb的行级锁
 */
class fileLock {
	private $path = '';
	//文件锁存放路径
	private $fp = '';
	//文件句柄
	private $lockFile = '';
	//锁文件
	/**
	 * 构造函数
	 * @param string $path 锁的存放目录
	 * @param string $name 锁 KEY
	 */
	public function __construct($name, $path = '') {
		if (empty($path)) {
			$this -> path = '/data/cache/lock/';
		} else {
			$this -> path = rtrim($path, '/') . '/';
		}
		!is_dir($this -> path) && mkdir($this -> path, 0777, true);
		$this -> lockFile = $this -> path . $name . '.lock';
	}

	public function __destruct() {
		$this -> unlock();
	}

	/**
	 * 加锁
	 */
	public function lock()
	{
		if( file_exists($this -> lockFile) )
		{
			$this->lock();
		}else{
			return file_put_contents($this -> lockFile, '');
		}
	}

	/**
	 * 解锁
	 */
	public function unlock()
	{
		if(file_exists($this -> lockFile))
		{
			unlink($this -> lockFile);
		}
		return true;
	}

}
