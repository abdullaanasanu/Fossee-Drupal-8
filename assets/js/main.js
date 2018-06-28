$(document).ready(function() {
    var basePath = Drupal.settings.basePath;
    var modPath = basePath + "jobs/";

    $shortlist = $(".shortlist");
    $shortlist.click(function (e) {
        $(".sync-msg").remove();
        $(this).after("<span class='sync-msg'>Saving...</span>");
        $.ajax({
            url: modPath + "ajax/shortlist/" + $(this).data("aid"),
            success: function() {
                $(".sync-msg").remove();
                console.log("success");
            }
        });
    });
    
    
   $(this).on('click','#d_app',function (e){ 
      if($shortlist.prop("checked") == false){
         alert("Please select atleast one resume for download");
         e.preventDefault();    
       }
         console.log('####');
    
    });
    
});
