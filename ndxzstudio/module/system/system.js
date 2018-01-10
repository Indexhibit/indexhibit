function updateCode(ida)
{
	var code = encodeURIComponent( $('textarea#code').val() );
	
	$.post('?a=system', { upd_jxcode : 'true', v : code, id : ida }, 
		function(html) {
			$('#location .col h2').append(html);
			$('#location .col h2 span').fadeOut('slow');
	});
}

function backupCode(ida)
{
	//alert(ida);

	$.post('?a=system', { upd_jxbackup : 'true', id : ida }, 
		function(html) {
			alert(html);
			//$('#location .col h2').append(html);
			//$('#location .col h2 span').fadeOut('slow');
	});
}

function update_flickr()
{
	var format = $('select#ajx-flickr').val();

	$.post('?a='+action, { upd_jxs : 'true', v : format, x : 'flickr', id : ide }, 
		function(html) {
			// return updated images
			parent.update_flickr_images();
			//updating(html);
			return false;
	});
}

function transmit(ide)
{
	$.post('?a=system', { sendlogin : 'true', id : ide }, 
		function(html) {
			alert(html);
	});
}

function file_add_single(ide, loop, file, folder)
{
	$.post('?a=system', { upd_jxs : 'true', v : file, x : 'filesingle', id : ide, f : folder }, 
		function(html) {
			if (html == 'yes')
			{
				$('li#file-' + loop).html(file).css('color', 'red');
				parent.updateImages();
				//parent.update_gallery_images();
			}
			else
			{
				alert(html);
			}
			return false;
	});
}

function file_add_all(ide, loop, file, folder)
{
	$.post('?a=system', { upd_jxs : 'true', v : file, x : 'filesall', id : ide, f : folder }, 
		function(html) {
			$('ul#thefiles').remove();
			parent.updateImages();
			//parent.update_gallery_images();
			return false;
	});
}

function update_location(url)
{
	window.location.href = baseurl + url;
}

function reset_stats()
{
	var answer = confirm('This can not be undone. Are you sure?');
	
	if (answer) 
	{
		$.post('?a=system', { upd_jxs : 'true', x : 'reset_stats' }, 
			function(html) {
				window.location.reload();
		});
	}
}