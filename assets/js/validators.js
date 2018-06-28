(function ($) {
  Drupal.behaviors.fossee_stats = {
    attach: function (context, settings) {

      if (typeof Drupal.settings.fossee_stats.numvidlink !== 'undefined')
      {
      	var numvidlink = Drupal.settings.fossee_stats.numvidlink;
    		for(let i = 0; i < numvidlink; i++)
    		{
          let videoid = "";
          if(i!=numvidlink-1)
      		 videoid = "#edit-videolink-fieldset-"+i.toString()+"-videolink-link--"+(numvidlink-i).toString();
          else
           videoid = "#edit-videolink-fieldset-"+i.toString()+"-videolink-link";

          $(videoid).change(
      	 	function(){
      	 				let link = $(this).val();
                console.log(i.toString());
      	 				if( link.indexOf("http://") && link.indexOf("https://") && link.length)
      	 					{ $("#validate_url_"+i.toString()).css("visibility", "visible"); $('#edit-submit').prop('disabled', true);}
      	 				else
      	 					{ $("#validate_url_"+i.toString()).css("visibility", "hidden");  }
      	 			   }
     	    );
          if(i!=numvidlink-1)
          {
            let link = $(videoid).val();
            if( link.indexOf("http://") && link.indexOf("https://") && link.length)
               { $("#validate_url_"+i.toString()).css("visibility", "visible"); $('#edit-submit').prop('disabled', true);}
            else
                { $("#validate_url_"+i.toString()).css("visibility", "hidden"); }
          }
    		}
      }

       $("#edit-event-link").change(
       		function(){
       					var link = $(this).val();
       					if( link.indexOf("http://") && link.indexOf("https://") && link.length)
       						{$("#validate_url").css("visibility", "visible");  $('#edit-submit').prop('disabled', true);}
       					else
                  { $("#validate_url").css("visibility", "hidden"); }
       				}
       );

       $("#edit-end-date-datepicker-popup-0").change(
       		function() {
       					if( $(this).val() < $("#edit-start-date-datepicker-popup-0").val())
       						{ $("#validate_date").css("visibility","visible"); $('#edit-submit').prop('disabled', true);}
       					else
                { $("#validate_date").css("visibility","hidden"); }
       				 }
      	);

       $("#edit-start-date-datepicker-popup-0").change(
          function() {
                if( $("#edit-end-date-datepicker-popup-0").val()!="" && $(this).val() > $("#edit-end-date-datepicker-popup-0").val())
                 { $("#validate_date").css("visibility","visible");  $('#edit-submit').prop('disabled', true);}
                else
                  { $("#validate_date").css("visibility","hidden");}
               }
        );

       $('#edit-start-date-timepicker-popup-1').change(
       		function() {
       			if($("#edit-end-date-datepicker-popup-0").val()==$("#edit-start-date-datepicker-popup-0").val())
       				if($(this).val()>$("#edit-end-date-timepicker-popup-1"))
       					{ $("#validate_time").css("visibility","visible"); $('#edit-submit').prop('disabled', true);}
       				else
       					{ $("#validate_time").css("visibility","hidden"); }
       		}

       	);

       $('#edit-end-date-timepicker-popup-1').change(
       		function() {
       			if($("#edit-end-date-datepicker-popup-0").val()==$("#edit-start-date-datepicker-popup-0").val())
       				if($(this).val()<$("#edit-start-date-timepicker-popup-1"))
       					{ $("#validate_time").css("visibility","visible");  $('#edit-submit').prop('disabled', true);}
       				else
       					{ $("#validate_time").css("visibility","hidden");}
       		}
       	);

      if(typeof Drupal.settings.fossee_stats.sortby !== undefined)
       	{ $("input[name='"+Drupal.settings.fossee_stats.sortby+"']").css("background", "lightgray"); }

    }
  };

}(jQuery));


function deletePrompt(id){
	if(prompt("Are you sure?")) window.location = "events/delete/"+ id;
}
