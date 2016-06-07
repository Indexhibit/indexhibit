function do_settings(things)
{
	var poster = things;
	var check = $('#s-' + things + ' a').attr('title');

	$.post('?a=system', { upd_jxs : 'true', v : check, x : poster }, 
		function(html) {
			$('#s-' + things).html(html);
	});
		
	return false;
}

function flickr_keys()
{
	var apikey = $('input#flickr_api_key').val();
	var userid = $('input#flickr_user_id').val();

	$.post('?a=system', { upd_jxs : 'true', v : '1', x : 'flickr_keys', a : apikey, b : userid }, 
		function(html) {
			alert('Updated?');
	});
		
	return false;
}

function update_sort() 
{
	var i = 0;
	var up = new Array();
	
	$('#sizes li span').each(function()
	{
		up[i] = $(this).attr('id');		
		i++;
	});
	
	// need to check when up is empty...
	up = up.join('.');

	$.post('?a=system', { upd_ord : 'true', order : up }, 
		function(html) {
			// nothing
	});
	
	return false;
}

//		handle: 'span.drag-title',

function apply_sort()
{
	$('#sizes').sortable({   
		axis: 'y', 
		placeholder: 'dragging',
		opacity: 0.9,
		handle: 'span.drag-title',
		forcePlaceholderSize: 'true',
		stop: function(){ update_sort(); } 
	});
}

function update_abstract(abst, ab_var)
{
	//var poster = things;
	//var check = $('#s-' + things + ' a').attr('title');

	$.post('?a=system', { abstract : 'true', ab : abst, v : ab_var, o : action, i : ide }, 
		function(html) {
			alert(html);
			//$('#s-' + things).html(html);
	});
		
	return false;
}