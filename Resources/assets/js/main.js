$(document).ready(function () {
	$('#setting_add').on('click', function(e){
		$('.setting_data').last().after($('<div>').append($('.setting_data').last().clone()).html());
	});

	$('#setting_remove').on('click', function(e){
		if($('.setting_data').length > 1)
		{
			$('.setting_data').last().remove();
		}
	});
});