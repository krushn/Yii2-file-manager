
$('a.directory').on('click', function(e) {
	$('#modal-image').load(decodeURIComponent($(this).attr('href')));
	e.preventDefault();
});

$('.pagination a').on('click', function(e) {
	$('#modal-image').load(decodeURIComponent($(this).attr('href')));
	e.preventDefault();
});

$('#button-parent').on('click', function(e) {
	$('#modal-image').load(decodeURIComponent($(this).attr('href')));
	e.preventDefault();
});

$('#button-refresh').on('click', function(e) {
	$('#modal-image').load(decodeURIComponent($(this).attr('href')));
	e.preventDefault();
});

$('input[name=\'search\']').on('keydown', function(e) {
	if (e.which == 13) {
		$('#button-search').trigger('click');
	}
});

$('#button-search').on('click', function(e) {

	var url = file_manager_url + '&directory='+directory;
	
	var filter_name = $('input[name=\'search\']').val();

	if (filter_name) {
		url += '&filter_name=' + encodeURIComponent(filter_name);
	}

	if (thumb) {
		url += '&thumb=' + thumb;
	} 

	if (target) { 
		url += '&target=' + target;
	} 

	$('#modal-image').load(url);
});

$('#button-upload').on('click', function() {
	$('#form-upload').remove();

	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file[]" value="" multiple="multiple" /></form>');

	$('#form-upload input[name=\'file[]\']').trigger('click');

	if (typeof timer != 'undefined') {
    	clearInterval(timer);
	}

	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file[]\']').val() != '') {
			clearInterval(timer);

			$.ajax({
				url: file_manager_upload_url + '&directory=' + directory,
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$('#button-upload i').replaceWith('<i class="glyphicon glyphicon- glyphicon-spin"></i>');
					$('#button-upload').prop('disabled', true);
				},
				complete: function() {
					$('#button-upload i').replaceWith('<i class="glyphicon glyphicon-upload"></i>');
					$('#button-upload').prop('disabled', false);
				},
				success: function(json) {
					if (json['error']) {
						alert(json['error']);
					}

					if (json['success']) {
						alert(json['success']);

						$('#button-refresh').trigger('click');
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});

$(document).ready(function(){
	$('#button-folder').popover({
		html: true,
		placement: 'bottom',
		trigger: 'click',
		title: 'Create folder',
		content: function() {
			html  = '<div class="input-group">';
			html += '  <input type="text" name="folder" value="" placeholder="Folder name" class="form-control">';
			html += '  <span class="input-group-btn"><button type="button" title="Create folder" id="button-create" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i></button></span>';
			html += '</div>';

			return html;
		}
	});
});

$('#button-folder').on('shown.bs.popover', function() {
	$('#button-create').on('click', function() {
		$.ajax({
			url: file_manager_folder_url + '&directory='+directory,
			type: 'post',
			dataType: 'json',
			data: 'folder=' + encodeURIComponent($('input[name=\'folder\']').val()),
			beforeSend: function() {
				$('#button-create').prop('disabled', true);
			},
			complete: function() {
				$('#button-create').prop('disabled', false);
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['success']) {
					alert(json['success']);

					$('#button-refresh').trigger('click');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});
});

$('#file_manager #button-delete').on('click', function(e) {
	if (confirm('Are you sure?')) {
		$.ajax({
			url: 'index.php?r=file_manager/delete',
			type: 'post',
			dataType: 'json',
			data: $('input[name^=\'path\']:checked'),
			beforeSend: function() {
				$('#button-delete').prop('disabled', true);
			},
			complete: function() {
				$('#button-delete').prop('disabled', false);
			},
			success: function(json) {
				if (json['error']) {
					alert(json['error']);
				}

				if (json['success']) {
					alert(json['success']);

					$('#button-refresh').trigger('click');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
});