$(function(){

	
	
	/********************************
	Toggle Aside Menu
	********************************/
	
	$(document).on('click', '.menu', function(){

		
		var container = $('.content-area');
	var width = parseInt(container.outerWidth());
	if (!container.hasClass('hidden')) {
	   
		//container.css( {'margin-left': 0});
		container.css( {'padding-left': 75});
		container.addClass('hidden');
		//$(".menu").css({ 'left': 57 });
	}else {
		container.removeClass('hidden');
		//container.css( {'margin-left': 0});
		container.css({ 'padding-left': 219 });
		$('.menu').css('left', '200');
		
		}
$('aside.left-panel').toggleClass('collapsed');
	});
	/********************************
	Aside Navigation Menu
	********************************/

	$("aside.left-panel nav.navigation > ul > li:has(ul) > a").click(function(){
		
		if( $("aside.left-panel").hasClass('collapsed') == false || $(window).width() < 768 ){

		
		
		$("aside.left-panel nav.navigation > ul > li > ul").slideUp(300);
		$("aside.left-panel nav.navigation > ul > li").removeClass('active');
		$("aside.left-panel nav.navigation > ul > li").removeClass('open');
		
		if(!$(this).next().is(":visible"))
		{
			
			$(this).next().slideToggle(300,function(){ $("aside.left-panel:not(.collapsed)").getNiceScroll().resize(); });
			$(this).closest('li').addClass('active open');
		}
		
		return false;
		
		}
		
	});
	
	/********************************
	NanoScroll - fancy scroll bar
	********************************/
	if( $.isFunction($.fn.niceScroll) ){
	$(".nicescroll").niceScroll({
	
		cursorcolor: '#FFF',
		cursorborderradius : '0px'		
		
	});
	}
	

	if( $.isFunction($.fn.niceScroll) ){
	$("aside.left-panel:not(.collapsed)").niceScroll({
		cursorcolor: '#8e909a',
		cursorborder: '0px solid #fff',
		cursoropacitymax: '0.5',
		cursorborderradius : '0px'	
	});
	}
});





