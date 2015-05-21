<script type="text/javascript">
	$(document).ready(function () {
		{{ $setting->mediaLibraryName }}.init(function(checkedValues)
		{	
			url        = '{{ url("admin/Installation/modulesettings/addfiles", $setting->module_key) }}';
			settingKey = '{{ $setting->key }}';
			$.ajax({
				url         : url,
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