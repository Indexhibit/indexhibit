var win_height = 0;
var node = new Array();
var active = 0;
var centered = false;
var open = false;
var adjust = 0;
var mousemove = false;
var previous_node = 0;
var current_node = 0;
var processing = false;

var preloadFrame = 1;
var preloadActive = false;
var preloadAnimTimer;
var output;
var lheight;

$(document).ready(function() 
{
	formatting('body');
	makeNodes();
	
	var loading_image = new Image();
	loading_image.onload = function() { lheight = loading_image.height; }
	loading_image.src = baseurl + '/ndxzsite/img/loading/dark-small2.png';
});

$(window).resize(function() 
{
	win_height = $(window).height();

	if (open == true)
	{
		// these all need to be same
		$('#overlay').css('height', win_height + 'px');	
		$('#dialog').css('height', win_height + 'px');	
		$('#inner-content').css('height', win_height + 'px');
		$('a.link').css('height', win_height);
	}
});

// need to set window resize functions too

function next()
{
	if (processing == false)
	{
		processing = true;
		var tmp = node.length;	
		active = active + 1;
		if ((active + 1) > tmp) active = 0;
		getNode(node[active]);
	}
}

function previous()
{
	if (processing == false)
	{
		processing = true;
		var tmp = node.length;
		active = active - 1;
		if ((active + 1) == 0) active = (tmp - 1);
		getNode(node[active]);
	}
}

function getNode(id) 
{
	processing = true;

	loading();
	
	$('#o' + previous_node).remove();
	
	// put the loading-dock here
	// preload the image
	// need to make sure it's an image
	var filesrc = $('a#aaa' + id).attr('href');
	
	// is it an image?
	//$("a:has(img)")
	var match = filesrc.match(/gif|jpg|jpeg|png/gi);
	
	if (match != null) var image = new Image();
	
	// get the grow content via ajax
	$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'ndxzbox', i : id, center : centered, height : win_height }, 
		function(html)
		{
			if (match == null)
			{
				current_node = id;
				preloadActive = false;
				fillOverlay(html.output, html.width, html.height, html.description);
				updatePosition(id);
				//preloadActive = false;
				//$('#loading-dock').hide();
				processing = false;
			}
			else
			{
				image.onload = function() 
				{ 
					current_node = id;
					preloadActive = false;
					fillOverlay(html.output, html.width, html.height, html.description);
					updatePosition(id);
					//preloadActive = false;
					//$('#loading-dock').hide();
					processing = false;
				};
				image.src = filesrc;
			}
	}, 'json');
		
	return false;
}



function updatePosition(id)
{
	//var tmp = node.length;
	
	var html = active + 1;
	html += ' / '
	html += node.length;
	$('#position').html(html);
	//alert( $('#pic-holder').height() );
}

function makeNodes() {
    var count = 0;
    $('.picture_holder a').each(function()
    {    
        var tmp = $(this).attr('id');
        if (tmp) {
            node[count] = tmp.replace('aaa', '');
            count++;
        }
    });
}


function formatting(context_selector) 
{	
	var context = $(context_selector);
	win_height = $(window).height();
	
	$('.overlay', context).click(function() {
		overlay(context, $(this));
		return false;
	});
}


function preloadImages()
{

}


function overlay(context, trigger) 
{
	open = true;

	var url = trigger.attr('href');
	var id = trigger.attr('id').replace('aaa', '');
	
	current_node = id;
	
	openOverlay();
	loading();
	
	// need to advance the 'active' node for next and previous functions
	active = jQuery.inArray(id, node);
	
	var filesrc = $('a#aaa' + id).attr('href');
	
	// is it an image?
	//$("a:has(img)")
	var match = filesrc.match(/gif|jpg|jpeg|png/gi);

	if (match != null) var image = new Image();

	// get the grow content via ajax
	$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'ndxzbox', i : id, center : centered, height : win_height }, 
		function(html) 
		{
			if (match == null)
			{
				current_node = id;
				preloadActive = false;
				fillOverlay(html.output, html.width, html.height, html.description);
				updatePosition(id);
				//preloadActive = false;
				//$('#loading-dock').hide();
				processing = false;
			}
			else
			{
				image.onload = function() 
				{ 
					current_node = id;
					preloadActive = false;
					fillOverlay(html.output, html.width, html.height, html.description);
					updatePosition(id);
					//preloadActive = false;
					//$('#loading-dock').hide();
					processing = false;
				};
				image.src = filesrc;
			}
	}, 'json');
	//});
	
	// we should start preloading the images here...
	
	return false;
}


