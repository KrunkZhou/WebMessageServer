<?php
/**
 * KRUNK.CN KDB 数据库主程序
 * @ Version: 1.5
 * @ Date: 2020/01/23
 * @ Website: https://api.krunk.cn/kdb/
 * @ Dev Website: http://kblog.krunk.cn/view.php?post=kdb
 */
class kdb {
	private $_kdb_version = '1.5';
	/** 数据库文件夹 */
	private $_kdb_dir = __DIR__ . '/kdb/';
	/** 默认数据库名称 */
	private $_kdb_tablename = 'default_kdb';
	/** 数据库后缀 */
	private $_kdb_extension = 'kdb';
	/** 文件名加密 */
	private $_kdb_encrypt = false;
	/** 缓存 DB Array */
	private $kdb_cache = [];

	/** 默认构造函数 */
	public function __construct(array $config = []) {
		$config = array_merge([
			'extension' => $this->_kdb_extension,
			'encrypt' => $this->_kdb_encrypt,
			'dir' => $this->_kdb_dir,
		],$config);
		
		$this->set_kdb_dir($config['dir']);
		$this->set_kdb_extension($config['extension']);
		$this->set_kdb_encryption($config['encrypt']);
	}
	private function set_kdb_dir($dir){
		$this->_kdb_dir = $dir;
	}
	/**
	 * 拆入数据
	 */
	public function insert($table, $new_data){
		$this->_load_db_table($table);

		if(!empty($new_data)){
			$id = $this->get_unique_hash_id();
			$this->kdb_cache[$table][$id] = $new_data;
			if($this->write_to_disk($table)){
				return $id;
			}
			return false;
		}
		return false;
	}
	/**
	 * 通过key获取数据
	 */
	public function find_one($table, $condition = null){
		$this->_load_db_table($table);
		
		if(empty($this->kdb_cache[$table]))
			return $this->find($table);

		/** 无条件 */
		if (!$condition) {
			return $this->kdb_cache[$table];
		}
		/** Array */
		if(is_array($condition)){
			$data = [];
			foreach($this->kdb_cache[$table] as $k => $v){
				foreach($condition as $condition_key => $condition_value){
					if(!isset($v[$condition_key]) || $v[$condition_key] != $condition_value){
						continue 2;
					}
				}
				$data[$k] = $v;
			}
			return $data;
		/** Key */
		}else{
			return isset($this->kdb_cache[$table][$condition]) ? $this->kdb_cache[$table][$condition] : false;
		}
	}
	/**
	 * 获取所有数据
	 */
	public function find($table){
		$this->_load_db_table($table);
		return $this->kdb_cache[$table];
	}
	/**
	 * 通过ID删除
	 */
	public function delete($table, $id = null){
		$this->_load_db_table($table);

		if(!$id){
			//return $this->delete_all($table);
		}else{
			if(isset($this->kdb_cache[$table][$id])){
				unset($this->kdb_cache[$table][$id]);
				return $this->write_to_disk($table);
			}
		}
		return false;
	}
	/**
	 * 删除整个table
	 */
	public function delete_all($table) {
		$this->set_db_table($table);
		unset($this->kdb_cache[$table]);
		return $this->write_to_disk($table);
	}
	/**
	 * 通过ID更新
	 */
	public function update($table, array $data, $id){
		$this->_load_db_table($table);

		if(isset($this->kdb_cache[$table][$id])){
			$this->kdb_cache[$table][$id] = array_merge(
				$this->kdb_cache[$table][$id],
				$data
			);
			if($this->write_to_disk($table)){
				return $this->kdb_cache[$table];
			}else{
				return false;
			}
		}
		return false;
	}
	/**
	 * 通过ID重置后更新
	 */
	public function reset($table, array $data, $id){
		$this->_load_db_table($table);

		if(isset($this->kdb_cache[$table][$id])){
			$this->kdb_cache[$table][$id] = $data;
			if($this->write_to_disk($table)){
				return $this->kdb_cache[$table];
			}else{
				return false;
			}
		}
		return false;
	}

