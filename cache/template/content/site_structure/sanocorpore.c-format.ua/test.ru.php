
 <link rel="stylesheet" href="/extras/jquery/css/chosen/style.css" />
  
  
<script src="/extras/jquery/jquery.chosen.min.js" type="text/javascript"></script>
<script type="text/javascript"> 
	$(document).ready(function() {
	  	$(".chzn-select").chosen({no_results_text: "Ничего не найдено", allow_single_deselect:true});  
	});
</script>
  
<form action="/action/cms/test/" method="POST"> 
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
	
	<select name="chosen_1" data-placeholder="Choose a Country..." class="chzn-select" style="width:350px;" tabindex="2">
		<option value=""></option> 
		<option value="United States">United States</option> 
		<option value="United Kingdom">United Kingdom</option> 
		
		<option value="Western Sahara">Western Sahara</option> 
		<option value="Yemen">Yemen</option> 
		<option value="Zambia">Zambia</option> 
		<option value="Zimbabwe">Zimbabwe</option>
	</select>
	 
	<select name="chosen_2[]" multiple data-placeholder="Your Favorite Type of Bear" style="width:350px;" class="chzn-select" tabindex="7">
		<option value=""></option>
		<option value="American Black Bear">American Black Bear</option>
		<option value="Asiatic Black Bear">Asiatic Black Bear</option>
		<option value="Brown Bear">Brown Bear</option> 
		<option value="Giant Panda">Giant Panda</option> 
		<option value="Sloth Bear">Sloth Bear</option> 
		<option value="Sun Bear">Sun Bear</option>
		<option value="Polar Bear">Polar Bear</option>
		<option value="Spectacled Bear">Spectacled Bear</option>
	</select>
        
	
	  <select data-placeholder="Your Favorite Football Team" style="width:350px;" class="chzn-select" multiple tabindex="6">
          <option value=""></option>
          
          <optgroup label="NFC EAST" style="border-width:6px">
            <option>Dallas Cowboys</option>
            <option>New York Giants</option>
        	<option>Philadelphia Eagles</option>
        	<option>Washington Redskins</option>
        	<option>Philadelphia Eagles</option>
        	
             	<optgroup label="TESTwer"  style="border-width:12px">>
	            	<option >gdsgasdg</option> 
	            	<option >gfsdfsadf</option>
	             </optgroup>
        	<option>Washington Redskins</option>
        	<option>Philadelphia Eagles</option>
        	<option>Washington Redskins</option>
          </optgroup>
         
        </select>
	<input type="submit" value="Chosen test"> 
</form>