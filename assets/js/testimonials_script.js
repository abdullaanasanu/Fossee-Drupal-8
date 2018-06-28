(function ($) {
$(document).ready(function(){


	$('#testimonials_front li').hide().eq(0).show();

	(function showNextTestimonial(){

		$('#testimonials_front li:visible').delay(12000).fadeOut('slow',function(){
			$(this).appendTo('#testimonials_front ul');
			if($(this).next().length > 0)
				{
				$(this).next().fadeIn('slow',function(){
					showNextTestimonial();
					});
				}
			else
				{
				$(this).siblings(':first').fadeIn('slow',function(){
					showNextTestimonial();
					});
				}
		});
	})();

});
});
