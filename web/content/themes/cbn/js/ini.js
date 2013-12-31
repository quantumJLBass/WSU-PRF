var pullman = new google.maps.LatLng(46.73191920826778,-117.15296745300293);
var ib = [];
var ibh = [];
var markerLog = [];
var shapes = [];

var ibHover = false;
jQuery.noConflict(); //:-\
jQuery(document).ready(function($) {

jQuery('.AffiliateChoice').on("change",function(){
	if(jQuery(this).val()==0){
		jQuery('.gradinfo').show();
	}else{
		jQuery('.gradinfo').hide();
	}
});



function prep(){
	jQuery(' [placeholder] ').defaultValue();
	jQuery("a").each(function() {jQuery(this).attr("hideFocus", "true").css("outline", "none");});
}
function centerOnAddress(map,add,city,state,zip,contry,calllback){
	
	var address =   add + ' '
					+ city + ' '
					+ state + ' '
					+ zip + ' '
					+ ( contry==''?' USA':contry );
	geocoder = new google.maps.Geocoder();
	geocoder.geocode( { 'address': address }, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
				if (results && results[0]&& results[0].geometry && results[0].geometry.viewport) 
				map.fitBounds(results[0].geometry.viewport);
				if(typeof(calllback)!=="undefined"){ calllback(results[0].geometry.location.lat(),results[0].geometry.location.lng() ) }
			}else{
				//alert('ERROR:'+status);
			}
		}else{
			//alert('ERROR:'+status);
		}
	});
}

function iniMap(url,callback){	
	var json = jQuery.parseJSON(jQuery('#mapJson').text());
	var map_op = {'zoom':15, "center":pullman };
	map_op = jQuery.extend(map_op,{"mapTypeControl":false,"panControl":false});
	jQuery('#front_cbn_map').gmap(map_op).bind('init', function() { 
		var map = jQuery('#front_cbn_map').gmap("get","map");
		jQuery.each(json.markers,function(i,marker){
			
			
			var boxText = document.createElement("div");
			boxText.style.cssText = "border: 1px solid rgb(102, 102, 102); background: none repeat scroll 0% 0% rgb(226, 226, 226); padding: 2px; display: inline-block; font-size: 10px !important; font-weight: normal !important;";
			boxText.innerHTML = "<h3 style='font-weight: normal !important; padding: 0px; margin: 0px;'>"+marker.title+"</h3>";
			var myHoverOptions = {
				alignBottom:true,
				 content: boxText//boxText
				,disableAutoPan: false
				,pixelOffset: new google.maps.Size(15,-15)
				,zIndex: 999
				,boxStyle: {
					minWidth: "250px"
				 }
				,infoBoxClearance: new google.maps.Size(1, 1)
				,isHidden: false
				,pane: "floatPane"
				,boxClass:"hoverbox"
				,enableEventPropagation: false
				,disableAutoPan:true
				,onOpen:function(){}
				
			};
			ib[i] = marker.id;
			ibh[i] = new InfoBox(myHoverOptions,function(){});
			jQuery('#front_cbn_map').gmap('addMarker', jQuery.extend({ 
				'position': new google.maps.LatLng(marker.position.latitude, marker.position.longitude),
				'z-index':1,
				'bounds':true,
				'icon':'../Content/img/biz_map_icon.png'
			},{}),function(ops,marker){
				markerLog[i]=marker;
			})
			.click(function(){
				jQuery('#data_display').html(jQuery('.businesscontainer[rel='+ib[i]+']').find('.maininfo').html());
				})
			.mouseover(function(event){
				jQuery.each(ibh, function(i) {ibh[i].close();});
				jQuery('.infoBox').hover( 
					function() { ibHover =  true; }, 
					function() { ibHover =  false;  } 
				); 
				if(ibHover!=true)ibh[i].open(jQuery('#front_cbn_map').gmap('get','map'), markerLog[i]);
			})
			.mouseout(function(event){jQuery.each(ibh, function(i) {ibh[i].close();});});
		});
		$('#front_cbn_map').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers'), {
		  maxZoom: null,
          gridSize: 60,
          styles: [{
					url: '../Content/img/m1.png',
					height: 52,
					width: 53,
					anchor: [20, 0],
					textColor: '#ffffff',
					fontWeight:"bold",
					textSize: 10
				  }, {
					url: '../Content/img/m2.png',
					height: 55,
					width: 56,
					anchor: [20, 0],
					textColor: '#c2c2c2',
					fontWeight:"bold",
					textSize: 11
				  }, {
					url: '../Content/img/m3.png',
					height: 65,
					width: 66,
					anchor: [25, 0],
					textColor: '#d2d2d2',
					fontWeight:"bold",
					textSize: 12
				  }]
			
			}));

		if(jQuery('#front_cbn_map.byState').length>0 || jQuery('#front_cbn_map.byCountry').length>0){
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': jQuery('#front_cbn_map').attr('rel')}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
						if (results && results[0]&& results[0].geometry && results[0].geometry.viewport) 
						map.fitBounds(results[0].geometry.viewport);
					}
				}
			});
		}
		
	});
	return jQuery('#front_cbn_map');
}


