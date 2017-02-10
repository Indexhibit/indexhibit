var active = 0;
var zindex = 999;
var disable_click = false;

// this is the init function
$(document).ready(function()
{
	// height of image plus height of title/caption
	// reset the height of the
	var tmp = $('#slideshow div#slide1000').height();
	var txt = $('#slideshow div#slide1000 .captioning').height();

	txt = (txt == null) ? 0 : txt;
	$('#slideshow').height(tmp);
});

function next()
{
	if (disable_click == true) return false;
	disable_click = true;
	
	var tmp = img.length;
	active = active + 1;
	if ((active + 1) > tmp) active = 0;
	getNode(img[active]);
}

function previous()
{
	if (disable_click == true) return false;
	disable_click = true;
	
	var tmp = img.length;
	active = active - 1;
	if ((active + 1) == 0) active = (tmp - 1);
	getNode(img[active]);
}

function show(id, order)
{
	// we need to set the active position differently
	active = order;
	getNode(id);
}

function getNode(id) 
{
	// display loading
	loading();

	// get the grow content via ajax
	$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'slideshow', i : id, z : zindex }, 
		function(html) 
		{
			fillShow(html.output, html.height, html.mime);
			disable_click = false;
			$('span#total em').html(active + 1);
	}, 'json');
		
	return false;
}

function loading()
{
	// remove previous and next slides
	$('a#slide-previous').remove();
	return;
}


function adjust_height(next)
{
	var adjust = 0;
	var current_height = $('#slideshow div#slide' + zindex).height();
	
	$('#slideshow').height(next);

	return;
}

function fillShow(content, next_height, mime)
{	
	// animate
	if ((fade == true))
	{
		$('#slideshow').append(content);
		
		var adj_height = $('#slideshow div#slide' + zindex).height();
		
		$('#slideshow div#slide' + (zindex + 1)).fadeOut('1000').queue(function(next){$(this).remove();});
		$('#slideshow div#slide' + zindex).fadeIn('1000');
		
		var tmp = $('#slideshow div#slide' + zindex + ' .captioning').height();
		tmp = (tmp == null) ? 0 : tmp;

		adjust_height(adj_height);
	}
	else
	{
		$('#slideshow').append(content);
		
		var adj_height = $('#slideshow div#slide' + zindex).height();

		$('#slideshow div#slide' + (zindex + 1)).remove();
		$('#slideshow div#slide' + zindex).show();
		
		var tmp = $('#slideshow div#slide' + zindex + ' .captioning').height();
		tmp = (tmp == null) ? 0 : tmp;
		
		adjust_height(adj_height);
	}
	
	// count down
	zindex--;
}

$.fn.preload = function() 
{
    this.each(function()
	{
        $('<img/>')[0].src = baseurl + '/files/dimgs/' + this;
    });
}

$(document).keydown(function(e)
{
	if (e.keyCode == 37) { previous(); }
	if (e.keyCode == 39) { next(); }
});