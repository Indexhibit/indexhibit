function update_sort() 
{
	var i = 0;
	var up = new Array();
	
	// it would be nice to use sortable/serialize here
	
	$('#boxes li').each(function()
	{
		up[i] = $(this).attr('id');		
		i++;
	});
	
	// need to check when up is empty...
	up = up.join('.');
	
	$.post('?a='+action, { order : up, upd_img_ord : 'true' }, 
		function(html) {
			updating(html);
			//updatingImgs(html);
	});
	
	//alert('here');
	
	return false;
}

function make_new(ide)
{
	var get = ($('span#activity-' + ide).hasClass('undraftnew')) ? 1 : 0;

	$.post('?a=exhibits', { upd_jxs : 'true', x : 'activity', v : get, id : ide }, 
		function(html) {
			if (html == 1)
			{
				// it seems to be a little buggy this part...
				$('span#activity-' + ide).removeClass('undraftnew');
				$('span#activity-' + ide).addClass('undraft');
			}
			else
			{
				$('span#activity-' + ide).removeClass('undraft');
				$('span#activity-' + ide).addClass('undraftnew');
			}
	});
}

function toggle_opts(n)
{
	$('#under' + n).toggle();
	
	/*
	$('#under' + n).toggle(
		function() { $(this).css("color", "blue"); },
		function() { $(this).css("color", "red"); }
		//function() { $('span#under' + n).hide(); },
		//function() { $('span#under' + n).show(); }
	);
	*/
}

function section_toggle(n)
{
	$('#sort' + n + ' li.sortableitem').toggle();
	
	($('#sort' + n + ' li.sortableitem').css('display') == 'none') ? 
		$('#sort' + n + ' li.group span#s' + n + ' a').css('color', 'red') : 
		$('#sort' + n + ' a').css('color', '');
		
	///////////////////////////// ???
	$('#sort' + n + ' li.notsortableitem').toggle();
	
	($('#sort' + n + ' li.notsortableitem').css('display') == 'none') ? 
		$('#s' + n + ' a').css('color', 'red') : 
		$('#s' + n + ' a').css('color', '');
}

function section_chron_toggle(n)
{
	$('#sort' + n + ' li.sortableitem').toggle();
	
	($('#sort' + n + ' li.sortableitem').css('display') == 'none') ? 
		$('#opener' + n + ' a').css('color', 'red') : 
		$('#opener' + n + ' a').css('color', '');
		
	///////////////////////////// ???
	$('#sort' + n + ' li.notsortableitem').toggle();
	
	($('#sort' + n + ' li.notsortableitem').css('display') == 'none') ? 
		$('#s' + n + ' a').css('color', 'red') : 
		$('#s' + n + ' a').css('color', '');
}

function section_toggle_tag(n)
{
	$('#sort' + n + ' li.notsortableitem').toggle();
	
	($('#sort' + n + ' li.notsortableitem').css('display') == 'none') ? 
		$('#s' + n + ' a').css('color', 'red') : 
		$('#s' + n + ' a').css('color', '');
}

function high_toggle(n, y)
{
	var high = (n == 1) ? 
		"<a href='#' class='highlight-0' onclick=\"high_toggle(0, " + y + "); return false;\">&nbsp;&nbsp;</a>" : 
		"<a href='#' class='highlight-1' onclick=\"high_toggle(1, " + y + "); return false;\">&nbsp;&nbsp;</a>";
		
	n = (n == 1) ? 0 : 1;
		
	$.post('?a=exhibits', { upd_jxs : 'true', x : 'ajx-highlight', v : n, id : y }, 
		function(html) {
			$('span#h-' + y).html(high);
	});

	return false;
}


function apply_sort()
{
	$('#boxes').sortable({ 
		handle: 'span.drag-img',
		placeholder: 'dragging',
		opacity: 0.9,
		containment: '#img-container',
		stop: function(e, ui){ update_sort(); } 
	});
	
	jQuery('a[rel*=facebox]').unbind('click');
	jQuery('a[rel*=facebox]').facebox();
}

function apply_sort_index()
{
	$('ul').sortable({ stop: function(){ update_sort(); } });
}

// sorting index
function test_index_sort() 
{	
	var i = 0;
	var up = new Array();
	
	$('li.sortableitem').each(function()
	{
		if ($(this).attr('id') != undefined) 
		{
			var check = this.parentNode.id + '=' + $(this).attr('id');
			if (check != '') up[i] = check; // why doesn't this work?
		}
		i++;
	});

	up = up.join('.');
	
	$.post('?a=exhibits', { order : up, upd_ord : 'true' }, 
		function(html) {
			//alert(html);
			//$('#dhtml').html(html);
			updating(html);
	});
	
	return false;
}

// sorting index
function update_index_sort() 
{	
	var i = 0;
	var up = new Array();
	
	// we need a function to delete the spacer holder divs
	// this deletes our fake sorts so they don't interfere
	$('.temp').remove();
	
	$('li.sortableitem').each(function()
	{
		if ($(this).css('visibility') == 'hidden') $(this).css({'visibility' : 'visible' });
			
		if ($(this).hasClass('ui-sortable-helper'))
		{
			// ignore it - jquery technical limitation
		}
		else
		{
			// if it's the helper div we don't want it recorded here
			var check = this.parentNode.id + '=' + $(this).attr('id');
			if (check != '') up[i] = check;
			i++;
		}
	});

	up = up.join('.');
	
	//return false;
	
	$.post('?a=exhibits', { order : up, upd_ord : 'true' }, 
		function(html) {
			updating(html);
			//$('#dhtml').html(html);
	});
	
	// add the fake sorts back
	fake_sorts();
	
	return false;
}

function fake_sorts()
{
	// hackery to deal with jquery bug
	// of not being able to drag to empty ul's
	var insert = "<li class='sortableitem temp' style='height: 2px; margin: 0; padding: 0; background: white; border-bottom: none;'><!-- blank --></li>";
	$('.sortable').append(insert);
}

function alert_parts(e, ui)
{
	var temp = $(ui).attr('id');
	//alert(temp);
}

function index_sort()
{
	$('div#mytest').sortable({ 
		items: "li.sortableitem", 
		containment: '#mytest',
		handle: 'span.drag-title',
		axis: "y",
		placeholder: 'dragging',
		forcePlaceholderSize: 'true',
		opacity: 0.5,
		//start: function(e, ui){ alert( $(this).attr('id') ); },
		stop: function(e, ui){ update_index_sort(); }  
	});
}