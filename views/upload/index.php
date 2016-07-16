<?php 

use yii\helpers\Url;

?>

<div class="image_wrapper">
	<a href="" id="thumb-image0" data-toggle="image" class="img-thumbnail">
		<img src="http://localhost/opencart/image/cache/catalog/demo/canon_logo-100x100.jpg" alt="" title="" data-placeholder="http://localhost/opencart/image/cache/no_image-100x100.png">
	</a>
	<input type="hidden" name="product_image[0][image]" value="catalog/demo/canon_logo.jpg" id="input-image0">
</div>

<?php 

$this->registerJs("

	var file_manager_url = '". Url::to(['file_manager/index']) ."';

	$(document).delegate('a[data-toggle=\'image\']', 'click', function(e) {
		e.preventDefault();
	
		var element = this;
	
		$(element).popover({
			html: true,
			placement: 'right',
			trigger: 'manual',
			content: function() {
				return '<button type=\"button\" id=\"button-image\" class=\"btn btn-primary\"><i class=\"glyphicon glyphicon-pencil\"></i></button> <button type=\"button\" id=\"button-clear\" class=\"btn btn-danger\"><i class=\"glyphicon glyphicon-trash\"></i></button>';
			}
		});
		
		$(element).popover('toggle');		
	
		$('#button-image').on('click', function() {
			$('#modal-image').remove();
	
			$.ajax({
				url: file_manager_url + '&target=' + $(element).parent().find('input').attr('id') + '&thumb=' + $(element).attr('id'),
				dataType: 'html',
				beforeSend: function() {
					$('#button-image i').replaceWith('<i class=\"glyphicon glyphicon-refresh glyphicon-spin\"></i>');
					$('#button-image').prop('disabled', true);
				},
				complete: function() {
					$('#button-image i').replaceWith('<i class=\"glyphicon glyphicon-upload\"></i>');
					$('#button-image').prop('disabled', false);
				},
				success: function(html) {
					$('body').append('<div id=\"modal-image\" class=\"modal\">' + html + '</div>');
	
					$('#modal-image').modal('show');
				}
			});
	
			$(element).popover('hide');
		});
	
		$('#button-clear').on('click', function() {
			$(element).find('img').attr('src', $(element).find('img').attr('data-placeholder'));
			
			$(element).parent().find('input').attr('value', '');
	
			$(element).popover('hide');
		});
	});

");