function show_loader()
{
	var h = $(window).height();
	var w = $(window).width();
	
	$('#loading-dock').css({'top' : ((h - 35) / 2), 'left' : ((w - 35) / 2)});
	$('#loading-dock').fadeIn(1000);
}


// Get Current Y position
function getYScroll() 
{
    var yScroll;
    if (self.pageYOffset) {
        yScroll = self.pageYOffset;
    } else if (document.documentElement && document.documentElement.scrollTop){  // Explorer 6 Strict
        yScroll = document.documentElement.scrollTop; 
    } else if (document.body) {// all other Explorers
        yScroll = document.body.scrollTop;
    }
	//alert(yScroll);
    return yScroll;
}

//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.com
// Edit for Firefox by pHaez
//
function getPageSize()
{	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;

	if (self.innerHeight) {	// all except Explorer
		if(document.documentElement.clientWidth){
			windowWidth = document.documentElement.clientWidth; 
		} else {
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		var pageHeight = windowHeight;
	} else { 
		var pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		var pageWidth = xScroll;		
	} else {
		var pageWidth = windowWidth;
	}

	var arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}



function openOverlay() 
{
	var yScroll = getYScroll();
	var arrayPageSize = getPageSize();
	
	var append = "<div id='overlay' class='" + theme + "-overlay'></div>";
	append += "<div id='dialog' class='" + theme + "-dialog'>";
	append += "<div id='inner-content'>";
	append += "<div id='inner-top'></div>";
	append += "<div id='inner-bot'><div id='position'></div></div>";
	append += "<div id='loading-dock'></div>";
	append += "<div class='the-content'></div>";
	//append += "<div id='dialog-close-layer'><a href='#' onclick=\"closeOverlay(); return false;\">&nbsp;</a></div></div>";
	append += "<div id='dialog-close'><a href='#' onclick=\"closeOverlay(); return false;\">Close</a></div>";
	append += "</div>";
	append += "</div>";

	$('body').append(append);
	
	$('#overlay').css('height', win_height + 'px');	
	$('#inner-content').css('height', win_height + 'px');

	$('#overlay').fadeIn('fast');
	$('#dialog').css('top', '0');	
}



function movement()
{
	if (mousemove == true)
	{
		//alert('yo');
		//$('#inner-top').fadeOut('slow');
	}
}


// need more research on this
function resize()
{
	var yScroll = getYScroll();
	// the next line is important
	//$('#dialog').css('top', yScroll);
	//$('#dialog').css({'position' : 'fixed'});
}

function check_the_numbers(imgx, imgy)
{
	var win = $(window).height();

	// height is bigger than browser window height
	if ((parseInt(adjust) + parseInt(imgy)) > win)
	{
		var found = $('#o' + current_node + ' #innerd').find('img');

		if (found.length == 0) 
		{
			// resize the image
			var new_height = imgy;
			var new_width = imgx;
		}
		else
		{
			// resize the image
			//var new_height = imgy - parseInt(adjust);
			//var new_width = parseInt((new_height * imgx) / imgy);
			
			var new_height = win - 120;
			var new_width = parseInt((new_height * imgx) / imgy);

			$('#o' + current_node + ' #innerd img').attr('width', new_width);
			$('#o' + current_node + ' #innerd img').attr('height', new_height);
		}
		
		// and padding?
		var new_padding = parseInt(new_height) + parseInt(adjust);
		var tmp = parseInt((((win - new_padding)) / 2));
		
		$('#o' + current_node + ' #innerd').css('padding-top', tmp);
		$('a.link').css('height', $(window).height());
	}
	else
	{
		var found = $('#innerd').find('img');
		if (found.length == 0) 
		{
			//alert('no img2');
			var new_height = imgy;
			var new_width = imgx;
		}
		else
		{
			// resize the image
			var new_height = imgy;
			var new_width = parseInt((new_height * imgx) / imgy);
			
			$('#o' + current_node + ' #innerd img').attr('width', new_width);
			$('#o' + current_node + ' #innerd img').attr('height', new_height);
		}

		// and padding?
		var new_padding = parseInt(new_height) + parseInt(adjust);
		var tmp = parseInt((((win - new_padding)) / 2));

		$('#o' + current_node + ' #innerd').css('padding-top', tmp);
		$('a.link').css('height', $(window).height());
	}
}


function fillOverlay(content, imgx, imgy, description) 
{
	$('#loading-dock').hide();

	if (previous_node == 0)
	{
		$('.the-content').html(content);
	}
	else
	{
		$('.the-content').append(content);
	}

	
	(description != null) ? $('#inner-top').html(description) : '';
	
	check_the_numbers(imgx, imgy);
	
	$('#o' + current_node).css('z-index', 11);

	// need to figure out how to remove the previous div
	//$('#o' + previous_node).remove();
	//if (previous_node != 0) $('#o' + previous_node).fadeOut(500);
	$('#o' + current_node).fadeIn(1000);
	
	previous_node = current_node;
}


function closeOverlay() 
{
	open = false;
	active = 0;
	
	$('#overlay').fadeOut('fast', function() {
		$('#overlay').remove();
	});	
	$('#dialog').fadeOut('fast', function() {
		$('#dialog').remove();	
	});
	
	// research this
	$('#dialog').css({'position' : 'absolute'});
}



function addError(title, text, context) 
{
	$('.content', context).prepend('<div class="message message-error"><h3>' + title + '</h3><p>' + text + '</p></div>');
	formatting(context);
}

function ajaxError(context) {
	addError('Communication error', 'The browser could not communicate with the server. Please try again later.', context);
}

function toggleVisibility(div)
{
	$(div).css({'visibility' : 'visible'});
}


function isOverlay(context) {
	if (context.attr('id') == 'dialog') return true;
	else return false;
}

function isSet( variable )
{
	return( typeof( variable ) != 'undefined' );
}

$(document).keydown(function(e)
{
	if (open == true)
	{
		if (e.keyCode == 37) { 
			previous();
			return false;
	    }

		if (e.keyCode == 39) { 
			next();
			return false;
	    }
		// esc
		if (e.keyCode == 27) { 
			closeOverlay();
			return false;
	    }
	}
});


/// loading
function loading()
{
	// get height of current #slideshow
	//var h = $('#slideshow').height();
	
	//preloadActive = true;
	//preloadAnimStart();
	
	// replacement div
	var html = "<div class='loading' style='background-image: url(" + baseurl + "/ndxzsite/img/loading/" + theme + "-small2.png);'>&nbsp;</div>";
	//var html = "<div id='loading'><img id='loadimage' src='" + baseurl + "/ndxzsite/img/loading-" + theme + "-large.gif' /></div>";
	//html += "</div>";
	
	//$('#slideshow').replaceWith(html);
	
	var h = $(window).height();
	var w = $(window).width();
	
	$('#loading-dock').html(html);
	$('#loading-dock').css({'top' : ((h - 50) / 2), 'left' : ((w - 50) / 2)});
	$('#loading-dock').fadeIn(1000);
	
	preloadActive = true;
	preloadAnimStart();
}

// adapted from Cabel/Panic Fancyzoom - credited totally goes to him
// http://www.panic.com
function preloadAnimStart() 
{
	preloadTime = new Date();
	preloadFrame = 1; preloadActive = true;
	preloadAnimTimer = setInterval('preloadAnim()', 100);
}

function preloadAnim(from) 
{
	var frames = (lheight / 24);

	if (preloadActive != false) 
	{
		$('.loading').css('background-position', 'center ' + (-24 * preloadFrame) + 'px');
		preloadFrame++;
		if (preloadFrame > (frames - 1)) { preloadFrame = 0; }
	} 
	else 
	{  
		clearInterval(preloadAnimTimer);
		preloadAnimTimer = 0;
		preloadActive = false;
	}
}