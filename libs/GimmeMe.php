<?php
class GimmeMe {
	
	protected $opts;

	protected $templates = array();

	protected $cache_prefix = 'GIMME___';

	protected $gb_username   = 'funkatron';
	protected $gb_collection = null;
	protected $gb_cache_ttl  = 900; // 15m default

	public function __construct($opts=array()) {
		$this->opts = $opts;
		$this->templates = $opts['templates'];
		$this->gb_username = isset($opts['gb_username']) ? $opts['gb_username'] : null;
		$this->gb_collection = isset($opts['gb_collection']) ? $opts['gb_collection'] : null;
		$this->gb_cache_ttl = isset($opts['gb_cache_ttl']) ? $opts['gb_cache_ttl'] : null;
	}

	public function go() {
		$assets = $this->getAssets();
		echo $this->render($assets);
	}

	protected function getAssets(array $opts=null) {
		$url = 'https://gimmebar.com/api/v0/public/assets/'.$this->gb_username;
		if ($this->gb_collection) {
			$url .= '/'.$this->gb_collection;
		}

		if (!isset($opts)) {
			$opts = array();
		}

		$opts['limit'] = isset($opts['limit']) ? $opts['limit'] : 50;
		$url .= '?'.http_build_query($opts);
		
		$ck = $this->cache_prefix.$url;

		// check cache
		if ($cached = apc_fetch($ck)) {
			return $cached;
		}

		$json = file_get_contents($url);
		$assets = json_decode($json);
		$assets->username = $this->gb_username;

		apc_store($ck, $assets, $this->gb_cache_ttl);
		unset($json);

		return $assets;
	}

	protected function render($assets) {
		foreach($assets->records as &$record) {
			$record = new GimmeAsset($record);
		}

		$m = new Mustache($this->templates['page'], $assets, $this->templates['partials']);
		return $m->render();
	}

}