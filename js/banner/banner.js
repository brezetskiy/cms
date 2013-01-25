function insertBanner(banner_id, url, tag_attr, flash_vars) {
	var swfNode = "";
	if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) { 
		swfNode = '<embed '+ tag_attr +' type="application/x-shockwave-flash" src="'+ url + '" ';
		swfNode += ' id="BanSwf'+ banner_id +'" name="BanSwf'+ banner_id +'" ';
		swfNode += 'wmode="transparent" flashvars="'+ flash_vars +'"';
		swfNode += '/>';
	} else {
		swfNode = '<object '+tag_attr+' id="BanSfw'+ banner_id +'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
		swfNode += '<param name="movie" value="'+ url +'" />';		
		swfNode += '<param name="flashvars" value="'+ flash_vars +'" />';
		swfNode += '<param name="wmode" value="transparent" />';
		swfNode += "</object>";
	}
	document.write(swfNode);
}