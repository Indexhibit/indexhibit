function bg_img_resize() 
{
	var w = $(window).width();
	var h = $(window).height();

	var win_width = w;
	var win_height = h;

	// we'll need to make sure we have this info...
	var iw = parseInt( $('#random-background img').attr('width') );
	var ih = parseInt( $('#random-background img').attr('height') );
	var rw = iw / ih;
	var rh = ih / iw;
	var sc = h * rw;

	if (sc >= w) 
	{
		nh = h;
		nw = sc;
	} 
	else 
	{
		sc = w * rh;
		nh = parseInt(sc);
		nw = parseInt(w);
	}
	
	// need to put a wrapper about body contents first
	$('body').prepend("<div id='random-background-wrapper' style='z-index: 5'></div>");
	//var tmp = "<div id='random-background-wrapper' style='z-index: 5'></div>";
	
	$('#menu').appendTo('#random-background-wrapper');
	$('#content').appendTo('#random-background-wrapper');
	
	//var menu = $('#menu').clone().remove();
	//var content = $('#content').clone().remove();
	
	
	//$('body').html("<div id='random-background-wrapper' style='z-index: 5'>" + menu + content + "</div>");
		
	$('#random-background').css({height: h, width: w});
	$('#random-background').css({ overflow: 'hidden' });
	$('#random-background img').css({height: nh, width: nw});
}