/*
Indexpand
Author: Indexhibit.org
Version: 1.2
*/

(function($){
$.fn.indexpand = function(options) 
{   
    var options = $.extend({
		active: 		'', 
		active_section: 0, 
		active_subsection: 0,
		speed:          100, // default speed of transitions
		exclude: 		'',	// exclude nodes
		single:       	true, // only allows one node open at a time
		atload: 		true,
		mouse: 			'click'
	}, options);
	
	$.fn.indexpand.init = function()
	{
		$('ul.section').each(function()
		{
			// -1 means it's not found
			if (jQuery.inArray(this.id, options.exclude) == -1)
			{
				// can we determine active section here?
				if ($('ul#' + this.id).hasClass('active_section'))
				{
					options.active_section = this.id;
				}	
				
				// we should check for mobile/tablets so that they only utilize 'click'
				if (options.mouse == 'click')
				{
					// need to rewrite the expander links
					$('#index ul#' + this.id + ' li a span.section_title').unwrap();
					$('#index ul#' + this.id + ' li span.section_title').next().hide();
					$('#index ul#' + this.id + ' li span.subsection_title').next().hide();
					$('#index ul#' + this.id + ' li span.section_title').bind('click', function(){ $.fn.indexpand.expander(this); return false; });
					$('#index ul#' + this.id + ' li span.subsection_title').bind('click', function(){ $.fn.indexpand.expander(this); return false; });
				}
				else
				{
					// do not turn the link into a non-link with 'over'
					$('#index ul#' + this.id + ' li span.section_title').next().hide();
					$('#index ul#' + this.id + ' li span.subsection_title').next().hide();
					$('#index ul#' + this.id + ' li span.section_title').bind('mouseover', function(){ $.fn.indexpand.expander(this); return false; });
					$('#index ul#' + this.id + ' li span.subsection_title').bind('mouseover', function(){ $.fn.indexpand.expander(this); return false; });
				}
			}
		});

		// show active
		$.fn.indexpand.active();
	}
	
	$.fn.indexpand.active = function()
	{	
		// make sure the active section is unfolded
		$('ul#' + options.active_section + ' li span').next('ul:first').show();

		// open up the pathway
		// works with active pages - but not section tops...
		$('li.active').parents('ul').show();
		
		// let's register the active ul - messy
		var a = new Array(); var b;
		
		// if we're returning it's a section top
		a = $('li.active').parents('ul').map(function () 
		{ 
			var tmp = options.active_section;

			if (tmp)
			{
				options.active = tmp.replace('_', '_title_');
			}
			else
			{
				options.active = '';
			}
			
			return false; }).get().reverse();
		
		// if there is no active link at all
		if (a == '')
		{
			// active section top is the default link
			var tmp = options.active_section;
			
			if (tmp)
			{
				options.active = tmp.replace('_', '_title_');
			}
			else
			{
				options.active = '';
			}
			
			//options.active = tmp.replace('_', '_title_');

			$('ul.active_section li ul.sub_section').show();
			return false;
		}
		else
		{
			b = a[0];
			b = (b != false) ? b.replace('section_', '') : '';
			b = 'section_' + b;
		}

		options.active = b;
	}

	$.fn.indexpand.expander = function(obj)
	{
		// we need to make sure it's not themselves
		if (options.single == true)
		{
			// nothing if it's the active part
			if (options.active == obj.id) return false;
			if (options.active_subsection == obj.id) return false;
			
			// this works but not with sub section clicks
			if ($('span#' + obj.id).hasClass('subsection_title'))
			{
				var tmp = obj.id;
				tmp = tmp.split('_');
				var subsection = tmp.pop();
				var section = tmp.pop();
				
				$('ul#section_' + section + ' ul.subsection:visible').hide(options.speed);
				
				// register the new active part
				options.active_subsection = obj.id;
			}
			else
			{
				$('ul.section').each(function()
				{
					// need an exclusion rule here too
					if (jQuery.inArray(this.id, options.exclude) == -1)
					{
						$('#index ul#' + this.id + ' li span.section_title').next().hide(options.speed);
					}
				});
				
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
	$.fn.indexpand.init(this);
};
})(jQuery);