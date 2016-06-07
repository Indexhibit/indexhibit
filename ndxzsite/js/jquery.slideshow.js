var active = 0;
var preloadFrame = 1;
var preloadActive = false;
var preloadAnimTimer;

function next()
{
	var tmp = img.length;	
	active = active + 1;
	if ((active + 1) > tmp) active = 0;
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
	$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'slideshow', i : id }, 
		function(html) 
		{
			fillShow(html.output);
			updatePosition(active);
	//});
	}, 'json');
		
	return false;
}

function loading()
{
	// get height of current #slideshow
	var h = $('#slideshow').height();
	
	// replacement div
	//var html = "<div id='slideshow' style='height:" + h + "px;'>";
	//html += "<div id='loading'><img id='loadimage' src='" + baseurl + "/ndxzsite/img/loading/loading-1.png' /></div>";
	//html += "</div>";
	
	//var html = "<div id='slideshow' style='height:" + h + "px;'>";
	var html = "<div id='loading' style='color: white; position: absolute; z-index: 100; top: 5px; left: 5px;'>Loading " + (active + 1) + " of " + total + "</div>";
	//html += "</div>";
	
	//$('#slideshow').replaceWith(html);
	$('.picture').prepend(html);
	
	preloadActive = true;
	preloadAnimStart();
}

// adapted from Cabel/Panic Fancyzoom - credited totally goes to him
// http://www.panic.com
function preloadAnimStart() 
{
	preloadTime = new Date();
	preloadFrame = 1; preloadActive = true;
	document.getElementById("loading").src = baseurl+'/ndxzsite/img/loading/loading-'+preloadFrame+'.png';  
	preloadAnimTimer = setInterval("preloadAnim()", 100);
}

function preloadAnim(from) 
{
	if (preloadActive != false) {
		document.getElementById("loadimage").src = baseurl+'/ndxzsite/img/loading/loading-'+preloadFrame+'.png';
		preloadFrame++;
		if (preloadFrame > 12) preloadFrame = 1;
	} else {  
		clearInterval(preloadAnimTimer);
		preloadAnimTimer = 0;
		preloadActive = false;
	}
}

function fillShow(content)
{
	$('#slideshow').replaceWith(content);
	
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