	/**
	 * 获取随机Key
	 */
	private function get_unique_hash_id(){
		return md5($_SERVER['REQUEST_TIME'] + mt_rand(1000,9999));
	}
	/**
	 * 获取文件路径
	 */
	private function get_kdb_table_path($table) {
		if ($this->_check_table_path()) {
			$filename = strtolower($table);
			return $this->get_db_path() . $this->_get_file_hash($table) . '.' . $this->get_kdb_extension();
		}
	}
	/**
	 * 获取db路径
	 */
	private function get_db_path(){
		return $this->_kdb_dir . '/';
	}
	/**
	 * 写入数据
	 */
	private function write_to_disk($table){
		if(!isset($this->kdb_cache[$table])){
			//return file_put_contents($this->get_kdb_table_path($table),'');
			return unlink($this->get_kdb_table_path($table));
		}
		return file_put_contents($this->get_kdb_table_path($table),json_encode($this->kdb_cache[$table]));
	}
	/**
	 * 检查文件夹是否存在并创建如果不存在
	 */
	private function _check_table_path() {
		static $cache = null;
		if($cache !== null){
			return $cache;
		}
		if (!is_dir($this->get_db_path()) && !mkdir($this->get_db_path(), 0775, true)) {
			$cache = false;
			throw new Exception('KDB 无法创建数据库文件夹 Unable to create file directory ' . $this->get_db_path());
		} elseif (!is_readable($this->get_db_path()) || !is_writable($this->get_db_path())) {
			if (!chmod($this->get_db_path(), 0775)) {
				$cache = false;
				throw new Exception($this->get_db_path() . ' must be readable and writeable');
			}
		}
		$cache = true;
		return true;
	}
	/**
	 * 设置数据库名称
	 */
	private function set_db_table($name){
		if(!isset($this->kdb_cache[$name])){
			$this->kdb_cache[$name] = [];
		}
		$this->current_kdb_tablename = $name;
	}
	/**
	 * 读取数据库
	 */
	private function _load_db_table($table) {
		if(!isset($this->kdb_cache[$table])){
			if(!is_file($this->get_kdb_table_path($table))){
				$this->kdb_cache[$table] = [];
			}else{
				$this->kdb_cache[$table] = json_decode(file_get_contents($this->get_kdb_table_path($table)),true);
			}
		}
		$this->current_kdb_tablename = $table;
		return $this->kdb_cache[$table];
	}
	/**
	 * 获取缓存数据库名称
	 */
	private function get_kdb_table($table) {
		return $this->_kdb_tablename;
	}
	/**
	 * 设置文件后缀
	 */
	private function set_kdb_extension($ext) {
		$this->_kdb_extension = $ext;
		return $this;
	}
	/**
	 * 获取后缀
	 */
	private function get_kdb_extension() {
		return $this->_kdb_extension;
	}
	/**
	 * 获取加密hash
	 */
	private function _get_file_hash($filename) {
		if($this->_kdb_encrypt)
			return md5($filename);
		return $filename;
	}
	/**
	 * 设置加密
	 */
	private function set_kdb_encryption($ext){
		$this->_kdb_encrypt = $ext;
		return $this;
	}
	/**
	 * 获取KDB版本
	 */
	public function get_kdb_version(){
		return $this->_kdb_version;
	}

	/**
	 * 获取 api.krunk.cn 的 token
	 * 此Function非kdb必须 (如不用KAPI可删除)
	 */
	public function kapi_get_token($pass){
		$token = file_get_contents("https://api.krunk.cn/token/?pass=".$pass);
		return $token;
	}
	/**
	 * 检查 api.krunk.cn 的 token
	 * 此Function非kdb必须 (如不用KAPI可删除)
	 */
	public function kapi_check_token($token){
		$validate = file_get_contents("https://api.krunk.cn/token/?check=".$token);
		if ($validate=='1'){
			return '1';
		}else{
			return '0';
		}
	}
	/**
	 * 通过 api.krunk.cn 发送邮件
	 * 此Function非kdb必须 (如不用KAPI可删除)
	 */
	public function kapi_send_mail($token,$receiver,$head,$content){
		$url="https://api.krunk.cn/sendmail/mail.php?token=".$token."&receiver=".$receiver."&head=".$head."&content=".$content;
		$send = file_get_contents($url);
		if ($send[0]=='1'){
			return '1';
		}else{
			return '0 '.$send;
		}
	}
}
?>