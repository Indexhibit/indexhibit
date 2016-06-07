(function($){
  $.fn.pageSlide = function(options) {
    
    var settings = $.extend({
		    width:          "215px", // Accepts fixed widths
		    duration:       "normal", // Accepts standard jQuery effects speeds (i.e. fast, normal or milliseconds)
		    direction:      "left", // default direction is left.
		    modal:          false, // if true, the only way to close the pageslide is to define an explicit close class.
 			locale: 		"#menu", 
		    start:          function(){}, // event trigger that fires at the start of every open and close.
		    stop:           function(){}, // event trigger that fires at the end of every open and close.
		    complete:       function(){}, // event trigger that fires once an open or close has completed.
		    _identifier: $(this)
		}, options);
		
		// these are the minimum css requirements for the pageslide elements introduced in this plugin.
		
		var pageslide_slide_wrap_css = {
		  position: 'fixed',
      width: '0',
      top: '0',
      height: '100%',
      zIndex:'999'
		};
		
		var pageslide_body_wrap_css = {
		  position: 'relative',
		  zIndex: '0'
		};
		
		var pageslide_blanket_css = { 
	    position: 'absolute',
	    top: '0px',
	    left: '0px',
	    height: '100%',
	    width: '100%', 
	    opacity: '0.0',
	    backgroundColor: 'black',
	    zIndex: '1',
	    display: 'none'
	  };
		
		function _initialize(anchor) {
      
      // Create and prepare elements for pageSlide
      
      if ($("#pageslide-body-wrap, #pageslide-content, #pageslide-slide-wrap").size() == 0) {
        
        var psBodyWrap = document.createElement("div");
        $(psBodyWrap).css(pageslide_body_wrap_css);
        $(psBodyWrap).attr("id","pageslide-body-wrap").width( $("body").width() );
        $("body").contents().wrapAll( psBodyWrap );
  	    
        var psSlideContent = document.createElement("div");
        $(psSlideContent).attr("id","pageslide-content").width( settings.width );

		// load the content here?
		//var data = $(settings.locale).html();
        //$("#pageslide-content").html(data);

        var psSlideWrap = document.createElement("div");
        $(psSlideWrap).css(pageslide_slide_wrap_css);
        $(psSlideWrap).attr("id","pageslide-slide-wrap").append( psSlideContent );
        $("body").append( psSlideWrap );
  	    
      }
      
      // introduce the blanket if modal option is set to true.
      if ($("#pageslide-blanket").size() == 0 && settings.modal == true) {
        var psSlideBlanket = document.createElement("div");
        $(psSlideBlanket).css(pageslide_blanket_css);
        $(psSlideBlanket).attr("id","pageslide-blanket");
        $("body").append( psSlideBlanket );
  	    $("#pageslide-blanket").click(function(){ return false; });
      }
          	    
	    // Callback events for window resizing
	    $(window).resize(function(){
        $("#pageslide-body-wrap").width( $("body").width() );
      });
	  };
	  
		function _openSlide(elm) {
		  if($("#pageslide-slide-wrap").width() != 0) return false;
		  _showBlanket();
		  settings.start();
		  // decide on a direction
		  if (settings.direction == "right") {
		    direction = {right:"-"+settings.width};
		    $("#pageslide-slide-wrap").css({left:0});
        _overflowFixAdd();
		  } 
		  else {
		    direction = {left:"-"+settings.width};
		    $("#pageslide-slide-wrap").css({right:0});
		  }
    	$("#pageslide-slide-wrap").animate({width: settings.width}, settings.duration);
		  $("#pageslide-body-wrap").animate(direction, settings.duration, function() {
		    settings.stop();
		
		// ???????????????????
	      //$.ajax({
  		      //type: "GET",
  		      //url: $(elm).attr("href"),
  		      //success: function(data){
	///alert('hhere?');

				var data = $(settings.locale).html();
				$("#pageslide-content").html(data).queue(function(){

  		        //$("#pageslide-content").html('a').queue(function(){
  		            $(this).dequeue();
  		            
  		            // restore working order to all anchors
  		            $("#pageslide-slide-wrap a").unbind('click').click(function(elm){
  		              document.location.href = elm.target.href;
  		            });
  		            
  		            // add hook for a close button
  		            $(this).find('.pageslide-close').unbind('click').click(function(elm){
  		              _closeSlide(elm);
  		              $(this).find('pageslide-close').unbind('click');
  		            });

  		            settings.complete();
  		          });
  		      //}
  		    //});
		  });
		};
		
		function _closeSlide(event) {
		  if ($(event)[0].button != 2 && $("#pageslide-slide-wrap").css('width') != "0px") { // if not right click.
		    _hideBlanket();
  		  settings.start();
  		  direction = ($("#pageslide-slide-wrap").css("left") != "0px") ? {left: "0"} : {right: "0"};
  		  $("#pageslide-body-wrap").animate(direction, settings.duration);
  	    $("#pageslide-slide-wrap").animate({width: "0"}, settings.duration, function() {
  	      $("#pageslide-content").empty();
  	      // clear bug
  	      $('#pageslide-body-wrap, #pageslide-slide-wrap').css('left','');
  	      $('#pageslide-body-wrap, #pageslide-slide-wrap').css('right','');
  	      _overflowFixRemove();
          settings.stop();
          settings.complete();
        });
      }
		};
		
		// this is used to activate the modal blanket, if the modal setting is defined as true.
		function _showBlanket() {
	    if(settings.modal == true) {
	      $("#pageslide-blanket").toggle().animate({opacity:'0.8'}, 'fast','linear');
	    }
	  };
	  
	  // this is used to deactivate the modal blanket, if the modal setting is defined as true.
	  function _hideBlanket(){
	    if (settings.modal == true) {
  	    $("#pageslide-blanket").animate({opacity:'0.0'}, 'fast','linear',function(){
  	      $(this).toggle();
  	    });
	    }
	  };
	  
	  // fixes an annoying horizontal scrollbar.
	  function _overflowFixAdd(){($.browser.msie) ? $("body, html").css({overflowX:'hidden'}) : $("body").css({overflowX:'hidden'});}
	  function _overflowFixRemove(){($.browser.msie) ? $("body, html").css({overflowX:''}) : $("body").css({overflowX:''});}
		
    // Initalize pageslide, if it hasn't already been done.
    _initialize(this);
    return this.each(function(){
      $(this).unbind("click").bind("click", function(){
    	  _openSlide(this);
    	  $("#pageslide-slide-wrap").click(function(){ return false; });
    	  if (settings.modal != true) {
  	      $(document).unbind('click').click(function(evt) { _closeSlide(evt); return false });
  	    }
    	  return false;
    	});	
    });
    
  };
})(jQuery);