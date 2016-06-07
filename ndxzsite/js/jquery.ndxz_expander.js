/*
The original expanding menu was created by Ross Cairns.
Much thanks to him for creating something that became so useful.

This version of expanding is suited to be used with Indexhibit v0.9....and greater.

Vaska / Indexhibit.org
*/

(function($){
$.fn.ndxzExpander = function(options) 
{   
    var options = $.extend({
		active: 		"", 
		speed:          100, // speed of transitions
		single:       	true, // only allow one node open at a time
		_identifier: $(this)
	}, options);
	
	//var exclude = new Array();
	//exclude[0] = 'section_4';
	
	$.fn.ndxzExpander.init = function()
	{
		// let's add a feature to ignore certain sections altogether
		// need to use each for this then
		$('ul.section').each(function()
		{
			//if (jQuery.inArray(this.id, exclude))
			//{
				
				//alert( '#menu ul#' + this.id + ' span.section_title' );
				// need to rewrite the expander links
				$('#menu ul#' + this.id + ' li a span.section_title').unwrap();
				$('#menu ul#' + this.id + ' li span.section_title').next().hide();
				$('#menu ul#' + this.id + ' li span.subsection_title').next().hide();
				$('#menu ul#' + this.id + ' span.section_title').bind('click', function(){ $.fn.ndxzExpander.expander(this); return false; });
				$('#menu ul#' + this.id + ' span.subsection_title').bind('click', function(){ $.fn.ndxzExpander.expander(this); return false; });
			//}
		});

		// show active
		$.fn.ndxzExpander.active();
	}
	
	/*
	<ul class='section active_section' id='section_2'>
	<li id='section_title_2'>
	<a href='http://www.indexhibit.dev/project/'><span id='section_link_2' class='section_title active'>Projects</span></a>
	*/
	
	$.fn.ndxzExpander.active = function()
	{
		//$('#menu ul.active_section li a span.active').nextAll('ul').toggle(options.speed);
		
		// open up the pathway
		// works with active pages - but not section tops...
		$('li.active').parents('ul').show();
		
		// let's register the active ul - messy
		var a = new Array(); var b;
		//a = $('span.active').parents('ul').map(function () { return this.id; }).get().reverse();
		a = $('li.active').parents('ul').map(function () { return this.id; }).get().reverse();
		
		// if there is no active link at all
		// review this later...
		if (a == '')
		{
			// maybe we need to look/expand for an active section instead?
			$('ul.active_section li ul.sub_section').show();
			return false;
		}
		else
		{
			b = a[0];
			b = b.replace('section_', '');
			b = 'section_link_' + b;
		}
		
		options.active = b;
	}

	$.fn.ndxzExpander.expander = function(obj)
	{
		// nothing if it's the active part
		if (options.active == obj.id) return false;

		// we need to make sure it's not themselves
		if (options.single == true)
		{
			// this works but not with sub section clicks
			if ($('span#' + obj.id).hasClass('subsection_title'))
			{
				var tmp = obj.id;
				tmp = tmp.split('_');
				var subsection = tmp.pop();
				var section = tmp.pop();
				
				$('ul#section_' + section + ' ul.subsection:visible').hide(options.speed);
				
				// register the new active part
				options.active = 'section_link_' + section;
			}
			else
			{
				$('#menu ul li span.section_title').next().hide(options.speed);
				
				// register the new active part
				options.active = obj.id;
			}
		}

		var found = $('span#' + obj.id).parent().find('ul').length;

		if (found > 0)
		{
			// show all secondary
			$('span#' + obj.id).nextAll('ul').toggle(options.speed);
		}
		else
		{
			$('span#' + obj.id  + ' + ul').toggle(options.speed);
		}
	}
	
	// Initalize the expander
	$.fn.ndxzExpander.init(this);
};
})(jQuery);