function iniSingleMap(obj,callback){
	var mapObj = obj.find('.map')	
	var json = jQuery.parseJSON(obj.find('.mapJson').text());
	var map_op = {'zoom':12};
	map_op = jQuery.extend(map_op,{"mapTypeControl":false,"panControl":false});
	mapObj.gmap(map_op).bind('init', function() { 
		var map = mapObj.gmap("get","map");
		jQuery.each(json.markers,function(i,marker){
			
			
			var boxText = document.createElement("div");
			boxText.style.cssText = "border: 1px solid rgb(102, 102, 102); background: none repeat scroll 0% 0% rgb(226, 226, 226); padding: 2px; display: inline-block; font-size: 10px !important; font-weight: normal !important;";
			boxText.innerHTML = "<h3 style='font-weight: normal !important; padding: 0px; margin: 0px;'>"+marker.title+"</h3>";
			var myHoverOptions = {
				alignBottom:true,
				 content: boxText//boxText
				,disableAutoPan: false
				,pixelOffset: new google.maps.Size(15,-15)
				,zIndex: 999
				,boxStyle: {
					minWidth: "250px"
				 }
				,infoBoxClearance: new google.maps.Size(1, 1)
				,isHidden: false
				,pane: "floatPane"
				,boxClass:"hoverbox"
				,enableEventPropagation: false
				,disableAutoPan:true
				,onOpen:function(){}
				
			};
			ibh[i] = new InfoBox(myHoverOptions,function(){});
			mapObj.gmap('addMarker', jQuery.extend({ 
				'position': new google.maps.LatLng(marker.position.latitude, marker.position.longitude),
				'z-index':1,
				'bounds':true,
				'icon':'../Content/img/biz_map_icon.png'
			},{}),function(ops,marker){
				markerLog[i]=marker;
				mapObj.gmap("setOptions",{'zoom':13});
			}).mouseover(function(event){
				jQuery.each(ibh, function(i) {ibh[i].close();});
				jQuery('.infoBox').hover( 
					function() { ibHover =  true; }, 
					function() { ibHover =  false;  } 
				); 
				if(ibHover!=true)ibh[i].open(mapObj.gmap('get','map'), markerLog[i]);
			})
			.mouseout(function(event){jQuery.each(ibh, function(i) {ibh[i].close();});});
		});
		/*
		if(jQuery('#front_cbn_map.byState').length>0 || jQuery('#front_cbn_map.byCountry').length>0){
			geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': jQuery('#front_cbn_map').attr('rel')}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
						if (results && results[0]&& results[0].geometry && results[0].geometry.viewport) 
						map.fitBounds(results[0].geometry.viewport);
					}
				}
			});
		}
		*/
	});
}




	if(jQuery('#tabs').length>0){
		jQuery( "#tabs" ).tabs({
			activate: function( event, ui ) {
				if(ui.newPanel.attr('id')=="tabs-1"){
					//map.gmap("refresh");
					var map = iniMap();
				}else{
						
				}
			}
		});

		jQuery.each(jQuery('.accordion'),function(){
			jQuery(this).accordion({collapsible: true,active: false, heightStyle: "content" ,
				activate: function(event, ui) { 
					if(ui.oldPanel.length)ui.oldPanel.find('.map').gmap('destroy');
					if(ui.newPanel.length)iniSingleMap(ui.newPanel);
				 }
			 });
		});
		
	jQuery('.more').on('click',function(e){
		e.stopPropagation();
		e.preventDefault();
		jQuery(jQuery(this).attr("href")).toggle("showOrHide",function(){
					if(!jQuery(this).is(':visible'))jQuery(this).find('.map').gmap('destroy');
					if(jQuery(this).is(':visible'))iniSingleMap(jQuery(this));
			
			});
	});
		
		
		
		
		/*jQuery('.accordion').accordion({ header: '.biz',collapsible: true,active: false });*/
	}
	
	if(jQuery('#cbn_map').length){
	
		var lat = jQuery('#lat').val();
		var lng = jQuery('#lng').val();
		var business_City = '';
		var business_state = '';
		var business_Zip = '';
		var business_country_CountryAbbr = '';
		
		
		
		
		
		jQuery('#cbn_map').gmap({
			'center': (typeof(lat)==='undefined' || lat=='')? pullman : new google.maps.LatLng(lat,lng),
			'zoom':15,
			'zoomControl': false,
			'mapTypeControl': {  panControl: true,  mapTypeControl: true, overviewMapControl: true},
			'panControlOptions': {'position':google.maps.ControlPosition.LEFT_BOTTOM},
			'streetViewControl': false 
		}).bind('init', function () {
			
			function makeMapChange(){
				business_Address1 = jQuery('#business_Address1').val();
				business_City = jQuery('#business_City').val();
				business_state = jQuery('#business_state').val();
				business_Zip = jQuery('#business_Zip').val();
				business_country_CountryAbbr = jQuery('#business_country_CountryAbbr').val();
				
				var map = jQuery('#cbn_map').gmap("get","map");
				centerOnAddress(map,business_Address1,business_City,business_state,business_Zip,business_country_CountryAbbr,function(lat,lng){
					jQuery('#cbn_map').gmap("setOptions",{position:new google.maps.LatLng(lat,lng)},markerLog[0]);
					jQuery('#lat').val(lat);
					jQuery('#lng').val(lng);
				});
			}

			jQuery("input[name='business.Address1']").on("change",function(){makeMapChange()});
			jQuery("input[name='business.Address1']").on("blur",function(){makeMapChange()});
			jQuery("input[name='business.Address1']").on("mouseup",function(){makeMapChange()});
			jQuery("input[name='business.Address1']").on("keyup",function(){makeMapChange()});
			
			jQuery("input[name='business.City']").on("change",function(){makeMapChange()});
			jQuery("input[name='business.City']").on("blur",function(){makeMapChange()});
			jQuery("input[name='business.City']").on("mouseup",function(){makeMapChange()});
			jQuery("input[name='business.City']").on("keyup",function(){makeMapChange()});
			
			jQuery("input[name='business.Zip']").on("change",function(){makeMapChange()});
			jQuery("input[name='business.Zip']").on("blur",function(){makeMapChange()});
			jQuery("input[name='business.Zip']").on("mouseup",function(){makeMapChange()});
			jQuery("input[name='business.Zip']").on("keyup",function(){makeMapChange()});
			
			jQuery("select[name='business.state']").on("change",function(){makeMapChange()});
			jQuery("select[name='business.state']").on("blur",function(){makeMapChange()});
			jQuery("select[name='business.state']").on("mouseup",function(){makeMapChange()});
			jQuery("select[name='business.state']").on("keyup",function(){makeMapChange()});
			
			
			jQuery("select[name='business.country.CountryAbbr']").on("change",function(){makeMapChange()});
			jQuery("select[name='business.country.CountryAbbr']").on("blur",function(){makeMapChange()});
			jQuery("select[name='business.country.CountryAbbr']").on("mouseup",function(){makeMapChange()});
			jQuery("select[name='business.country.CountryAbbr']").on("keyup",function(){makeMapChange()});
			

			jQuery('#cbn_map').gmap('addMarker', jQuery.extend({ 
				'position': (typeof(lat)==='undefined' || lat=='')?pullman:new google.maps.LatLng(lat,lng),
				'icon':'../Content/img/biz_map_icon.png'
			},{'draggable':true}),function(markerOptions, marker){
				markerLog[0]=marker;

			}).click(function() {

			}).dragend(function(e) {
				var placePos = this.getPosition();
				var lat = placePos.lat();
				var lng = placePos.lng();
				jQuery('#lat').val(lat);
				jQuery('#lng').val(lng);
			});
			
			if( (jQuery('#lat').val() == "" || jQuery('#lng').val() == "") && jQuery("input[name='business.Zip']").val() !='' ){
				makeMapChange();
			}
			
			
			
		});
	}
		
		
		
		

		
		prep();
});

function disableFields(selected){
	if(selected){
		jQuery('#gradinfo').hide();
	}else{
		jQuery('#gradinfo').show();
	}
}


jQuery(document).ready(function(){
	
	
	
	
	
	/*
	jQuery('#cbn-logo').delay(900).animate({
		width:"326px",
		left: "-113px"
	},1500,"jswing",function(){
		
	});*/
	jQuery('[href="#more"]').on('click',function(e){
		e.stopPropagation();
		e.preventDefault();
			
		if(jQuery(".expoArea.active").length){
			jQuery('#more').css("display","none");
			}
			
		jQuery('#more').toggle("showOrHide");
		if(jQuery('.expoArea').is(jQuery(".active"))){
			jQuery('a[href="#more"]').html(" Read More ....");
			jQuery('.expoArea').removeClass("active");
		}else{
			jQuery('a[href="#more"]').html(" &laquo;Less");
			jQuery('.expoArea').addClass("active");
		}
	});
});