<?php if(!empty($this->vars['table_title'])): ?><H2><?php echo $this->vars['table_title']; ?></H2><?php endif; ?>

<?php if($this->vars['show_path'] == true): ?>
<?php
			reset($this->vars['/path/'][$__key]);
			while(list($_path_key,) = each($this->vars['/path/'][$__key])):
			?><a href="<?php echo $this->vars['/path/'][$__key][$_path_key]['url']; ?>"><?php echo $this->vars['/path/'][$__key][$_path_key]['name']; ?></a> <img src="/design/cms/img/ui/selector.gif" width="6" height="9" alt=""> <?php 
			endwhile;
			?> <?php echo $this->vars['path_current']; ?>
<?php endif; ?>


<script>
max_height = '<?php echo $this->global_vars['max_height']; ?>';
var swf_upload_<?php echo $this->vars['image_field']; ?>;
</script>

<script language="JavaScript" type="text/javascript" src="/extras/swfupload/swfupload.2.2.0.beta3.js"></script>
<script language="JavaScript" type="text/javascript" src="/js/gallery/coolgallery.js"></script>
<script language="JavaScript" type="text/javascript" src="/extras/swfupload/handlers_coolgallery.js"></script>
<script language="JavaScript" type="text/javascript" src="/extras/longtailvideo/swfobject.js"></script>
<script type='text/javascript' src='swfobject.js'></script>


<div id="mediaspace" class="longtailvideo">
	<a href="javascript:void(0);" onclick="$('#mediaspace').css({'display':'none'});">Закрыть</a>
	<div id="mediaspace_video"></div>
</div>


<span id="spanSWFUploadButton"></span>
<div id="gallery_upload_status"></div><br>
<input type="hidden" name="gallery_table_id" id="gallery_table_id" value="<?php echo $this->global_vars['table']['id']; ?>">


<div id="gallery_drag_delimiter"></div>
<div id="gallery_hover_layer" onmouseout="return galleryLayerOut()" onmousedown="return galleryDragStart(event)" onmouseup="return galleryDragStop()"></div>
<div id="gallery_drag_handler" onmouseup="return galleryDragStop()" onmousemove="return galleryDragMove(event)"></div>
<div id="gallery_drag_layer" onmouseup="return galleryDragStop()" onmousemove="return galleryDragMove(event)"></div>

<div class="gallery_container" id="gallery_container">
<?php
			reset($this->vars['/image/'][$__key]);
			while(list($_image_key,) = each($this->vars['/image/'][$__key])):
			?>
	<div style="height: <?php echo $this->global_vars['cell_height']; ?>px; width: <?php echo $this->global_vars['cell_width']; ?>px;" onmouseover="return galleryLayerOver(this, '<?php echo $this->global_vars['width']; ?>', '<?php echo $this->global_vars['height']; ?>', '<?php echo $this->vars['/image/'][$__key][$_image_key]['description']; ?>')" class="gallery_image_layer" id="il_<?php echo $this->vars['/image/'][$__key][$_image_key]['id']; ?>">
		<div class="gallery_image_layer_toolbar">
			<div style="float:left"><span style="cursor: default" ondblclick="GalleryEditPhotoDescription('<?php echo $this->vars['/image/'][$__key][$_image_key]['id']; ?>', <?php echo $this->global_vars['table']['id']; ?>, '<?php echo LANGUAGE_CURRENT; ?>', this.title); return false;" id="gallery_descr_<?php echo $this->global_vars['table']['id']; ?>_<?php echo $this->vars['/image/'][$__key][$_image_key]['id']; ?>" title="<?php echo $this->vars['/image/'][$__key][$_image_key]['description']; ?>"><?php echo $this->vars['/image/'][$__key][$_image_key]['description_show']; ?></span></div>
			<?php if(in_array($this->vars['/image/'][$__key][$_image_key]['extension'], array('flv', 'mp4', 'aac', 'mp3'))): ?>
				<a title="<?php echo $this->vars['/image/'][$__key][$_image_key]['description']; ?>" href="javascript:void(0);" onclick="show_video('mediaspace', '<?php echo $this->vars['/image/'][$__key][$_image_key]['url']; ?>');"><img border="0" src="/design/cms/img/icons/zoom.gif"></a>
			<?php else: ?>
				<a rel="lightbox-gallery" title="<?php echo $this->vars['/image/'][$__key][$_image_key]['description']; ?>" href="<?php echo $this->vars['/image/'][$__key][$_image_key]['url']; ?>"><img border="0" src="/design/cms/img/icons/zoom.gif"></a>
			<?php endif; ?>
			<a href="#" title="Редактировать" onclick="EditWindow('<?php echo $this->vars['/image/'][$__key][$_image_key]['id']; ?>', <?php echo $this->global_vars['table']['id']; ?>, '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '');return false;"><img border="0" src="/design/cms/img/icons/change.gif"></a>
			<a href="#" title="Удалить" onclick="gallery_swf_upload_delete('<?php echo $this->vars['/image/'][$__key][$_image_key]['id']; ?>','<?php echo $this->global_vars['table']['name']; ?>','photo', '<?php echo $this->vars['/image/'][$__key][$_image_key]['extension']; ?>'); return false;"><img border="0" src="/design/cms/img/icons/del.gif"></a>
		</div>
		<div rel="image_holder" class="image_holder" style="text-align: center; height: <?php echo CMS_THUMB_HEIGHT; ?>;">
			<img style="<?php echo $this->vars['/image/'][$__key][$_image_key]['thumb_style']; ?>" src="<?php echo $this->vars['/image/'][$__key][$_image_key]['thumb_url']; ?>">
		</div>
	</div>
<?php 
			endwhile;
			?>
</div>