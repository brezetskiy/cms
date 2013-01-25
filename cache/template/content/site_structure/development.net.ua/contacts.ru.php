<!--<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false&language=ru"></script>
<script type="text/javascript">

	var initialLocation;
	var browserSupportFlag = new Boolean();
	
	var map;
	var geocoder;
	var infowindow = new google.maps.InfoWindow();
	var marker;
	
	
	function init() {
		geocoder = new google.maps.Geocoder();
	
		var myOptions = {
			zoom: 16,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		var location = new google.maps.LatLng(50.446511, 30.434080999999992);
		
	    map.setCenter(location);  
		var marker = new google.maps.Marker({
	      	position: location,  
	      	map: map,
	      	title:'Украина, г. Киев, переулок Западный 3-Д, 1 этаж.'
	  	});
	  	
	  	var marker_content = $('#marker_content').html();
	  	var infowindow = new google.maps.InfoWindow({ 
	  		content: marker_content, 
	  		size: new google.maps.Size(30, 30)
		});
		
		infowindow.open(map, marker);  
	}  
  
	window.onload = init;
</script>
 
<div id="map_canvas" style="width:100%; min-height:500px; border:1px solid #777;"></div>

<div id="marker_content" style="display:none;">
	<img src="/design/ukraine/img/logo.png" border="0" style="width:120px;">
	<div style="margin-top:10px;">
		Украина, г. Киев, 
		<br/>переулок Западный 3-Д, 1 этаж.
		<br/>Время работы офиса: Пн-Пт, 10:00-18:00
	</div>
</div>
-->