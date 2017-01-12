<!doctype html>
<html lang='{{site_lang}}'>
<head>
<meta charset='utf-8'>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=10.0, user-scalable=yes, minimal-ui" />
<title><last:ndxz_title /> : {{obj_name}}</title>
<last:page:meta />
<link rel='alternate' type='application/rss+xml' title='RSS' href='{{baseurl}}/xml/' />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/{{obj_theme}}/reset.css<last:page:version: />' type='text/css' />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/{{obj_theme}}/base.css<last:page:version: />' type='text/css' />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/{{obj_theme}}/style.css<last:page:version />' type='text/css' />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/mobile/mobile.css<last:page:version />' type='text/css' />
<last:page:css />
<last:page:javascript />
<last:page:onready /><plugin:backgrounder />
<script type='text/javascript' src='{{baseurl}}/ndxzsite/js/jquery.js<last:page:version />'></script>
<script type='text/javascript' src='{{baseurl}}/ndxzsite/mobile/mobile.js<last:page:version />'></script>
</head>
<body class='{{object}} section-{{section_id}} exhibit-{{id}} format-{{format}}'>
<div id='index'>
<div class='container'>

<div class='top'>{{obj_itop}}</div>
<plugin:index:load_index />
<div class='bot'>{{obj_ibot}}</div>

<last:page:append_index />
</div>
</div>

<div id='exhibit'>
<div class='container'>

<div class='top'><!-- --></div>
<!-- text and image -->
<plugin:page:exhibit />
<!-- end text and image -->

</div>
</div>

<div id='closing_layer'></div>
<div class='index-toggle'><a id='nav-toggle' href="javascript:void(0)"><span></span></a></div>

<plugin:page:append_page />
<plugin:page:closing />
</body>
</html>