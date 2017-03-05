<?php 

use yii\web\view;
use yii\helpers\Url;

?>

<div id="file_manager" class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      <h4 class="modal-title">File manager</h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-sm-5">
         <a href="<?php echo $parent; ?>" data-toggle="tooltip" title="Parent" id="button-parent" class="btn btn-default"><i class="glyphicon glyphicon-level-up"></i></a>
         <a href="<?php echo $refresh; ?>" data-toggle="tooltip" title="Refresh" id="button-refresh" class="btn btn-default"><i class="glyphicon glyphicon-refresh"></i></a>
          <button type="button" data-toggle="tooltip" title="Upload" id="button-upload" class="btn btn-primary"><i class="glyphicon glyphicon-upload"></i></button>
          <button type="button" data-toggle="tooltip" title="Create folder" id="button-folder" class="btn btn-default"><i class="glyphicon glyphicon-folder-open"></i></button>
          <button type="button" data-toggle="tooltip" title="Delete" id="button-delete" class="btn btn-danger"><i class="glyphicon glyphicon-trash"></i></button>
        </div>
        <div class="col-sm-7">
          <div class="input-group">
            <input type="text" name="search" value="<?php echo $filter_name; ?>" placeholder="Search" class="form-control">
            <span class="input-group-btn">
            <button type="button" data-toggle="tooltip" title="Search" id="button-search" class="btn btn-primary"><i class="glyphicon glyphicon-search"></i></button>
            </span></div>
        </div>
      </div>
      <hr />
      <?php foreach (array_chunk($images, 4) as $image) { ?>
      <div class="row">
        <?php foreach ($image as $image) { ?>
        <div class="col-sm-3 col-xs-6 text-center">
          <?php if ($image['type'] == 'directory') { ?>
          <div class="text-center"><a href="<?php echo $image['href']; ?>" class="directory" style="vertical-align: middle;"><i class="glyphicon glyphicon-folder-open glyphicon-5x"></i></a></div>
          <label>
            <input type="checkbox" name="path[]" value="<?php echo $image['path']; ?>" />
            <?php echo $image['name']; ?></label>
          <?php } ?>
          <?php if ($image['type'] == 'image') { ?>
          <a href="<?php echo $image['href']; ?>" class="thumbnail"><img src="<?php echo $image['thumb']; ?>" alt="<?php echo $image['name']; ?>" title="<?php echo $image['name']; ?>" /></a>
          <label>
            <input type="checkbox" name="path[]" value="<?php echo $image['path']; ?>" />
            <?php echo $image['name']; ?></label>
          <?php } ?>
        </div>
        <?php } ?>
      </div>
      <br />
      <?php } ?>
    </div>
    <div class="modal-footer"><?php echo $pagination; ?></div>
  </div>
</div>

<style>
.glyphicon-5x{font-size: 5em;}
</style>


<script>
  
var directory = '<?= $directory ?>';
var thumb = '<?= $thumb ?>';
var target = '<?= $target ?>';
var file_manager_url = '<?= Url::to(['file_manager/index']) ?>';
var file_manager_upload_url = '<?= Url::to(['file_manager/upload']) ?>';
var file_manager_folder_url = '<?= Url::to(['file_manager/folder']) ?>';

if ('<?= $target ?>') { 
  
  $('a.thumbnail').on('click', function(e) {

    if('<?= $thumb ?>'){
      $('#<?= $thumb ?>').find('img').attr('src', $(this).find('img').attr('src'));
    }
    
    $('#<?= $target ?>').val($(this).parent().find('input').val());

    $('#modal-image').modal('hide');

    e.preventDefault();   
  });
}


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

  $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="File_manager[images][]" value="" multiple="multiple" /><input name="_csrf" value="'+$('meta[name="csrf-token"]').attr("content")+'" /></form>');

  $('#form-upload input[name=\'File_manager[images][]\']').trigger('click');

  if (typeof timer != 'undefined') {
      clearInterval(timer);
  }

  timer = setInterval(function() {
    if ($('#form-upload input[name=\'File_manager[images][]\']').val() != '') {
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
          $('#button-upload i').replaceWith('<i class="glyphicon glyphicon-refresh glyphicon-spin"></i>');
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

</script>