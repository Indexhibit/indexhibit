function move_up() 
{
	var move = getCookie('move');
	if (move == '') return false;
	
	var menu = document.getElementById('menu');
	menu.scrollTop = move;
	
	// should i delete the cookie here
	// or reset it to zero
}


function do_click()
{
	moved = document.getElementById('menu');
	moved = moved.scrollTop;
	
	// record the cookie
	setCookie('move', moved, 1);
}

function getCookie(c_name)
{
	if (document.cookie.length > 0)
	{
		c_start = document.cookie.indexOf(c_name + "=")

		if (c_start != -1)
		{ 
			c_start = c_start + c_name.length + 1 
			c_end = document.cookie.indexOf(";", c_start)
			
			if (c_end == -1) c_end = document.cookie.length
			return unescape(document.cookie.substring(c_start, c_end))
		} 
	}
	return 0;
}

function setCookie(c_name,value,expiredays)
{
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie = c_name + "=" + escape(value) +
	((expiredays==null) ? "" : ";expires=" + exdate.toGMTString()) + '; path=/';
}