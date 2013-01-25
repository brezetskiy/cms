
<script>
	$(document.body).click(function(e) {
		var id = e.target.id;
		var is_in = false;
		  
		$('#phone_block_container').find('*').each(function() { 
			if(id == this.id) is_in = true; 
		});
		
		if(id == 'phone_block_form') is_in = true;
		if(id == 'phone_block_form_link') is_in = true; 
		if(id == 'phone_block_form_img') is_in = true;
		 
		if(!is_in){
			$('#phone_block_container').hide();
			$('#phone_block_error').html('');
		}
	});
		
	jQuery(function($) {
		$('#phone_block_input_phone').mask('+38(999) 999-99-99'); 
	}); 
</script>

<style>
	table.phones a { text-decoration: none; }
	table.phones tr.phone td.entity { background-color:#f0f0f0; border:1px solid #7398AF; padding:5px; box-shadow:1px 1px 1px #FFFFFF inset; border-radius:15px; }
	
	table.confirm tr td { border-bottom:0px dotted #ccc !important; }  
	table.confirm tr td.title { color:#555 !important; }   
	table.confirm tr td a { font-size:11px; }
	 
	div.phone_block_container{ background-color:#D4DEEC; padding:5px; border:1px solid #7398AF; padding:3px; position:absolute;  margin:5px 0px;  display:none; width:330px; box-shadow:2px 2px 2px #bbb;  }
	
	div.phone_form{ background-color:#f3f3f3; border:1px solid #7398AF;  }
	div.phone_form div.hat { background-color:#B4C8D4; border-bottom:1px solid #7398AF;  height:18px;  padding:5px 10px; }
	
	div.phone_form input[type=text] { padding:5px; width:206px !important; color:#555; border:1px solid #ccc; }
	div.phone_form img { vertical-align:middle !important; }
	div.phone_form a.submit_phone_button {border: 1px solid #CCCCCC; margin: 0 0 5px 5px; padding: 3px 5px; text-decoration: none;}
	div.phone_form a.submit_phone_button:hover { background-color:#fff; }
	
	div.hr { border-bottom:1px dotted #7398AF; width:100%; margin:2px 0px; }
	
</style>

<table border="0" cellpadding="0" cellspacing="10"  class="form phones">		
	<tr>
		<td colspan="2">
			<a id="phone_block_form_link" href="javascript:void(0);" onclick="phone_error_clear(); $('#phone_block_container').toggle();">
				<img id="phone_block_form_img" src="/img/user/add.png" border="0" align="absmiddle" style="margin:2px 5px;">�������� �����
			</a>
	  
			<form onsubmit="phone_add(); return false;">
 				<div id="phone_block_container" class="phone_block_container">
					<div id="phone_block_form" class="phone_form">
						<div id="phone_block_form_hat" class="hat"> 
							<div id="phone_block_form_hat_left" style="float:left;"><img id="phone_block_form_hat_left_img" border="0" align="absmiddle" src="/img/user/mobile-phone.png"> <b id="phone_block_form_hat_left_title">������� ��� �����</b></div>
							<div id="phone_block_form_hat_right" style="float:right;"><a id="phone_block_form_hat_right_href" href="javascript:void(0);" onclick="$('#phone_block_container').hide();"><img id="phone_block_form_hat_right_img" src="/design/ukraine/img/cp/domain/cross.png" border="0" align="absmiddle" ></a></div>
							<div id="phone_block_form_hat_clear" style="clear:both;"></div>
						</div>
						
						<div id="phone_block_form_content" style="padding:10px;">
							<input type="text" id="phone_block_input_phone" value="" style="height: 10pt; font-size: 11px; width:200px;"> 
							
							<a id="phone_block_add_link" href="javascript:void(0);" onclick="phone_add();" class="submit_phone_button">
								<img id="phone_block_add_img" src="/img/user/add_green.png" border="0" align="absmiddle" title="��������"> ��������
							</a>
							
							<div id="phone_block_error" style="color:red; font-size:10px;"></div>
							<div id="phone_block_country_control" style="margin-top:5px;">
								<span id="phone_block_country_title" style="font-size:11px; color:#777; margin-top:5px;">���� ������:</span> 
								<a id="phone_block_country_ukraine" href="javascript:void(0);" onclick="phone_country('ukraine');" style="font-size:10px; color:#ff9d02;" class="country_mask_button">�������</a>, 
								<a id="phone_block_country_russia" href="javascript:void(0);" onclick="phone_country('russia');" style="font-size:10px;" class="country_mask_button">������</a>,
								<a id="phone_block_country_other" href="javascript:void(0);" onclick="phone_country('other');" style="font-size:10px;" class="country_mask_button">������</a>
							</div>
						</div>
					</div>
				</div> 
			</form>
		</td> 
	</tr> 
	 
	<?php if($this->global_vars['phones_count'] == 0): ?><tr><td colspan="2" style="color:grey; font-size:10px;">������ ����� ���������� ������� ����</td></tr><?php endif; ?>
	<?php if($this->global_vars['phones_count'] > 1 && !$this->global_vars['is_main_exists']): ?>
		<tr><td colspan="2" style="color:red; font-size:10px;">����������, ���������� ��� �������� ���������� �����</td></tr>
	<?php endif; ?>
	
	<?php
			reset($this->vars['/phones/'][$__key]);
			while(list($_phones_key,) = each($this->vars['/phones/'][$__key])):
			?>
	<tr class="phone">
		<td class="entity">
			<a href="javascript:void(0);" title="�������" onclick="phone_delete(<?php echo $this->vars['/phones/'][$__key][$_phones_key]['id']; ?>);"><img src="/img/user/delete.png" border="0" align="absmiddle" style="margin:2px 5px;"></a>
			<span style="<?php if($this->vars['/phones/'][$__key][$_phones_key]['is_confirmable'] && $this->vars['/phones/'][$__key][$_phones_key]['confirmed'] == 0): ?>color:red<?php endif; ?>"><?php echo $this->vars['/phones/'][$__key][$_phones_key]['phone_original']; ?></span>&nbsp;    
			<div style="float:right; margin-right:10px;"><label><input type="checkbox" <?php if($this->vars['/phones/'][$__key][$_phones_key]['is_main']): ?>disabled checked<?php else: ?>onclick="phone_set_main(<?php echo $this->vars['/phones/'][$__key][$_phones_key]['id']; ?>);"<?php endif; ?>> ��������</label></div>
		</td>
	</tr>
	<?php 
			endwhile;
			?>
</table>

