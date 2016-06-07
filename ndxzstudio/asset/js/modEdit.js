// super simple tools

// Start the subroutines.
var text_enter_url      = "Enter the complete URL for the hyperlink";
var text_enter_url_name = "Enter the title of the webpage";
var text_enter_email    = "Enter the email address";
var error_no_url        = "You must enter a URL";
var error_no_title      = "You must enter a title";
var error_no_email      = "You must enter an email address";


function contentWrite(NewCode) 
{
    document.mform.content.value+=NewCode;
    document.mform.content.focus();
    return;
}

function Modbold() 
{
	add = "<strong></strong>";
	contentWrite(add);
}

function Moditalic() 
{
	add = "<em></em>";
	contentWrite(add);
}

function Modunder() 
{
	add = "<u></u>";
	contentWrite(add);
}

function Modstrike() 
{
	add = "<s></s>";
	contentWrite(add);
}

function ModInsImg(enterIMG, enterIMGw, enterIMGh)
{
	var ToAdd = "<img src='" + enterIMG + "' width='" + enterIMGw + "' height='" + enterIMGh + "' />";
	document.mform.content.value+=ToAdd;
};

// the point of this one is that it is resized by the system
// we need the newest info
function ModInsGimg(enterIMG, enterIMGw, enterIMGh)
{
	var x = (enterIMGw == '') ? '' : ", '" + enterIMGw + "'";
	var y = (enterIMGh == '') ? '' : ", '" + enterIMGy + "'";
	var ToAdd = "<media:gimg '" + enterIMG + "'" + x + "" + y + " />";
	document.mform.content.value+=ToAdd;
};

// have to be really careful with the quotes, if any
function ModInsWhatever(snip)
{
	var ToAdd = snip;
	document.mform.content.value+=ToAdd;
};

function ModInsFile(enterFile, enterFileDesc, target)
{
	var target = (target == 1) ? " target='_new'" : '';
	var ToAdd = "<a href='" + enterFile + "'" + target + ">" + enterFileDesc + "</a>";
	document.mform.content.value+=ToAdd;
};

function ModInsMov(file, x, y)
{
	var ToAdd = "<media:mov '"+  file + "', " + x + ", " + y + " />";
	document.mform.content.value+=ToAdd;
};

function ModInsYoutube(file, x, y)
{
	var ToAdd = "<media:youtube '"+  file + "', " + x + ", " + y + " />";
	document.mform.content.value+=ToAdd;
};

function ModInsVimeo(file, x, y)
{
	var ToAdd = "<media:vimeo '"+  file + "', " + x + ", " + y + " />";
	document.mform.content.value+=ToAdd;
};

function ModInsMP3(file, desc)
{
	var temp = (desc == '') ? '' : ", '" + desc + "'";
	var ToAdd = "<media:mp3 '" + file + "'" + temp + " />";
	document.mform.content.value+=ToAdd;
};

function ModInsCode(code)
{
	// we need to get the code
	//var ToAdd = $('input#mod-code').val();
	var ToAdd = code;
	
	//if (ToAdd == null) { alert('Error'); return false; }
	
	parent.document.mform.content.value += ToAdd;
};

function ModInsFlv(file, x, y, thumb)
{
	// 400 x 300 is the default size
	var tthumb = (thumb != '') ? ", '" + thumb + "'" : '';
	
	var ToAdd = "<media:flv '" + file + "', " + x + ", " + y + "" + tthumb + " />";
	document.mform.content.value+=ToAdd;
};

