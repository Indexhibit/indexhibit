function get_last_visit(c_name)
{
	if (document.cookie.length > 0)
	{
		var visited = document.cookie.indexOf(c_name + "=")

		if (visited != -1)
		{ 
			visited = visited + c_name.length + 1 
			var c_end = document.cookie.indexOf(";", visited)
			
			if (c_end == -1) c_end = document.cookie.length
			//return unescape(document.cookie.substring(visited, c_end))
			return true;
		} 
	}

	return false;
}

function set_last_visit(c_name, value, expiredays)
{
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie = c_name + "=" + escape(value) +
	((expiredays==null) ? "" : ";expires=" + exdate.toGMTString()) + '; path=/';
}

function do_statistics()
{
	var lasted = get_last_visit('last_visit');
	(lasted == '') ? 0 : 1;

	var refer = (document.referrer != '') ? document.referrer : 'none';
	var path = window.location.pathname;

	// get the grow content via ajax
	$.post('/ndxzsite/plugin/ajax.php', { jxs : 'statistics', last_visit : lasted, url : path, referrer : refer }, 
		function(html) 
		{
			//alert(html);
			// set the cookie
			set_last_visit('last_visit', 'true', 365);
			return false; 
		});
}

// if no cookie it's a unique
// need a jquery.ajax call