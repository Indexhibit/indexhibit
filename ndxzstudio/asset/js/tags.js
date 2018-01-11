function upd_tags(id, style)
{
	// change the active tag
	var tag = $('#tag' + id);
	(tag.attr('class') == 'inactive') ? toggle_class(tag, 'active') : toggle_class(tag, 'inactive');
	
	var tags = new Array();
	
	var i = 0;
	
	// get the active tags
	$('#tag-box span').each(function()
	{
		var atag = this.id;
		atag = atag.replace('tag', '');
		
		if ($('#tag' + atag).attr('class') == 'active') { tags.push(atag); }
	});
	
	// update database
	// check this on browsers
	tags = (tags == '') ? 0 : tags.join(',');
	
	var style = (style == 'img') ? 'img' : 'exh';

	$.post('?a=system&id=' + ide, { upd_jxs : 'true', v : tags, x : 'tags', id : ide, method : style, p : ide }, 
		function(html) {
			//alert(html);
			$('#tag-holder').html(html);
			//if (style == 'exh') parent.update_tag_box();
			return false;
	});
}

function update_tag_box(style)
{
	var style = (style == 'img') ? 'img' : 'exh';
	
	$.post('?a=' + action, { upd_jxs : 'true', x : 'gettags', id : ide, method : style }, 
		function(html) {
			$('#tag-holder').html(html);
			return false;
	});
}

function delete_tag(ida, page, style)
{
	var answer = confirm('Are you sure?');
	
	if (answer) 
	{
		var style = (style == 'img') ? 'img' : 'exh';

		$.post('?a=system&id=' + ide, { upd_jxs : 'true', x : 'deltag', id : ida, method : style, p : ide }, 
			function(html) {
				$('div#tag-holder').html(html);
		});

		$('#tag-edit').remove();
	}
	
	return false;
}

function editor_tag(ida, page, style)
{
	var tag = encodeURIComponent( $('input#tag-editor').val() );
	var g = 1;
	var style = (style == 'img') ? 'img' : 'exh';
	if (tag == '') { alert('Can not be blank.'); return false; }
	
	$.post('?a=system&id=' + ide, { upd_jxs : 'true', v : tag, x : 'uptag', id : ida, group : g, method : style, p : ide }, 
		function(html) {
			$('div#tag-holder').html(html);
			return false;
	});
	
	$('#tag-edit').remove();
}


function edit_tag(ida, style)
{
	// clear out the field first
	$('#tag-edit').remove();
	var style = (style == 'img') ? 'img' : 'exh';
	
	// get field
	$.post('?a=system&id=' + ide, { upd_jxs : 'true', x : 'edtag', id : ida, method : style }, 
		function(html) {
			$('#tag-holder').before(html);
	});
	
	return false;
}


function tagme(tag_id, file_id)
{
	$.post('?a=system&id=1', { upd_jxs : 'true', x : 'tagme', id : tag_id, fid : file_id }, 
		function(html) {
			// delete the thumb from display
			$('li#box' + file_id).remove();
	});
	
	return false;
}


function add_tags(style)
{
	var tag = encodeURIComponent( $('input#new_tag').val() );
	var g = 1
	if (tag == '') return false;
	var style = (style == 'img') ? 'img' : 'exh';
	
	$.post('?a=system&id=' + ide, { upd_jxtag : 'true', v : tag, x : 'addtag', id : ide, group : g, method : style }, 
		function(html) {
			//alert(html); return false;
			$('div#tag-holder').html(html);
			$('input#new_tag').val('');
			$('#tag-add').hide();
			return false;
	});
}

function add_master_tags(style)
{
	var tag = encodeURIComponent( $('input#new_tag').val() );
	var g = 1
	if (tag == '') return false;
	var style = (style == 'img') ? 'img' : 'exh';
	
	$.post('?a=system&id=' + ide, { upd_jxtag : 'true', v : tag, x : 'addmastertag', id : ide, group : g, method : style }, 
		function(html) {
			//alert(html); return false;
			$('div#master-tag-list').html(html);
			$('input#new_tag').val('');
			$('#tag-add').hide();
			
			// need to reload facebox
			jQuery('a[rel*=facebox]').facebox();
			return false;
	});
}

function reload_master_tags()
{
	$.post('?a=system&id=' + ide, { upd_jxtag : 'true', x : 'reload' }, 
		function(html) {
			$('div#master-tag-list').html(html);
			
			// need to reload facebox
			jQuery('a[rel*=facebox]').facebox();
	});
}

function toggle_class(clas, input)
{
	clas.removeClass();
	clas.addClass(input);
}