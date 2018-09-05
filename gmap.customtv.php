<?php
if (IN_MANAGER_MODE != 'true') {
	die('<h1>Error:</h1><p>Please use the MODx content manager instead of accessing this file directly.</p>');
}
$site_url = MODX_SITE_URL;
$dirTv = str_replace("\\", "/", dirname(__FILE__)."/");
$dirTv = "/" . str_replace(MODX_BASE_PATH, "", $dirTv);
$includeGmap = <<<EOD
<link media="all" rel="stylesheet" href="{$dirTv}css/gmap.css" />
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAqObB5jDzWbLoN0_oDxajFw9IsCYrcAjc&libraries=places"></script>
<script type="text/javascript">
	(function(jq){
		jq(document).ready(function(){
			var infoWindow = new google.maps.InfoWindow({
				content: ""
			});
			jq("[data-tvgmap]").each(function(){
				var self = jq(this),
					data = self.data(),
					input = jq(data.tvgmap),
					rinput = jq(data.tvgmapr),
					izoom = jq(data.tvgmap + "zoom"),
					itext = jq(data.tvgmap + "textarea"),
					ititle = jq(data.tvgmap + "title"),
					icenter = jq(data.tvgmap + "center"),
					search = jq(data.tvgmap + "search"),
					value = input.val(),
					mapData = JSON.parse(value),
					map = new google.maps.Map(this, {
						center: mapData.center,
						zoom: mapData.zoom,
						mapTypeId: google.maps.MapTypeId.HYBRID,
						scrollwheel: false,
						streetViewControl: false,
						mapTypeControl: false,
						motionTrackingControl: true,
						rotateControl: true,
						scaleControl: true,
						mapTypeControlOptions: {
							style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
							mapTypeIds: [google.maps.MapTypeId.HYBRID]
						}
					}),
					marker = new google.maps.Marker({
						map: map,
						draggable: true,
						animation: google.maps.Animation.DROP,
						position: mapData.marker.position,
						title: mapData.marker.title || ""
					}),
					setInput = function(){
						try {
							var json = JSON.stringify(mapData);
							input.val(json).trigger('change');
						}catch(err){
							
						}
					},
					autocomplete = new google.maps.places.Autocomplete(search[0]);
				autocomplete.bindTo('bounds', map);
				autocomplete.addListener('place_changed', function() {
					infoWindow.close();
					var place = autocomplete.getPlace();
					if (!place.geometry) {
						return;
					}
					map.setCenter(place.geometry.location);
					marker.setPosition(place.geometry.location);
					mapData.center = mapData.marker.position = place.geometry.location;
					setInput();
				});
				input.on("input change", function(e){
					mapData = JSON.parse(this.value);
					var rjson = JSON.stringify(mapData.marker.position);
					rinput.val(rjson);
					icenter.val(JSON.stringify(mapData.center));
					itext.val(mapData.infowindow.content);
					izoom.val(mapData.zoom);
					marker.setOptions({
						title: mapData.marker.title || ""
					});
				});
				ititle.on("input change", function(e){
					infoWindow.close();
					mapData.marker.title = this.value;
					setInput();
				});
				rinput.val(JSON.stringify(mapData.marker.position));
				ititle.val(mapData.marker.title || "");
				izoom.val(mapData.zoom);
				icenter.val(JSON.stringify(mapData.center));
				itext.on('input change', function(){
					mapData.infowindow.content = jq(this).val();
					google.maps.event.trigger(marker,'click');
				}).val(mapData.infowindow.content);
				jq(document).on('blur', data.tvgmap + "textarea", function(e){
					console.log(jq(this).val());
					mapData.infowindow.content = jq(this).val();
					setInput();
				});
				marker.addListener('drag', function(e) {
					var lat = marker.getPosition().lat(),
						lng = marker.getPosition().lng();
					mapData.marker.position.lat = lat;
					mapData.marker.position.lng = lng;
					setInput();
				});
				marker.addListener('click', function(e) {
					if(jq.trim(mapData.infowindow.content).length){
						infoWindow.setOptions({
							content: mapData.infowindow.content,
							maxWidth: parseInt(self.width()) - 80,
						});
						var m = marker.get('map');
						infoWindow.open(m, marker);
						infoWindow.isOpen = true;
					}else{
						infoWindow.close();
						infoWindow.isOpen = false;
					}
				});
				map.addListener('drag', function(){
					mapData.center = map.getCenter();
					setInput();
				});
				map.addListener('click', function(){
					if(infoWindow){
						infoWindow.close();
					}
				});
				map.addListener('zoom_changed', function(e){
					mapData.zoom = map.getZoom();
					setInput();
				});
			});
		});
	}(jQuery));
</script>
EOD;
$default = '{"center":{"lat":53.203821794214086,"lng":50.10991940269878},"marker":{"position":{"lat":53.2038235530191,"lng":50.10992000933027},"title":""},"zoom":10,"infowindow":{"content":"<p>The <a href=\"http://evo.im/\" target=\"_blank\" rel=\"noopener\">EVO Community</a> provides a great starting point to learn all things Evolution CMS, or you can also <a href=\"http://evo.im/\">see some great learning resources</a> (books, tutorials, blogs and screencasts).</p>\n<p>Welcome to EVO!</p>"}}';
$value = empty($row['value']) ? $default : $row['value'];//htmlspecialchars(stripslashes())
$id = $row['id'];
$outputGmap = <<<EOD
<div class="gmaptv-block">
	<details>
		<div>
			<label><b>Center Map Position</b></label>
			<input type="text" id="tv{$id}center" readonly="readonly">
		</div>
		<div>
			<label><b>Zoom Map</b></label>
			<div>
				<input type="text" id="tv{$id}zoom" readonly="readonly"></textarea>
			</div>
		</div>
		<div>
			<label><b>Marker Position</b></label>
			<input type="text" id="tvr{$id}" readonly="readonly">
		</div>
		<div>
			<label><b>Marker title</b></label>
			<input type="text" id="tv{$id}title">
		</div>
		<div>
			<label><b>InfoWIndow Content</b></label>
			<div>
				<textarea id="tv{$id}textarea"></textarea>
			</div>
		</div>
	</details>
	<div>
		<label><b>Search Box</b></label>
		<div>
			<input id="tv{$id}search" type="text" />
		</div>
	</div>
	<div class="gmaptv-block--map">
		<div class="gmaptv-block--map__map" data-tvgmap="#tv{$id}" data-tvgmapr="#tvr{$id}"></div>
	</div>
	<div>
		<textarea style="display: none !important;" id="tv{$id}" name="tv{$id}" onchange="documentDirty=true;">{$value}</textarea>
	</div>
</div>
EOD;
echo $outputGmap;

if(!defined('GMAPTV')) {
	echo $includeGmap;
	define('GMAPTV', 1);
}