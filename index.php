<?php
require('libs/Mustache.php');
require('templates/templates.php');

// config
define('GB_CACHE_TTL', 15*60);
define('GB_USERNAME', 'funkatron');


/************************************************************
 * DON'T MESS WITH ANYTHING BELOW
 ************************************************************/
class GimmeMe {
	
	protected $opts;

	protected $templates = array();

	protected $cache_prefix = 'GIMME___';

	protected $gb_username   = 'funkatron';
	protected $gb_collection = null;
	protected $gb_cache_ttl  = 900;

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

		apc_store($ck, $assets, GB_CACHE_TTL);
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

/*
 * Use this to add lambdas to the Mustache view object
 */
class GimmeAsset {
	
	public function __construct($data_obj) {
		$props = get_object_vars($data_obj);
		foreach($props as $key=>$val) {
			$this->{$key} = $val;
		}
	}

	public function nice_date() {
		return date("l, F j, Y g:sa", $this->date);
	}

	public function is_image() {
		return ('image' === $this->asset_type);
	}

	public function is_page() {
		return ('page' === $this->asset_type);
	}

	public function is_embed() {
		return ('embed' === $this->asset_type);
	}

	public function is_text() {
		return ('text' === $this->asset_type);
	}
	
	public function embed_html() {
		if (!$this->is_embed()) {
			return "not an embed!";
		}
		
		if (isset($this->content->info) && isset($this->content->info->html)) {
			return $this->content->info->html;
		}

		if (!isset($this->content->attributes)) {
			return "no attributes!";
		}

		$tag = strtolower($this->content->tag);

		// build attr string
		$attr_str = '';
		foreach($this->content->attributes as $key=>$val) {
			$key = trim($key);
			$val = trim($val);
			$attr_str .= "{$key}=\"{$val}\" ";
			$attr_str = trim($attr_str);
		}

		$html = "<{$tag} {$attr_str}>";
		return $html;
	}


}

// Now actually create the GimmeMe class and display
$gm = new GimmeMe(array(
		'templates'=>$templates,
		'gb_cache_ttl'=>GB_CACHE_TTL,
		'gb_username'=>GB_USERNAME,
		'gb_collection'=>null,
	)
);

$gm->go();