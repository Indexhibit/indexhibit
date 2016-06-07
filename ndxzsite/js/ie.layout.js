$(document).ready(function()
{
	if ($.browser.msie)
	{
		if (($.browser.version >= 6) && ($.browser.version < 7))
		{
			// change this value if you change the width of the #index/#exhibit
			var width = 215;
			
			$('#index').css('width', width);
			$('#exhibit').css('margin-left', width);
		}
	}
});