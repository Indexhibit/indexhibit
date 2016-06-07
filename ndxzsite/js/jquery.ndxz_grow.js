(function($){
$.fn.ndxz_grow = function(options) 
{   
	var tmpnode = new Array();
	var opened = false;
	var active = 0;
	var closer = false;
	var enable = true;
	var preloadFrame = 0;
	var preloadActive = false;
	var preloadAnimTimer;
	
	// maybe in the future
	var options = $.extend({
		br: 			0, 
		lheight: 		0,
		speed:          100, // speed of transitions
		single:       	true, // only allow one node open at a time
		start:          function(){}, // event trigger that fires at the start of execution.
		stop:           function(){}, // event trigger that fires at the end of execution.
		complete:       function(){}, // event trigger that fires once execution has finished.
		load: 			function(){}, // event trigger that fires upon page load. 
		loading: 		function(){}, // event trigger that fires upon page load. 
		doneload: 		function(){} // event trigger that fires upon page load complete.
	}, options);
	
	$.fn.ndxz_grow.grower = function(obj, state)
	{
		// only one at a time
		if (options.single == true)
		{
			if (enable == false) return false;
			enable = false;
		}

		var node = obj.id;
		node = node.replace('a', '');
		
		// single image at a time mode
		if (options.single == true)
		{
			if (active != node)
			{
				// send a trigger to close below
				closer = true;
				var tmp = active;
				//if (active != 0) $('#node' + active).replaceWith(tmpnode[active]);
				active = node;
			}
			else
			{
				active = 0;
			}
		}
		
		if (state == true)
		{
			// do the grow
			// clone things first
			tmpnode[node] = $('#node' + node).clone();
			
			/*
				<div class='picture_holder' id='node33' style='width: 225px; height: 178px;'>
				<div class='picture' style='width: 200px;'>
				<div style='padding-top: 0;'>
				<a href='#' class='link' id='a33'  onclick="$.fn.ndxz_grow.grower(this, true); return false;"><img src='http://www.indexhibit.dev/files/gimgs/th-11_imagefile.jpg' width='200' height='113' alt='http://www.indexhibit.dev/files/gimgs/th-11_imagefile.jpg' /></a>
				</div>
				</div>
				<div class='captioning' style='width: 200px;'></div>
				</div>
			*/

			var imgw = parseInt($('#node' + node + ' .picture div img').width());
			var imgh = $('#node' + node + ' .picture div img').height();
			var imgp = $('#node' + node + ' .picture div').css('padding-top');
			imgp = parseInt(imgp.replace('px', ''));
			var itmp = parseInt(((imgh/2) - 12) + imgp); //loading image is 24x24
			var pw = parseInt($('#node' + node + ' .picture').width());

			if (pw != imgw)
			{
				if ($('#node' + node + ' .picture').css('text-align') == 'center')
				{
					imgw = pw;
				}
				else
				{
					imgw = imgw
				}
			}
			
			var isIE6 = $.browser.msie && $.browser.version < 7;
			
			if (!isIE6)
			{
				//style='background-image: url(" + baseurl + "/ndxzsite/img/loading/" + theme + "-small2.png);'
				preloadActive = true;
				$('#node' + node + ' .picture').prepend("<div class='loading' style='background-image: url(" + baseurl + "/ndxzsite/img/loading/dark-small2.png); top: " + itmp + "px; width: " + imgw + "px;'>&nbsp;</div>");
				$.fn.ndxz_grow.preloadAnimStart();
			}
			else
			{
				// alt loader
			}
			
			// get the grow content via ajax
			$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'grow', i : node, s : state }, 
				function(html) {
					// remove 'once'
					$('div').remove('.once');
					
					// happens at the same time
					if (closer == true)
					{
						if (tmp != 0) $('#node' + tmp).replaceWith(tmpnode[tmp]);
						closer = false;
					}
					
					preloadActive = false;
					// display - loader disappears
					$('#node' + node).replaceWith(html);
					
					// reflow
					$.fn.ndxz_grow.flow();
					
					if (options.single == true) enable = true;

					return false;
			});
		}
		else
		{
			// close the grow
			// show the cloned content
			$('#node' + node).replaceWith(tmpnode[node]);
			
			// reflow
			$.fn.ndxz_grow.flow();
			
			if (options.single == true) enable = true;

			return false;
		}
	}
	
	// adapted from Cabel/Panic Fancyzoom - credited totally goes to him
	// http://www.panic.com
	$.fn.ndxz_grow.preloadAnimStart = function()
	{
		preloadTime = new Date();
		preloadFrame = 1; 
		preloadActive = true;
		preloadAnimTimer = setInterval($.fn.ndxz_grow.preloadAnim, 100);
	}
	
	$.fn.ndxz_grow.preloadAnim = function()
	{
		var frames = (options.lheight / 24);

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
	
	$.fn.ndxz_grow.flow = function()
	{
		$('div').remove('.once');
		var thiswidth = 0;
		var bigwidth = ($('#img-container').width());
		i = 1;
		
		//alert(bigwidth);

		$('div.picture_holder').each(function()
		{
			var thewidth = $(this).width();
			thiswidth = parseInt(thiswidth) + parseInt(thewidth);
			
			if (thiswidth > bigwidth)
			{
				$(this).prev('div.picture_holder').after("<div class='once'><!-- --></div>");
				thiswidth = thewidth;
			}
			
			i++;
		});
	}
	
	// let's set our breaks
	//$.fn.ndxz_grow.flow();
	
	// loading graphic preload
	var loading_image = new Image();
	loading_image.onload = function() { options.lheight = loading_image.height; }
	loading_image.src = baseurl + '/ndxzsite/img/loading/dark-small2.png';
};
})(jQuery);


$(window).resize( function(){ $.fn.ndxz_grow.flow(); });