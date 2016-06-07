function delbg(filename)
{
	$.post('?a='+action, { id : ide, del_bg_img : 'true', name : filename }, function(html) 
	{
		$('div#back-img').html(html);
	});
}

function edit_title()
{
	title = $('span.inplace1').html();
	
	//$('h3 span.sec-title').hide();
	
	var input = "<div id='temp'>";
	input += "<input type='text' value=\"" + URLencode(title) + "\" style='width: 300px;' /><br />";
	input += " <input type='button' value='Save' onclick=\"edit_title_save(); return false;\" />";
	input += " <input type='button' value='Cancel' onclick=\"edit_title_close(); return false;\" />";
	input += "</div>";
	
	$('span.inplace1').replaceWith(input);
}

function edit_title_save()
{
	var title = $('h3 input').val();
	var type = $('input#htitletype').val();
	var subid = (type == 'subsection') ? $('input#hsubsection_id').val() : '0';
	
	if (title == '')
	{
		alert('Can not be empty.');
		title = $('input#htitle').val();
	}
	else
	{
		// save it up
		$.post('?a='+action, { upd_jxs : 'true', x : 'title', v : encodeURIComponent(title), id : ide, t : type, sub : subid }, 
			function(html) {
				updating(html);
		});
		
		// update hidden
		$('input#htitle').val(title);
	}
	
	$('#temp').remove();
	//$('h3 span.sec-title').show();
	$('h3').html("<span class='inplace1' onclick=\"edit_title(); return false;\" title='Edit'>" + title + "</span>");
}

function edit_title_close()
{
	$('#temp').remove();
	//$('h3 span.sec-title').show();
	$('h3').html("<span class='inplace1' onclick=\"edit_title(); return false;\" title='Edit'>" + $('input#htitle').val() + "</span>");
}

function edit_sec_title()
{
	title = $('span.inplace1').html();
	
	//$('h3 span.sec-title').hide();
	
	var input = "<div id='temp'>";
	input += "<input type='text' value=\"" + URLencode(title) + "\" style='width: 300px;' /><br />";
	input += " <input type='button' value='Save' onclick=\"edit_sec_title_save(); return false;\" />";
	input += " <input type='button' value='Cancel' onclick=\"edit_sec_title_close(); return false;\" />";
	input += "</div>";
	
	$('span.inplace1').replaceWith(input);
}

function edit_sec_title_save()
{
	var title = $('h3 input').val();
	var type = $('input#htitletype').val();
	
	if (title == '')
	{
		alert('Can not be empty.'); return false;
		title = $('input#htitle').val();
		//var type = $('input#htitletype').val();
	}
	else
	{
		var section = $('input#hsection_id').val();

		// save it up
		$.post('?a='+action, { upd_jxs : 'true', x : 'sectitle', v : encodeURIComponent(title), id : ide, s : section, t : type }, 
			function(html) {
				updating(html);
		});
		
		// update hidden
		$('input#htitle').val(title);
	}
	
	$('#temp').remove();
	//$('h3 span.sec-title').show();
	$('h3').html("<span class='inplace1' onclick=\"edit_sec_title(); return false;\" title='Edit'>" + title + "</span>");
}

function edit_sec_title_close()
{
	$('#temp').remove();
	//$('h3 span.sec-title').show();
	$('h3').html("<span class='inplace1' onclick=\"edit_sec_title(); return false;\" title='Edit'>" + $('input#htitle').val() + "</span>");
}

