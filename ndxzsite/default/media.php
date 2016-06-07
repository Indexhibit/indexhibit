<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><last:ndxz_title /> : {{obj_name}}</title>
<last:page:meta />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/{{obj_theme}}/reset.css<last:page:version: />' type='text/css' />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/{{obj_theme}}/base.css<last:page:version: />' type='text/css' />
<link rel='stylesheet' href='{{baseurl}}/ndxzsite/{{obj_theme}}/style.css<last:page:version />' type='text/css' />
<last:page:css />
<last:page:javascript />
<last:page:onready /><plugin:backgrounder />
</head>
<body id='exhibit-{{id}}' class='section-{{section_id}}'<last:page:onload_js />>
<div id='index'>
<div class='container'>

<div class='top'>{{obj_itop}}</div>
<plugin:index:load_index />
<div class='bot'>{{obj_ibot}}</div>

<last:page:append_menu />
</div>
</div>

<div id='exhibit'>
<div class='container'>

<div class='top'><!-- --></div>
<!-- text and image -->
<plugin:submedia:display />
<!-- end text and image -->

</div>
</div>
<plugin:page:append_page />
<plugin:page:closing />
</body>
</html>