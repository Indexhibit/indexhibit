var active = 0;
var preloadFrame = 1;
var preloadActive = false;
var preloadAnimTimer;
var zindex = 999;
var disable_click = false;

function next()
{
	if (disable_click == true) return false;
	var tmp = img.length;	
	active = active + 1;
	if ((active + 1) > tmp) active = 0;
	disable_click = true;
	getNode(img[active]);
}

function previous()
{
	var tmp = img.length;
	active = active - 1;
	if ((active + 1) == 0) active = (tmp - 1);
	getNode(img[active]);
}

function getNode(id) 
{
	// display loading graphic
	loading();

	// get the grow content via ajax
	$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'slideshow2', i : id, z : zindex }, 
		function(html) 
		{
			fillShow(html.output, html.height);
			updatePosition(active);
			disable_click = false;
	//});
	}, 'json');
		
	return false;
}

function loading()
{
	// remove slide previous and slide next
	$('a#slide-next').remove();
	$('a#slide-previous').remove();
	
	// get height of current #slideshow
	var h = $('#slideshow').height();
	var html = "<div id='loading' style='color: #000; position: absolute; z-index: 2000; top: 12px; left: 9px;'><span style='background: #fff; padding: 3px;'>" + (active + 1) + "/" + total + "</span></div>";

	$('.picture').prepend(html);
	
	return;
}


function adjust_height(next)
{
	var adjust = 0;
	var current_height = $('#slideshow').height();
	
	if (current_height > next)
	{
		// larger
		adjust = (current_height - next);
		
		// animate
		$('#slideshow').animate({height: (current_height - adjust)}, 100);
	}
	else if (current_height < next)
	{
		// smaller
		adjust = (next - current_height);
		
		// animate
		$('#slideshow').animate({height: (current_height + adjust)}, 100);
	}
	else
	{
		// the same, do nothing
	}
	
	adjust = 0;
}

function fillShow(content, next_height)
{	
	$('#slideshow').append(content);
	
	var adj_height = $('#slideshow div#slide' + zindex).height();
	
	// get height of #slideshow
	adjust_height(adj_height);
	
	// animate
	$('#slideshow div#slide' + (zindex + 1)).fadeOut().delay(3000).queue(function(next){$(this).remove();});
	$('#slideshow div#slide' + zindex).fadeIn();
	
	// count down
	zindex--;
	
	preloadActive = false;
}

function updatePosition()
{
	$('#slideshow-nav span#total strong').html((active + 1));
}

$(document).keydown(function(e)
{
	if (e.keyCode == 37) { 
		previous();
		return false;
	}

	if (e.keyCode == 39) { 
		next();
		return false;
	}
});