function URLencode(sStr) 
{
    return sStr.replace(/\"/g,'&quot;').replace(/\'/g, '&#039;');
}

function edit_link()
{
	link = $('span.inplace2').html();
	
	var input = "<div id='templink'>";
	input += "<input type='text' value=\"" + link + "\" style='width: 600px; font-size: 18px;' /><br />";
	input += " <input type='button' value='Save' onclick=\"edit_link_save('save'); return false;\" />";
	input += " <input type='button' value='Cancel' onclick=\"edit_link_save('close'); return false;\" />";
	input += "</div>";
	
	$('span.inplace2').replaceWith(input);
}

function edit_link_save(type)
{
	if (type == 'close')
	{
		$('#templink').remove();
		$('h3#linkr').html("<span class='inplace2' onclick=\"edit_link(); return false;\">" + $('input#hlink').val() + "</span>");
		return false;
	}

	var title = $('h3#linkr input').val();
	
	if (title == '')
	{
		alert('Can not be empty.');
		title = $('input#hlink').val();
	}
	else
	{
		// save it up
		$.post('?a='+action, { upd_jxs : 'true', x : 'link', v : encodeURIComponent(title), id : ide }, 
			function(html) {
				updating(html);
		});
		
		// update hidden
		$('input#hlink').val(title);
	}
	
	$('#templink').remove();
	$('h3#linkr').html("<span class='inplace2' onclick=\"edit_link(); return false;\">" + title + "</span>");
}


function getOrder()
{
	var ord = toolOrder();
	
	$.post('?a='+action, { order : ord, upd_img_ord : 'true' }, 
		function(html) {
			updatingImgs(html);
	});
}

function extend_comments()
{
	var extend = $('#extend_date').val();
	var cdate = $('p#expire_date span').html();

	$.post('?a='+action, { upd_jxs : 'true', x : 'extend', v : extend, id : ide, date : cdate }, 
		function(html) {
			$('#expire_date').html(html);
			return false;
	});
}

function update_pwd()
{
	var pwd = $('#password').val();
	
	$.post('?a='+action, { upd_jxs : 'true', x : 'password', v : pwd, id : ide }, 
		function(html) {
			updating(html);
	});
}

function update_sec_pwd()
{
	var pwd = $('#password').val();
	var section = $('#hsecid').val();
	
	$.post('?a='+action, { upd_jxs : 'true', x : 'secpassword', v : pwd, id : ide, s : section }, 
		function(html) {
			updating(html);
	});
}


function updateImages()
{
	getExhibit();
}

function getExhibit()
{
	$('#img-container').load('?a='+action+'&q=jximg&id='+ide,
		function() {
			apply_sort();
	});
}

function updateImage(ida)
{
	var title = encodeURIComponent( $('input#media_title').val() );
	var caption = encodeURIComponent( $('input#media_caption').val() );
	
	$.post('?a='+action, { upd_jximg : 'true', v : title, x : caption, id : ida }, 
		function(html) {
			getExhibit();
	});
}

function previewText(ida)
{
	var text = encodeURIComponent( $('textarea#jxcontent').val() );
	
	$.post('?a='+action, { upd_jxtext : 'true', v : text, id : ida }, 
		function(html) {
			window.location = '?a='+action+'&q=prv&id='+ide;
	});
}


function updateText(ida)
{
	if (typeof tinymce == 'undefined')
	{
		var text = encodeURIComponent( $('textarea#jxcontent').val() );
	}
	else
	{
		// silly that it really needs 'name' instead of 'id'
		var text = tinyMCE.getInstanceById('content').getHTML();
	}
	
	$.post('?a='+action, { upd_jxtext : 'true', v : text, id : ida }, 
		function(html) {
			updating(html);
	});
}

// onunload
function save_text()
{
	var answer = confirm('Save text?');
	
	if (answer) {
		var text = encodeURIComponent( $('textarea#jxcontent').val() );
		$.post('?a='+action, { upd_jxtext : 'true', v : text, id : ide }, 
			function(html) {
				updating(html);
		});
	}
}

function deleteImage(ida, file)
{
	var answer = confirm('Are you sure?');
	
	if (answer) {
		$.post('?a='+action, { upd_jxdelimg : 'true', id : ide, f : file }, 
			function(html) {
				$('li#box' + ida).remove();
				updating(html);
		});
	}
}

function getImgPreview(ida)
{
	$('#img-container').load('?a='+action+'&q=view&id='+ida);
}

function getColor(color)
{
	$.post('?a='+action, { upd_jxs : 'true', v : color, x : 'color', id : ide }, 
		function(html) {
			parent.updating(html);
			return false;
	});
}

function editTitle()
{
	$('.sec-title').after('');
	$('.sec-title').css('width', '300px');
	$('.sec-title').after("<input type='text' style='width:100px;' maxlength='50' /><input type='submit' />");
}

function processing()
{
	var processing = $('a#processing img').attr('alt');

	$.post('?a='+action, { upd_jxs : 'true', v : processing, x : 'processing', id : ide }, 
		function(html) 
		{
			(processing == 1) ? $('a#processing img').attr({src: baseurl + '/ndxzstudio/asset/img/process-off.gif', alt: '0'}) : 
					$('a#processing img').attr({src: baseurl + '/ndxzstudio/asset/img/process-on.gif', alt: '1'});
			updating(html);
	});
}

function updatePresent()
{
	var format = $('select#ajx-present').val();

	$.post('?a='+action, { upd_jxs : 'true', v : format, x : 'present', id : ide }, 
		function(html) {
			//updatingImgs(html);
			//return false;
			// update page...trigger images fresh too
			window.location.href = window.location.href + '&update=1';
			//alert(html);
	});
}

// needed because some formats don't have all the sources
// and some have different defaults
function postUpdatePresent()
{
	var source = $('select#ajx-source').val();

	
	$.post('?a='+action, { upd_jxs : 'true', v : source, x : 'source', id : ide }, 
		function(html) {
			// update page...trigger images fresh too
			return false;
	});
}

function updateFolder()
{
	var folder = $('select#ajx-folder').val();

	$.post('?a='+action, { upd_jxs : 'true', v : folder, x : 'folder', id : ide }, 
		function(html) {
			//updatingImgs(html);
			//return false;
			// update page...trigger images fresh too
			//window.location.href = window.location.href;
			window.location.href = window.location.href + '&update=1';
	});
}

function updateSource()
{
	var source = $('select#ajx-source').val();

	$.post('?a='+action, { upd_jxs : 'true', v : source, x : 'source', id : ide }, 
		function(html) {
			// update page...trigger images fresh too
			window.location.href = window.location.href + '&update=1';
	});
}

function show_upload(input)
{
	(input == 0) ? $('#uploadings').show() : $('#uploadings').hide();
}

function update_gallery()
{
	var format = $('select#ajx-source').val();
	//alert(format);

	$.post('?a=system', { upd_jxs : 'true', v : format, x : 'gallery', id : ide }, 
		function(html) 
		{
			// return updated images
			parent.update_gallery_images();
			
			/*
			if (format == 0) 
			{
				parent.show_upload(0); 
			}
			else
			{
				//alert("???????");
				parent.show_upload(1);
			}
			*/

			setTimeout('pause()', 1000);
			return false;
	});
}


function sb()
{
	$.facebox.reset();
	//jQuery('a[rel*=facebox]').facebox();
	//Shadowbox.clearCache();
	//Shadowbox.setup();
	apply_sort();
}

function pause()
{
	parent.sb();
}

function update_gallery_images()
{ 
	$.post('?a=system&id=' + ide, { upd_jxs : 'true', x : 'gallery-imgs', id : ide }, 
		function(html) {
			$('#img-container').html(html);
			setTimeout('pause()', 1000);
			return false;
	});
}

function updateTemplate()
{
	var format = $('select#ajx-template').val();
	$.post('?a='+action, { upd_jxs : 'true', v : format, x : 'template', id : ide }, 
		function(html) {
			updating(html);
			return false;
	});
}

function updateYear()
{
	var format = $('select#ajx-year').val();
	$.post('?a='+action, { upd_jxs : 'true', v : format, x : 'year', id : ide }, 
		function(html) {
			updating(html);
			return false;
	});
}

function updateBreak()
{
	var format = $('select#ajx-break').val();
	
	$.post('?a='+action, { upd_jxs : 'true', v : format, x : 'break', id : ide }, 
		function(html) {
			updatingImgs(html);
			return false;
	});
}

function resize_images(check, post)
{
	$.post('?a=system&id='+ide, { upd_jxs : 'true', x : 'resize', sz : check, p : post }, 
		function(html) {
			updatingImgs(html);
			return false;
	});
}

// also need
/*
option_select_post
option_slider_post
*/

// this is specifically for list options
jQuery.fn.option_list_post = function(check, type)
{
	this.click(function()
	{
		var poster = this.parentNode.id;
		var checkk = this.title;
		
		// need to clarify these better
		$.post('?a=system', { upd_jxs_opt : 'true', id : ide,  v : checkk, x : check, t : type }, 
			function(html) 
			{	
				parent.updating(html);
		});
		
		// need to make a new 'active' element
		$('#' + check + ' li').removeClass('active');
		$(this).addClass('active');
		
		return false;
	});
}

jQuery.fn.tabpost = function()
{
	this.click(function()
	{
		var poster = this.parentNode.id;
		var check = this.title;
		var section = $('input#hsecid').val();
		
		$.post('?a='+action, { upd_jxs : 'true', v : check, x : poster, id : ide, s : section }, 
			function(html) {
				//alert(html);
				if ((poster == 'ajx-images') || (poster == 'ajx-thumbs') || (poster == 'ajx-place'))
				{
					updatingImgs(html);
					if ((poster == 'ajx-images') || (poster == 'ajx-thumbs')) parent.resize_images(check, poster);
					if (poster == 'ajx-place') parent.layout_controls(check);
				}
				else
				{
					parent.updating(html);
				}
		});

		$('#' + this.parentNode.id + ' li').each(function()
		{
			$(this).tabpost_compare(check, this.title);
		});
		
		return false;
	});
}

jQuery.fn.tabpost_compare = function(first, second)
{
	(first == second) ? $(this).addClass('active') : $(this).removeClass();
}

function layout_controls(id)
{
	(id == 1) ? $('#layout-b').insertBefore('#layout-a') : $('#layout-a').insertBefore('#layout-b');
}

function flickr_controls(id)
{
	(id != 1) ? $('#img-sizes').show() : $('#img-sizes').hide();
	(id != 1) ? $('#iframe').show() : $('#iframe').hide();
}

function test_callback(content)
{
	//alert(content);
}

function ndxz_comments(ide, type)
{
	// get the textare if updating
	var text = (type == 'post') ? encodeURIComponent( $('textarea#comment-area-' + ide).val() ) : '';
	
	$.post('?a='+action, { upd_jxs : 'true', y : type, x : 'comment', id : ide, t : text }, 
		function(html) {
			$('#posted-' + ide).html(html);
			return false;
	});
}

function ndxz_delete(ide)
{
	var answer = confirm('Are you sure?');
	
	if (answer) 
	{
		$.post('?a='+action, { upd_jxs : 'true', x : 'c_delete', id : ide }, 
			function(html) {
				$('#comment' + ide).remove();
				return false;
		});
	}
}

function updating(html)
{
	// need to check the amount of the scrollbar and place accordingly in view
	//var moved = document.getElementById('all');
	//moved = moved.scrollTop;
	//alert(moved);
	
	var tmp = "<div id='updating' class='notify'><div id='updating-inner' class='corners'>" + html + "</div></div>";
	
	$('body').prepend(tmp);
	setTimeout(fader, 1000);
}

function updatingImgs(html)
{
	$('p#imgshold').append(html);
	setTimeout(fader, 1000);
}

function updateColor()
{
	// get color
	var color = ($('#colorBox').val() == '') ? 'ffffff' : $('#colorBox').val();
	
	// no hashes allowed
	color = color.replace('#', '');
	
	$.post('?a='+action, { upd_jxs : 'true', v : color, x : 'color', id : ide }, 
		function(html) {
			parent.updating(html);
			// update bg color box
			$('span#plugID').css('background', '#' + color);

			// update bg color text description
			$('span#colorTest2').html('#' + color);
			return false;
	});
}

// add this to the page directly later
var selectd = new Array();
var deleted = new Array();

function make_queue(tmpid, image)
{
	if (jQuery.inArray(image, selectd) == -1)
	{
		// add it
		selectd.push(image);
		highlighter(tmpid, true);
		deleted.push(tmpid);
	}
	else
	{
		// remove it
		selectd.splice(selectd.indexOf(image), 1);
		highlighter(tmpid, false);
		deleted.splice(selectd.indexOf(image), 1);
	}
}

function select_all()
{	
	// each
	$('#show_flickrs div a').each(function (i) 
	{
		// get the id
		var tmpid = this.id;
		tmpid = tmpid.replace('t', '');
		
		// get the image
		var image = $('div#l' + tmpid + ' a img').attr('alt');

		selectd.push(image);
		highlighter(tmpid, true);
		deleted.push(tmpid);
	});
}

function highlighter(tmpid, state)
{
	if (state == true) { 
		$('div#l' + tmpid + ' a').css({'backgroundColor': '#ccc'});
	} else { 
		$('div#l' + tmpid + ' a').css({'backgroundColor': ''});
	}
}

var last_image;
var no;

function execute_selection()
{
	var tmp = jQuery.unique(selectd);
	
	// can we get the last image from the array?
	no = tmp.length;
	
	var xyz = 0;
	
	$.each(tmp, function(index, value) 
	{
		$.post('?a=system', { upd_jxs : 'true', v : value, x : 'doqueue', id : ide }, 
			function(html) {
				// we should delete out the image or something
		});
	});
	
	$.each(deleted, function(index, value) 
	{
		//$('div#l' + value).remove();
		$('div#l' + value + ' div').css({'opacity': '0.5'});
		$('div#l' + value + ' div a').removeAttr('onclick');
	});
	
	return false;
}

function iamtesting()
{
	//alert('what?');
	//parent.update_gallery_images();
}

function reset_selectd()
{
	selectd = new Array();
}

function flickr_queue(image)
{
	//alert('Clicked: ' + image);

	$.post('?a=system', { upd_jxs : 'true', v : image, x : 'doqueue', id : ide }, 
		function(html) {
			// we should delete out the image or something
			check_uploaded(image);
			return false;
	});
}


function update_flickr()
{
	var format = $('select#ajx-flickr').val();
	reset_selectd();

	$.post('?a=system', { upd_jxs : 'true', v : format, x : 'get_flickrs', id : ide }, 
		function(html) {
			// return updated images
			//parent.update_flickr_images();
			$('#show_flickrs').html(html);
			//updating(html);
			return false;
	});
}


// tmp
function update_abstract(abst, ab_var, set)
{
	$.post('?a=system', { abstract : 'true', ab : abst, v : ab_var, o : action, i : ide, s : set }, 
		function(html) {
			//alert(html);
			//$('#s-' + things).html(html);
	});
		
	return false;
}

function create_exhibit(state)
{
	var title = $('input#title').val();
	var section = $('select#section_id').val();
	var year = (state == 'exhibit') ? $('select#ajx-year').val() : '2011';
	var link = (state == 'exhibit') ? '' : $('input#link').val();
	
	if ((title == '') || (section == '') || (year == ''))
	{
		alert('Can not be empty.');
		return false;
	}
	else
	{
		// save it up
		$.post('?a='+action, { upd_jxs : 'true', x : 'create', st : state, l : link, t : title, s : section, y : year }, 
			function(html) {
				//updating(html);
				// reload via the parent page
				//alert(html);
				window.parent.location.href = html;
		});
	}
}