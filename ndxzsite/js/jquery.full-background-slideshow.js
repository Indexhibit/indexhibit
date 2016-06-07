var current_position = 0;
var previous_position = 0;

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


function loading()
{
	var html = "<div id='theloader'><img src='" + baseurl + "/ndxzsite/img/loading/24x24loader.gif' /></div>";
	$('#full-background-controls').append(html);
}


function next()
{
	loading();
	var tmp_length = bgimgs.length;

	current_position = current_position + 1;
	
	if (current_position > (tmp_length -1))
	{
		current_position = 0;
	}
	
	var next_image = bgimgs[current_position];
	
	var image = new Image();
	image.onload = function() 
	{
		$('#theloader').remove();
		$('#random-background').html("<img src='http://www.indexhibit.dev" + next_image + "' width='" + this.width + "' height='" + this.height + "' />");
		bg_img_resize();
	};
	image.src = 'http://www.indexhibit.dev' + next_image;
}


function previous()
{
	loading();
	var tmp_length = bgimgs.length;

	current_position = current_position - 1;
	
	if (current_position < 0)
	{
		current_position = (tmp_length - 1);
	}
	
	var next_image = bgimgs[current_position];
	
	var image = new Image();
	image.onload = function() 
	{
		$('#theloader').remove();
		$('#random-background').html("<img src='" + baseurl + next_image + "' width='" + this.width + "' height='" + this.height + "' />");
		bg_img_resize();
	};
	image.src = baseurl + next_image;
}