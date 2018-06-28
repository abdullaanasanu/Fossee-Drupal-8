(function ($) {
	$(document).ready(function()
	{

	    $( ".delete").bind("click", function () {
		var e=confirm('Are you sure?');
		if(e)
		{
		return true;
		}
		else
		{
		return false;
		}
	 });
	});
});