function ModInsJAR(file, x, y)
{
	var ToAdd = "<!--<![code to display applets]-->";
	ToAdd = ToAdd + "<!--[if !IE]> -->";
	ToAdd = ToAdd + "<object classid='java:"+file+".class' type='application/x-java-applet' archive='"+file+"' width='"+x+"' height='"+y+"' standby='Loading Processing software...' >";
	ToAdd = ToAdd + "<param name='archive' value='"+file+"' />";
	ToAdd = ToAdd + "<param name='mayscript' value='true' />";
	ToAdd = ToAdd + "<param name='scriptable' value='true' />";
	//ToAdd = ToAdd + "<param name='image' value='loading.gif' />";
	ToAdd = ToAdd + "<param name='boxmessage' value='Loading Processing software...' />";
	ToAdd = ToAdd + "<param name='boxbgcolor' value='#FFFFFF' />";
	ToAdd = ToAdd + "<param name='test_string' value='outer' />";
	ToAdd = ToAdd + "<!--<![endif]-->";
	ToAdd = ToAdd + "<object classid='clsid:8AD9C840-044E-11D1-B3E9-00805F499D93' codebase='http://java.sun.com/update/1.4.2/jinstall-1_4_2_12-windows-i586.cab' width='"+x+"' height='"+y+"' standby='Loading Processing software...' >";
	ToAdd = ToAdd + "<param name='code' value='"+file+"' />";
	ToAdd = ToAdd + "<param name='archive' value='"+file+"' />";
	ToAdd = ToAdd + "<param name='mayscript' value='true' />";
	ToAdd = ToAdd + "<param name='scriptable' value='true' />";
	//ToAdd = ToAdd + "<param name='image' value='loading.gif' />";
	ToAdd = ToAdd + "<param name='boxmessage' value='Loading Processing software...' />";
	ToAdd = ToAdd + "<param name='boxbgcolor' value='#FFFFFF' />";
	ToAdd = ToAdd + "<param name='test_string' value='inner' />";
	ToAdd = ToAdd + "<p><strong>This browser does not have a Java Plug-in.<br />";
	ToAdd = ToAdd + "<a href='http://java.sun.com/products/plugin/downloads/index.html' title='Download Java Plug-in'>Get the latest Java Plug-in here.</a>";
	ToAdd = ToAdd + "</strong></p>";
	ToAdd = ToAdd + "</object>";
	ToAdd = ToAdd + "<!--[if !IE]> -->";
	ToAdd = ToAdd + "</object>";
	ToAdd = ToAdd + "<!--<![endif]-->";
	ToAdd = ToAdd + "<!--<![code to display applets]-->";
	
	window.opener.document.mform.content.value+=ToAdd;
};

function ModInsLink(enterLink, enterLinkTitle)
{
	if (document.mformpop.selectType.value == '1') 
	{
	    var ToAdd = "<a href='" + enterLink + "'>" + enterLinkTitle + "</a>"; 
	};
	
	if (document.mformpop.selectType.value == '2') 
	{
	    var ToAdd = "<a href='mailto:" + enterLink + "'>" + enterLinkTitle + "</a>";
	};
	
 	document.mform.content.value+=ToAdd;
};

function ModSysLink(sysLink)
{
	var ToAdd = sysLink; 
	document.mform.content.value+=ToAdd;
};

function ModInsExtImg()
{
	var inp = $('input#img_ext').val();
	var w = ($('input#img_w').val() != null) ? $('input#img_w').val() : 100;
	var h = ($('input#img_h').val() != null) ? $('input#img_h').val() : 100;
	
	if (inp == null) { alert('Error'); return false; }
	
	var ToAdd = "<img src='" + inp + "' width='" + w + "' height='" + h + "' alt='image' />";
	parent.document.mform.content.value+=ToAdd;
}

// woops, where did this come from?
function getQueryVariable(url, variable) 
{
	var http = url;
	http = http.split("?")
	var query = http[1]; 
  	var vars = query.split("&"); 
  	for (var i=0;i<vars.length;i++) { 
    	var pair = vars[i].split("="); 
    	if (pair[0] == variable) { 
      		return pair[1]; 
    	} 
  	} 

  	alert('Query Variable ' + variable + ' not found'); 
}
/*
function ModInsYoutube()
{
	var inp = $('input#youtube').val();
	
	if (inp == null) { alert('Error'); return false; }
	
	inp = getQueryVariable(inp, 'v')
	
	var ToAdd = "<media:youtube '" + inp + "' />";
	parent.document.mform.content.value+=ToAdd;
}

function ModInsVimeo()
{
	var inp = $('input#vimeo').val();
	
	if (inp == null) { alert('Error'); return false; }
	
	var http = inp;
	http = http.split("com/")
	inp = http[1];
	
	var ToAdd = "<media:vimeo '" + inp + "' />";
	parent.document.mform.content.value+=ToAdd;
}
*/

function ModInsFlash(file, x, y)
{
	var ToAdd = "<object type='application/x-shockwave-flash' data='" + file + "' width='" + x + "' height='" + y + "'>";
	ToAdd = ToAdd + "<param name='movie' value='" + file + "' />";
	ToAdd = ToAdd + "<div style='width: " + x + "px; height: " + y + "px;'>";
	ToAdd = ToAdd + "<a href=\'http://www.adobe.com/go/gntray_dl_getflashplayer\'>";
	ToAdd = ToAdd + "Get Flash player to view this content";
	ToAdd = ToAdd + "</a>";
	ToAdd = ToAdd + "</div>";
	ToAdd = ToAdd + "</object>";
	
	window.opener.document.mform.content.value+=ToAdd;
}

function switch_default()
{
	if (document.mformpop.selectType.value == '1')
	{
		$('input#enterLink').val('http://');
	}
	else
	{
		$('input#enterLink').val('mailto:');
	}
}