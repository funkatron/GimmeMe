<?php
// a bunch of mustache templates
$templates = array();
$templates['page'] = <<< EOF
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>{{username}} – Powered by Gimme Bar</title>
	<style>
	@import url('main.css');
	</style>
</head>
<body>

<div id="container">
	<h1>
		<div><img id="user-avatar" src="https://gimmebar.com/img/avatar/{{username}}"></div>
		<div id="user-name">{{username}}</div>
	</h1>

	<ul>
	{{#records}}
		{{>record}}
	{{/records}}
	</ul>

	<div class="more">View more in <a href="https://gimmebar.com/user/{{username}}">my Gimme Bar profile</a></div>

</div><!-- #container -->

{{#addthis_pubid}}
<!-- AddThis Button BEGIN -->
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid={{addthis_pubid}}"></script>
<!-- AddThis Button END -->
{{/addthis_pubid}}
</body>
</html>
EOF;

$templates['partials']['record'] = <<< EOF
	<li class="record">
		<a name="asset-{{id}}"></a>
		<h2 class="record-title"><a href="{{short_url}}">{{title}}</a></h2>
		<h3 class="record-meta">
			{{nice_date}} &mdash; <a href="{{source}}" class="record-source">source</a>
			&mdash; <a href="#asset-{{id}}" class="permalink">#</a>
		</h3>
		
		<div class="record-content">
			{{! image assets use this}}
			{{#is_image}}{{>content_image}}{{/is_image}}

			{{#is_page}}{{>content_page}}{{/is_page}}

			{{#is_embed}}{{>content_embed}}{{/is_embed}}

			{{#is_text}}{{>content_text}}{{/is_text}}

			{{#is_document}}{{>content_document}}{{/is_document}}
		</div>

		{{#description}}
			<div class="record-description">{{{formatted_description}}}</div>
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

$templates['partials']['content_image'] = <<< EOF
	{{#content.display}}
		<img src="{{content.display}}">
	{{/content.display}}

	{{^content.display}}
		<img src="{{content.full}}">
	{{/content.display}}

EOF;

$templates['partials']['content_page'] = <<< EOF
	{{#content.thumb}}
		<a href="{{content.original}}"><img src="{{content.thumb}}"></a>
	{{/content.thumb}}

	{{^content.thumb}}
		<a href="{{content.original}}">{{content.original}}</a>
	{{/content.thumb}}
EOF;

$templates['partials']['content_embed'] = <<< EOF
	{{{embed_html}}}
EOF;

$templates['partials']['content_text'] = <<< EOF
	{{{formatted_text_content}}}
EOF;

$templates['partials']['content_document'] = <<< EOF
	<a href="{{content.url}}">{{content.url}}</a>
EOF;
