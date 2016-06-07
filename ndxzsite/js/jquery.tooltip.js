//style-my-tootltips by malihu (http://manos.malihu.gr)
(function($){  
 $.fn.tooltips = function(options) {  
	var defaults = {  
		tip_follows_cursor: "on", 
		tip_delay_time: 1000
	};
	var options = $.extend(defaults, options);
	$("body").append("<div id='tooltip'></div>"); //create the tooltip container
	smtTip=$("#tooltip"); 
	smtTip.hide(); //hide it
    return this.each(function() {  
		function smtMouseMove(e){
			smtMouseCoordsX=e.pageX;
			smtMouseCoordsY=e.pageY;
			smtTipPosition();
		}
		function smtTipPosition(){
			var cursor_tip_margin_x=0; //horizontal space between the cursor and tooltip
			var cursor_tip_margin_y=24; //vertical space between the cursor and tooltip
			var leftOffset=smtMouseCoordsX+cursor_tip_margin_x+$(smtTip).outerWidth();
			var topOffset=smtMouseCoordsY+cursor_tip_margin_y+$(smtTip).outerHeight();
			if(leftOffset<=$(window).width()){
				smtTip.css("left",smtMouseCoordsX+cursor_tip_margin_x);
			} else {
				var thePosX=smtMouseCoordsX-(cursor_tip_margin_x)-$(smtTip).width();
				smtTip.css("left",thePosX);
			}
			if(topOffset<=$(window).height()){
				smtTip.css("top",smtMouseCoordsY+cursor_tip_margin_y);
			} else {
				var thePosY=smtMouseCoordsY-(cursor_tip_margin_y)-$(smtTip).height();
				smtTip.css("top",thePosY);
			}
		}
		$(this).hover(function(e) {  
			// mouseover
			var $this=$(this);
			$this.data("smtTitle",$this.attr("title")); //store title 
			var theTitle=$this.data("smtTitle");
			$this.attr("title",""); //remove title to prevent native tooltip showing
			smtTip.empty().append(theTitle).hide(); //set tooltip text and hide it
			smtTip_delay = setInterval(smtTip_fadeIn, options.tip_delay_time); //set tooltip delay
			if(options.tip_follows_cursor=="off"){
				smtMouseMove(e);
			} else {
				$(document).bind("mousemove", function(event){
					smtMouseMove(event); 
				});
			}
		}, function() {  
			// mouseout
			var $this=$(this);
			if(options.tip_follows_cursor!="off"){
				$(document).unbind("mousemove");
			}
			clearInterval(smtTip_delay);
			if(smtTip.is(":animated")){ 
				smtTip.hide();
			} else {
				smtTip.fadeTo("fast",0);
			}
			$this.attr("title",$this.data("smtTitle")); //add back title
		});
		function smtTip_fadeIn(){
			smtTip.fadeTo("fast",1,function(){clearInterval(smtTip_delay);});
		}
	});  
 };  
})(jQuery);