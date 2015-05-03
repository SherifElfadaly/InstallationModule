<script type="text/javascript">
	$(document).ready(function () {
		{{ $module->mediaLibraryName }}.init(function(checkedValues)
		{	
			settingKey = '{{ $setting->key }}';
			$.ajax({
				url         : window.location,
				type        : 'GET',
				data        : {'ids': checkedValues, 'settingKey' : settingKey},
				success     : function(data)
				{
					location.reload();
				}
			});
		});
	});
</script>
