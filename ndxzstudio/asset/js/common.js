function OpenWindow(mypage, myname, w, h, scroll)
{
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
	win = window.open(mypage, myname, winprops)
	if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
}


function editTab(obj)
{
if (document.getElementById) {
	var el = document.getElementById(obj);
	var ar = document.getElementById("tab").getElementsByTagName("div");
		if(el.style.display != "block"){
			for (var i=0; i<ar.length; i++){
				if (ar[i].className == "subTabs") {
				ar[i].style.display = "none";
				styleTab(ar[i].id,'off');
				}
			}
		}
	el.style.display = "block";
	styleTab(obj,'on');
	}
}

// need to style this from our stylesheets...hmmm...
function styleTab(id,state)
{
	tabStyle = document.getElementById("a"+id);
	if (state == 'on') {
		tabStyle.className = 'tabOn';
	} else {
		tabStyle.className = 'tabOff';
	}
}


function toggle(targetId)
{
	if (document.getElementById) {
		target = document.getElementById(targetId);
		if (target.style.display == "none") { 
			target.style.display = "";
		} else {
			target.style.display = "none"; 
		} 
	} 
}


function gohere(url) {
	window.location.href = url;
}


// uploading files stuff
var nextHiddenIndex = 1;

function AddFileInput()
{
	ylib_getObj("fileInput" + nextHiddenIndex).style.display = document.all ? "block" : "table-row";
	nextHiddenIndex++;
	if (nextHiddenIndex >= 10) ylib_getObj("attachMoreLink").style.display = "none";
}

function ylib_getObj(id,d)
{
	var i,x; 
	if (!d) d=  document; 
	if (!(x = d[id])&&d.all) x = d.all[id]; 
	for (i = 0;!x && i < d.forms.length; i++) x = d.forms[i][id];
	for (i = 0;!x && d.layers&& i < d.layers.length; i++) x = ylib_getObj(id,d.layers[i].document);
	if (!x && document.getElementById) x = document.getElementById(id); 
	return x;
}

// common jquery functions
function fader()
{ 
	//$('.notify').fadeOut('slow');
	$('.notify').fadeOut('slow');
}