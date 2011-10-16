<?php
require('./Mustache.php');

// config
define('GB_CACHE_TTL', 15*60);
define('GB_USERNAME', 'funkatron');


// templates
$templates = array();
$templates['page'] = <<< EOF
<html>
<head>
	<title>{{username}}</title>
	<style>
	@import url('main.css');
	</style>
</head>
<body>

<div id="container">
	<h1>{{username}}</h1>

	<ul>
	{{#records}}
		{{>record}}
	{{/records}}
	</ul>

	<div class="more">View more in <a href="https://gimmebar.com/user/{{username}}">my Gimme Bar profile</a></div>

</div><!-- #container -->

<!-- AddThis Button BEGIN -->
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e9a50eb30ab82ff"></script>
<!-- AddThis Button END -->
</body>
</html>
EOF;

$templates['partials']['record'] = <<< EOF
	<li class="record">
		<a name="media_hash"></a>
		<h2 class="record-title"><a href="{{short_url}}">{{title}}</a></h2>
		<h3 class="record-meta">
			{{nice_date}} &mdash; <a href="{{source}}" class="record-source">source</a> &mdash; <a href="{{short_url}}" class="permalink">#</a>
		</h3>
		
		<div class="record-content">
			{{#content.display}}
			<img src="{{content.display}}">
			{{/content.display}}

			{{#content.thumb}}
			<img src="{{content.thumb}}">
			{{/content.thumb}}

			{{#content.info.html}}
			{{{content.info.html}}}
			{{/content.info.html}}		
		</div>

		{{#description}}
		<div class="record-description">{{description}}</div>
		{{/description}}

		<div id="share-{{id}}" class="addthis_toolbox addthis_default_style"
			addthis:url="{{short_url}}"
			addthis:title="{{title}}"
			addthis:description="{{description}}">
			<a class="addthis_button_preferred_1"></a>
			<a class="addthis_button_preferred_2"></a>
			<a class="addthis_button_preferred_3"></a>
			<a class="addthis_button_preferred_4"></a>
			<a class="addthis_button_compact"></a>
		</div>
	</li>
EOF;


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
		if (apc_exists($ck)) {
			return apc_fetch($ck);
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
 * Use this to add "lambdas" to the object
 */
class GimmeAsset {
	
	public function __construct($data_obj) {
		$props = get_object_vars($data_obj);
		foreach($props as $key=>$val) {
			$this->{$key} = $val;
		}
	}

	function nice_date() {
		return date("l, F j, Y g:sa", $this->date);
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