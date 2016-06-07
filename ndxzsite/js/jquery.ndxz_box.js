var tmpnode = new Array();

(function($){
$.fn.ndxz_box = function(options) 
{   
	// maybe in the future
	var options = $.extend({
		br: 			0, 
		speed:          100, // speed of transitions
		single:       	true, // only allow one node open at a time
		start:          function(){}, // event trigger that fires at the start of execution.
		stop:           function(){}, // event trigger that fires at the end of execution.
		complete:       function(){}, // event trigger that fires once execution has finished.
		load: 			function(){}, // event trigger that fires upon page load. 
		loading: 		function(){}, // event trigger that fires upon page load. 
		doneload: 		function(){} // event trigger that fires upon page load complete.
	}, options);
	
	$.fn.ndxz_box.grower = function(obj, state)
	{
		var node = obj.id;
		node = node.replace('a', '');
		
		if (state == true)
		{
			// do the grow
			// clone things first
			tmpnode[node] = $('#node' + node).clone();
			
			// turn on loader
			$('#node' + node + ' .picture').prepend("<span class='loading'>Loading...</span>");
			
			// get the grow content via ajax
			$.post(baseurl + '/ndxzsite/plugin/ajax.php', { jxs : 'box', i : node, s : state }, 
				function(html) {
					// remove 'once'
					$('div').remove('.once');
			
					// display - loader disappears
					$('#node' + node).replaceWith(html);
					
					// reflow
					$.fn.ndxz_box.flow();
			
					return false;
			});
		}
		else
		{
			// close the grow
			// show the cloned content
			$('#node' + node).replaceWith(tmpnode[node]);
			
			// reflow
			$.fn.ndxz_box.flow();
	
			return false;
		}
	}
	
	$.fn.ndxz_box.flow = function()
	{
		$('div').remove('.once');
		var thiswidth = 0;
		var bigwidth = ($('#img-container').width());

		$('div.picture_holder').each(function()
		{
			var thewidth = $(this).width();
			thiswidth = parseInt(thiswidth) + parseInt(thewidth);

			if (thiswidth > bigwidth)
			{
				$(this).prev('div.picture_holder').after("<div class='once'><!-- --></div>");
				thiswidth = thewidth;
			}
		});
	}
	
	// let's set our breaks
	$.fn.ndxz_box.flow();
};
})(jQuery);

$(window).resize( function(){ $.fn.ndxz_box.flow(); });