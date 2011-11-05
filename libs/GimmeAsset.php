<?php
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

	public function is_document() {
		return ('document' === $this->asset_type);
	}

	public function formatted_text_content() {
		$text = $this->content;
		$text = nl2br($text);
		return $text;
	}

	public function formatted_description() {
		$text = trim($this->description);
		$text = nl2br($text);
		return $text;
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