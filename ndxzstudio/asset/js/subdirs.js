function add_subdir(secid, flag)
{
	var title = $('input#sub_title').val();
	var dir = $('input#sub_dir').val();
	
	// clear the fields
	$('input#sub_title').val('');
	$('input#sub_dir').val('');
	
	if ((title != '') && (dir != ''))
	{
		$.post('?a=system', { upd_jxs : 'true', v : 'add', x : 'subdir', id : secid, t : title, d : dir, f : flag }, 
			function(html) 
			{
				$('#thesubsections').html(html);
				apply_sort();
				return false;
		});
	}
	else
	{
		
	}
}

function delete_subdir(secid, node)
{
	var answer = confirm('Are you sure?');
	
	if (answer) 
	{
		$.post('?a=system', { upd_jxs : 'true', v : 'del', x : 'subdir', id : secid, n : node }, 
			function(html) 
			{
				$('#thesubsections').html(html);
				apply_sort();
				return false;
		});
	}
}

var tmp;

function cancel_subdir(node)
{
	$('li#subdir_node_' + node).html(tmp);
}

function edit_subdir(secid, node)
{
	var olddir = $('li#subdir_node_' + node + ' span.subdir').html();
	tmp = $('li#subdir_node_' + node).html();
	
	var html = "";
	html += "<label>Title</label>";
	html += "<input type='text' id='subtitle' value=\"" + $('li#subdir_node_' + node + ' span.subtitle').html() + "\" />";
	html += "<label>Folder</label>";
	html += "<input type='text' id='subdir' value=\"" + $('li#subdir_node_' + node + ' span.subdir').html() + "\" />";
	html += "<input type='hidden' id='subid' value='" + node + "' />";
	html += "<input type='hidden' id='olddir' value='" + olddir + "' />";
	html += "<div class='buttons'>";
	html += "<button type='button' class='general_submit' onclick=\"update_subdir(" + secid + ", " + node + "); return false;\">Update</button>";
	html += "<button type='button' class='general_delete' onclick=\"cancel_subdir(" + node + "); return false;\">Cancel</button>";
	html += "</div>";

	$('li#subdir_node_' + node).html(html);
	return false;
}

function update_subdir(secid, node)
{
	var title = $('li#subdir_node_' + node + ' input#subtitle').val();
	var dir = $('li#subdir_node_' + node + ' input#subdir').val();
	var olddir = $('li#subdir_node_' + node + ' input#olddir').val();

	$.post('?a=system', { upd_jxs : 'true', v : 'update', x : 'subdir', sid : secid, id : node, t : title, d : dir, old : olddir }, 
		function(html) 
		{
			$('#thesubsections li#subdir_node_' + node).html(html);
			apply_sort();
			return false;
	});
}


// for sorting the parts
function update_sort() 
{
	var i = 0;
	var up = new Array();
	
	$('#subdirs li div').each(function()
	{
		up[i] = $(this).attr('id');		
		i++;
	});
	
	// need to check when up is empty...
	up = up.join('.');

	$.post('?a=system', { upd_jxs : 'true', x : 'subdir', v : 'order', order : up, id : ide }, 
		function(html) {
			//alert(html);
	});
	
	return false;
}

//		handle: 'span.drag-title',

function apply_sort()
{
	$('#subdirs').sortable({   
		axis: 'y', 
		placeholder: 'dragging',
		opacity: 0.9,
		handle: 'span.handle',
		forcePlaceholderSize: 'true',
		stop: function(){ update_sort(); } 
	});
}

$(document).ready(function()
{
	apply_sort();
});