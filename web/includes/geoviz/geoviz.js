function GeoViz()
{
	
	//var mxml = new OpenLayers.Format.XML();
	//var gml = new OpenLayers.Format.GML();
	var map,modify,vlayer,google_hybrid,flayer;
	var mapOptions, toolbarOptions;
	var drawControls;
	
	var defaultStyle, selectStyle, temporaryStyle;
	var defaultStyleMap;
	var lastBounds;
	var firstLoad;
	var drawMode = 'polygon';
	
	this.drawMode = drawMode;
	
	var mapDiv;
	this.mapDiv = mapDiv;
	
	this.toolbardiv;
	
	this.orderEnum = 
	{
		OK : 0,
		LONGLAT : 1,
		LATLONG : 2,
		UNKNOWN : 3,
		MIXED: 4,
		LATLONGML : 5,
		LONGLATML : 6
	}
	
	this.wkt = new OpenLayers.Format.WKT();
	this.gml = new OpenLayers.Format.GML.v3({ 
             srsName: "urn:x-ogc:def:crs:EPSG:4326"
         }); 
	
	var lon = -90, lat = 25, //Gulf of Mexico
	zoom = 4,
	epsg4326 = new OpenLayers.Projection('EPSG:4326'),
	epsg900913 = new OpenLayers.Projection('EPSG:900913');
				
	this.initMap = function(DIV,Options)
	{
		googleZoomLevel = 11, //max 11 on hybrid in ocean.
		
		firstLoad = false;
		
		mapDiv = "#"+DIV;
		mapOptions = Options;
		map = new OpenLayers.Map( 
		{
			div: DIV,
			projection: new OpenLayers.Projection('EPSG:900913'),
			displayProjection: new OpenLayers.Projection('EPSG:4326'),
			zoomDuration: 10,
			maxResolution: "auto",
			maxExtent: new OpenLayers.Bounds(-180, -90, 180, 90),
			minResolution: "auto",
			//allOverlays:true,
			eventListeners: {
				featureover: function(e) 
				{
					e.feature.renderIntent = "select";
					e.feature.layer.drawFeature(e.feature);
					////console.log("Map says: " + e.feature.id + " mouse over " + e.feature.layer.name);
					jQuery(mapDiv).trigger('overFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				},
				featureout: function(e) 
				{
					e.feature.renderIntent = "default";
					e.feature.layer.drawFeature(e.feature);
					////console.log("Map says: " + e.feature.id + " mouse out " + e.feature.layer.name);
					jQuery(mapDiv).trigger('outFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				},
				featureclick: function(e) 
				{
					//console.log("Map says: " + e.feature.id + " clicked on " + e.feature.layer.name + " udi:" + e.feature.attributes["udi"]);
					jQuery(mapDiv).trigger('clickFeature',{"featureID":e.feature.id,"attributes":e.feature.attributes});
				}
			}
		});
		
		if (Options.staticMap)
		{
			makeStatic();
			googleZoomLevel = 7;
		}
		
		dstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["default"]);
		dstyle.graphicZIndex = 1;
		dstyle.fillOpacity = 0;
		dstyle.strokeOpacity = 0.5;
		dstyle.strokeWidth = 2;
		dstyle.pointRadius = 10;
		
		defaultStyle = new OpenLayers.Style(dstyle);
		
		sstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style[dstyle]);
		
		sstyle.fillOpacity = 0.0;
		sstyle.strokeWidth = 4;
		sstyle.strokeOpacity = 1.0;
		//sstyle.graphicZIndex = 2;
		sstyle.pointRadius = 12;
		//sstyle.strokeColor = "#FFFFFF";
		
		if (Options.labelAttr)
		{
			sstyle.label = "${" + Options.labelAttr + "}";
			//console.log("label set");
		}
		
		tstyle = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style["temporary"]);
		
		selectStyle = new OpenLayers.Style(sstyle);
		
		temporaryStyle = new OpenLayers.Style(tstyle);
		
		defaultStyleMap = new OpenLayers.StyleMap(
		{
			"default": defaultStyle,
			"select": selectStyle
		});
		
		google_hybrid = new OpenLayers.Layer.Google('Google Hybrid Map', 
		{
			type: google.maps.MapTypeId.HYBRID,
			numZoomLevels: googleZoomLevel,
			sphericalMercator: true,
			displayInLayerSwitcher: true
		});
		
		google_terain = new OpenLayers.Layer.Google('Google Terrain Map', 
		{
			type: google.maps.MapTypeId.TERRAIN,
			numZoomLevels: googleZoomLevel,
			sphericalMercator: true,
			displayInLayerSwitcher: true
		});
		
		vlayer = new OpenLayers.Layer.Vector("Datasets",{
			projection: new OpenLayers.Projection('EPSG:4326'),
			styleMap: defaultStyleMap,
			rendererOptions: {zIndexing: true},
			afterAdd: function() 
			{
				//console.log('layer ready');
			},
			displayInLayerSwitcher: false
		});
		
		modify = new OpenLayers.Control.ModifyFeature(vlayer);
		modify.mode = OpenLayers.Control.ModifyFeature.RESHAPE;
		modify.createVertices = true;
		map.addControl(modify);
		
		var filterStyles = new OpenLayers.StyleMap(
		{
			"default": new OpenLayers.Style(
			{
				strokeColor: "#66CCCC",
				strokeOpacity: 1,
				strokeWidth: 3,
				fillOpacity: 0.0,
				fillColor: "#66CCCC",
				strokeDashstyle: "dash",
				label: "FILTER AREA",
				fontColor: "white",
				labelOutlineColor: "black",
				labelOutlineOpacity: 1,
				fontOpacity: 1,
				labelOutlineWidth: .5,
				graphicZIndex: -2
			}),
			"select": new OpenLayers.Style(
			{
				strokeColor: "#66CCCC",
				strokeOpacity: 1,
				strokeWidth: 3,
				fillOpacity: 0.0,
				fillColor: "#66CCCC",
				strokeDashstyle: "dash",
				label: "FILTER AREA",
				fontColor: "white",
				labelOutlineColor: "black",
				labelOutlineOpacity: 1,
				fontOpacity: 1,
				labelOutlineWidth: .5,
				graphicZIndex: -2
			})			
		});
		
		flayer = new OpenLayers.Layer.Vector("Filter", {
			styleMap: filterStyles,
			rendererOptions: {zIndexing: true},
			displayInLayerSwitcher: false
		});
		
		filter = new OpenLayers.Control.DrawFeature(flayer, OpenLayers.Handler.RegularPolygon, {
                            handlerOptions: {
                                sides: 4,
                                irregular: true
                            }
			});
		map.addControl(filter);
		
		//TODO: if Options.BaseMapTerainDefault == true then add terain layer first.
		
		map.addLayers([google_hybrid, vlayer, flayer]);
		
		function get_my_url (bounds) {
			var res = this.map.getResolution();
			var x = Math.round ((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
			var y = Math.round ((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
			var z = this.map.getZoom();
			
			var path = z + "/" + x + "/" + y + "." + this.type +"?"+ parseInt(Math.random()*9999);
			var url = this.url;
			if (url instanceof Array) {
				url = this.selectUrl(path, url);
			}
			return url + this.service +"/"+ this.layername +"/"+ path;
			
		}
		
		if (Options.showRadar)
		{
			var n0q = new OpenLayers.Layer.TMS(
			'NEXRAD Base Reflectivity',
			'https://mesonet.agron.iastate.edu/cache/tile.py/',
			{layername      : 'nexrad-n0q-900913',
				service         : '1.0.0',
				type            : 'png',
				visibility      : true,
				getURL          : get_my_url,
			isBaseLayer     : false}
			);
			
			map.addLayers([n0q]);
		}
		
		//draw = new OpenLayers.Control.DrawFeature(vlayer, OpenLayers.Handler.Polygon);
		//map.addControl(draw);
		
		drawControls = {
			point: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.Point),
			line: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.Path),
			polygon: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.Polygon),
			box: new OpenLayers.Control.DrawFeature(vlayer,OpenLayers.Handler.RegularPolygon, {
				handlerOptions: {
					sides: 4,
					irregular: true
				}
			}
			)
		};
		
		for(var key in drawControls) {
			map.addControl(drawControls[key]);
		}
		
		this.setDrawMode('polygon');
		
		map.events.register('updatesize', map, function () {
			//console.log('Window Resized');
			setTimeout( function() { 
				lastBounds = map.getExtent()
				}, 200);
				if (lastBounds)
				{
					//map.zoomToExtent(lastBounds,true);
				}
			
		});
		
		map.events.register('preaddlayer', map, function () {
			////console.log('Adding something?');
		});
		
		checkAllowModify(false);
		
		vlayer.events.register('loadstart', vlayer, function () {
			//console.log("loading");
		});
		
		vlayer.events.on({
			'beforefeaturemodified': function(event) {
				////console.log("Selected " + event.feature.id  + " for modification");
				jQuery("#eraseTool").button("enable");
				if (typeof event.feature == 'object')
				{
					//checkPolygon(event.feature.id);
				}
				jQuery("#helptext").html('Modify Mode<br>(Drag points to modify feature)');
			},
			'afterfeaturemodified': function(event) {
				////console.log("Finished with " + event.feature.id);
				jQuery("#eraseTool").button("disable");
				checkOnlyOnePolygon();
				if (typeof event.feature == 'object')
				{
					jQuery(mapDiv).trigger('featureAdded',getCoordinateList(event.feature));
				}
				jQuery("#helptext").text('Navigation Mode');
				
			},
			'beforefeatureadded': function(event) {
				stopDrawing();
				checkAllowModify(true);
			},
			'featureadded': function(event) {
				//checkPolygon(event.feature.id);
				checkOnlyOnePolygon();
				jQuery(mapDiv).trigger('featureAdded',getCoordinateList(event.feature));
			},
			'loadend': function(event) {
				//console.log('Done loading vlayer layer');
				map.updateSize();
				vlayer.redraw();
			},
			'loadstart': function(event) {
				//console.log('Done Drawing?');
			},
			'vertexmodified': function(event) {
				jQuery(mapDiv).trigger('vectorChanged',getCoordinateList(event.feature));
			},
			'sketchmodified': function(event) {
				jQuery(mapDiv).trigger('vectorChanged',getCoordinateList(event.feature));
				//jQuery(mapDiv).trigger('vectorChanged',event.feature.getCoordinateList());
			}
		});
		
		flayer.events.on({
			beforefeatureadded: function(event) {
				//console.debug(wkt.write(event.feature));
				flayer.removeAllFeatures();
				filter.deactivate();
			},
			featureadded: function(event) {
				jQuery(mapDiv).trigger('filterDrawn');
			}
		});
		
		google.maps.event.addListener(google_hybrid.mapObject, "tilesloaded", function() {
			//console.log("Tiles loaded");
			if (!firstLoad)
			{
				//console.log('done with map');
				firstLoad = true;
				setTimeout( function() { 
					map.removeLayer(google_hybrid);
					map.updateSize();
					map.addLayer(google_hybrid);
						
					jQuery(mapDiv).trigger('imready',mapDiv);
				}
				, 100)
			};
		});
				
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom, true, true);
		map.render(DIV);
		
		//Add map selector for highlighting
		mapOptions.allowModify
		selectControl = new OpenLayers.Control.SelectFeature(vlayer);
		map.addControls([selectControl]);
		//selectControl.activate();
		
		lastBounds = map.getExtent();
	}
	
	this.flashMap = function ()
	{
		
		setTimeout( function() { 
			map.removeLayer(google_hybrid);
			map.updateSize();
			map.addLayer(google_hybrid);
			
		}
		, 100)
		
	}
	
	this.setDrawMode = function(handlerType) 
	{
		for(key in drawControls) {
			var control = drawControls[key];
			if (handlerType == key) 
			{
				//control.activate();
				drawMode = handlerType;
				control.deactivate();
			} 
			else 
			{
				
				control.deactivate();
			}
		}
	}
	
	this.updateMap = function ()
	{
		map.updateSize();
	}
	
	function makeStatic()
	{
		Controls = map.getControlsByClass('OpenLayers.Control.Navigation');
		Controls[0].destroy();
		
		Controls = map.getControlsByClass('OpenLayers.Control.Zoom');
		Controls[0].destroy();
	}
	
	this.showTerrainMap = function ()
	{
		map.addLayers([google_terain]);
		//map.setBaseLayer(map.layers[1]);
		map.setBaseLayer(map.getLayersByName('Google Terrain Map'));
	}
	
	this.showHybridMap = function ()
	{
		//map.setBaseLayer(map.layers[0]);
		map.setBaseLayer(map.getLayersByName('Google Hybrid Map'));
	}
		
	this.drawFilter = function ()
	{
		filter.activate();
	}
	
	this.getFilter = function ()
	{
		return wktTransformToWGS84(wkt.write(flayer.features[0]));
	}
	
	this.clearFilter = function ()
	{
		flayer.removeAllFeatures();
	}
	
	function addImage(Img,Opacity)
	{
		var graphic = new OpenLayers.Layer.Image(
		'Image',
		Img,
		map.getExtent(),
		new OpenLayers.Size(0,0),
			{
            isBaseLayer:false, 
            visibility:true,
			opacity: Opacity
			}
		);
		map.addLayers([graphic]);
	}
	
	this.initToolbar = function (DIV,Options)
	{
		toolbarOptions = Options;
		
		this.toolbardiv = '#'+DIV;
		jQuery(this.toolbardiv)
		.append('<img id="homeTool" src="/images/geoviz/home.png">')
		//.append('<img id="filterTool" src="/images/geoviz/filter.png">')
		.append('<img id="drawTool" src="/images/geoviz/paint.png">');
		
		jQuery(this.toolbardiv).append('<span id="drawtools"></span>');

		jQuery("#drawtools")
		.append('<img id="polygonTool" src="/images/geoviz/polygon.png">')
		.append('<img id="lineTool" src="/images/geoviz/line.png">')
		.append('<img id="circleTool" src="/images/geoviz/circle.png">')
		.append('<img id="squareTool" src="/images/geoviz/square.png">');
		
		jQuery(this.toolbardiv)
		.append('<img id="eraseTool" src="/images/geoviz/delete.png">')
		//.append('<img id="panTool" src="/images/geoviz/pan.png">')
		.append('<img id="worldTool" src="/images/geoviz/world.png">')
		.append('<img id="zoominTool" src="/images/geoviz/zoomin.png">')
		.append('<img id="zoomoutTool" src="/images/geoviz/zoomout.png">');
		
		if (toolbarOptions.showExit)
		{
			jQuery(this.toolbardiv)
			.append('<img id="exitTool" src="/images/geoviz/exit.png">');
		}
		
		jQuery("#exitTool").button()
		.click(function() {
			stopDrawing();
			closeMe();
			}).qtip({
			content: {
				text: 'Exit the Map'
			}
		})
		
		jQuery("#drawtools").hide();
		
		jQuery("#homeTool")
		
		jQuery("#polygonTool").button();
		jQuery("#lineTool").button();
		jQuery("#circleTool").button();
		jQuery("#squareTool").button();
		
		jQuery("#homeTool").button()
		.click(function() {
			goHome();
			}).qtip({
			content: {
				text: 'Go Home'
			}
		});
		
		jQuery("#drawTool").button().qtip({content: {text: 'Draw a Polygon'}})
		.click(function() {
			if (drawControls[drawMode].active)
			{
				stopDrawing();
				//$(this).attr("src","/images/geoviz/draw.png");
			}
			else
			{
				startDrawing();
				//$(this).attr("src","/images/geoviz/pan.png");
			}
		});
		
		jQuery("#panTool").button()
		.click(function() {
			stopDrawing();
		});
		
		jQuery("#filterTool").button()
		.click(function() {
			drawFilter();
		});
				
		jQuery("#eraseTool").button()
		.click(function() {
			deleteSelected();
		}).qtip({content: {text: 'Delete a Feature'}});
		
		jQuery("#worldTool").button().qtip({content: {text: 'Maximum Zoom Out'}})
		.click(function() {
			zoomToMaxExtent();
		});
		
		jQuery("#zoominTool").button().qtip({content: {text: 'Zoom In'}})
		.click(function() {
			zoomIn();
		});
		
		jQuery("#zoomoutTool").button().qtip({content: {text: 'Zoom Out'}})
		.click(function() {
			zoomOut();
		});
		
		jQuery("#eraseTool").button("disable");
		
		jQuery(this.toolbardiv).append('<span style="font-family:Arial, Verdana, sans-serif;text-align:right;float:right;font-size:20;" id="helptext"></span>');
		
		jQuery("#helptext").text('Navigation Mode');
		
	}
	
	//TODO: Zoom/Pan/Select/Highlight Feature Function
		
	this.gotoAllFeatures = function ()
	{
		if (vlayer.features.length > 0)
		{
			map.zoomToExtent(vlayer.getDataExtent());
		}
	}
	
	this.gotoFeature = function (attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		map.zoomToExtent(myFeature.geometry.getBounds())
	}
	
	this.highlightFeature = function (attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.highlight(myFeature);
		}
	}
	
	this.unhighlightFeature = function (attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.unhighlight(myFeature);
		}
	}
	
	this.selectFeature = function (attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.highlight(myFeature);
		}
	}
	
	this.selectNone = function ()
	{
		selectControl.unselectAll();
	}
	
	this.selectNone = function unselectFeature(attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		if (myFeature)
		{
			selectControl.unselect(myFeature);
		}
	}
	
	function checkAllowModify(On)
	{
		if (mapOptions.allowModify && On)
		{
			modify.activate();
		}
		else
		{
			modify.deactivate();
		}
	}
	
	function checkOnlyOnePolygon()
	{
		if (mapOptions.onlyOneFeature)
		{
			if (vlayer.features.length > 0)
			{
				jQuery("#drawTool").button("disable");
				return true;
			}
			else
			{
				jQuery("#drawTool").button("enable");
				return false;
			}
		}
	}
	
	this.getFeatureById = function (FeatureID)
	{
		return Feature = vlayer.getFeatureById(FeatureID);
	}
	
	this.getFeatureIDFromAttr = function (attrName,attrValue)
	{
		var myFeature=vlayer.getFeaturesByAttribute(attrName,attrValue)[0];
		return myFeature.id;
	}
	
	this.concaveHull = function (FeatureID)
	{
		var Feature = vlayer.getFeatureById(FeatureID);
		var featureID = Feature.id;
		var WKT = this.wkt.write(Feature);
		jQuery.ajax({
			url: "/includes/geoviz/concavehull.php", // replace this url with geoviz lib
			type: "POST",
			data: {wkt: WKT, featureid: featureID},
			context: document.body
			}).done(function(html) {
			eventObj = jQuery.parseJSON(html);
			jQuery(mapDiv).trigger('featureConverted',eventObj);
			//console.log(html);
			return true;
		});	
	}
	
	this.gmlToWKT = function (GML)
	{
		jQuery.ajax({
			url: "/includes/geoviz/gmltowkt.php", // replace this url with geoviz lib
			type: "POST",
			data: {gml: GML},
			context: document.body
			}).done(function(html) {
				//eventObj = jQuery.parseJSON(html);
				jQuery(mapDiv).trigger('gmlConverted',html);
				//console.log(html);
			return true;
		});	
	}
	
	this.wktToGML = function (WKT)
	{
		jQuery.ajax({
			url: "/includes/geoviz/wkttogml.php", // replace this url with geoviz lib
			type: "POST",
			data: {wkt: WKT},
			context: document.body
			}).done(function(html) {
				//eventObj = jQuery.parseJSON(html);
				jQuery(mapDiv).trigger('wktConverted',html);
				//console.log(html);
			return true;
		});	
	}
	
	this.checkPolygon = function (FeatureID)
	{
		var Feature = vlayer.getFeatureById(FeatureID);
		var featureID = Feature.id;
		var WKT = this.wkt.write(Feature);
		jQuery.ajax({
			url: "/includes/geoviz/geocheck.php", // replace this url with geoviz lib
			type: "POST",
			data: {wkt: WKT, featureid: featureID},
			context: document.body
			}).done(function(html) {
				eventObj = jQuery.parseJSON(html);
				jQuery(mapDiv).trigger('featureConverted',eventObj);
				//console.log(html);
				return html;
		});
	}
	
	this.addFeatureFromWKT = function (WKT,Attributes,Style)
	{
		var addFeature = this.wkt.read(this.wktTransformToSperMerc(WKT));
		
		// Sample: {"strokeColor": "#ff00ff", "fillColor": "#ffffff"}
		if (typeof Style == 'object')
		{
			var style = OpenLayers.Util.extend({}, OpenLayers.Feature.Vector.style['default']);
			//style.fillColor = Style.fillColor;
			//style.fillOpacity = Style.fillOpacity;
			//style.strokeColor = Style.strokeColor;
			//style.strokeWidth = Style.strokeWidth;
			//style.strokeOpacity = Style.strokeOpacity;
			
			//addFeature.style = style;
		}
		
		// Sample: {"attribute" : "value", "label": "text"}
		if (typeof Attributes == 'object')
		{
			addFeature.attributes = Attributes;
		}
				
		vlayer.addFeatures(addFeature);
		
		return addFeature;
	}
	
	function featureTransformToWGS84(Feature)
	{
		var myFeature = Feature.clone();
		myFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		return myFeature;
	}
	
	function featureTransformToSperMerc(Feature)
	{
		var myFeature = Feature.clone();
		myFeature.geometry.transform('EPSG:4326',map.getProjectionObject());
		return myFeature;
	}
	
	this.wktTransformToWGS84 = function (WKT)
	{
		var wktFeature = this.wkt.read(WKT);
		wktFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		return this.wkt.write(wktFeature);
	}
	
	this.wktTransformToSperMerc = function (WKT)
	{
		var wktFeature = this.wkt.read(WKT);
		wktFeature.geometry.transform('EPSG:4326',map.getProjectionObject());
		return this.wkt.write(wktFeature);
	}
	
	function transformLayers(Layer)
	{
		var tLayer = Layer.clone();
		for (var i=0;i<tLayer.features.length;i++)
		{
			var tFeature = tLayer.features[i];
			tFeature.geometry.transform(map.getProjectionObject(),'EPSG:4326');
		}
		return tLayer;
		
	}
	
	this.unhighlightAll = function ()
	{
		for (var i=0;i<vlayer.features.length;i++)
		{
			var Feature = vlayer.features[i];
			selectControl.unhighlight(Feature);
		}
	}
	
	this.determineOrder = function (List)
	{
		var pointList = this.checkPointList(List);
		var orders = new Array();
		var LongLat = 0;
		var LatLong = 0;
				
		pointList = pointList.split(" ");
		
		for (var i=0;i<pointList.length;i++)
		{
			var pointSplit = pointList[i].split(",");
			
			if (Math.abs(pointSplit[0]) > 90 && Math.abs(pointSplit[1]) <= 90)
			{
				//console.log ('LongLat');
				orders.push('LongLat');
				LongLat += 1;
			}
			else if (Math.abs(pointSplit[0]) <= 90 && Math.abs(pointSplit[1]) > 90)
			{
				//console.log('LatLong');
				LatLong += 1;
			}
			else if (pointSplit[0] > pointSplit[1] && pointSplit[1] < 0 && (pointSplit[0] - (pointSplit[1])) > 90)
			{
				//console.log('Pos LatLong');
				LatLong += .5;
			}
			else
			{
				//console.log('unknown');
				LatLong += 0;
			}
		}
		
		
		if ((LatLong == pointList.length))
		{
			console.log('For Sure Lat:Long');
			return orderEnum.LATLONG;
		}
		else if ((LongLat == pointList.length))
		{
			console.log('For Sure Long:Lat');
			return orderEnum.LONGLAT;
		}
		else if (LongLat > 0 && LatLong > 0)
		{
			console.log('Unknown Mixed');
			return orderEnum.MIXED;
		}
		else if ((LatLong / pointList.length) > (LongLat / pointList.length))
		{
			console.log('Most Likely Lat:Long');
			return orderEnum.LATLONGML;
		}
		else if ((LatLong / pointList.length) < (LongLat / pointList.length))
		{
			console.log('Most Likely Long:Lat');
			return orderEnum.LONGLATML;
		}
		else if (LongLat == 0 && LatLong == 0)
		{
			console.log('Really Unknown');
			return orderEnum.UNKNOWN;
		}
	}
	
	function determineOrder2(List)
	{
		var pointList = checkPointList(List);
		var lx = new Array();
		var ry = new Array();
		
		pointList = pointList.split(" ");
		
		for (var i=0;i<pointList.length;i++)
		{
			var pointSplit = pointList[i].split(",");
			lx.push(pointSplit[0]);
			ry.push(pointSplit[1]);
		}
		lxMax = Math.max.apply(Math,lx);
		lxMin = Math.min.apply(Math,lx);
		ryMax = Math.max.apply(Math,ry);
		ryMin = Math.min.apply(Math,ry);
		
		if (Math.abs(lxMax) > 90 || Math.abs(lxMin) > 90)
		{
			return 'LongLat';
		}
		else if (Math.abs(ryMax) > 90 || Math.abs(ryMin) > 90)
		{
			return 'LatLong';
		}
		else
		{
			return 'Unknown';
		}
	}
	
	this.checkCoordList = function (List)
	{
		var pointList = this.checkPointList(List);
		var lx = 0;
		var ry = 0;
		var msg = new Array();
		var msgTxt = 'OK';
		
		var superList = pointList.trim();
		superList = superList.split(/[\s,]+/); // /^\s+(.*?)\s+$/
		
		pointList = pointList.split(" ");
		
		if (superList.length % 2 === 0)
		{
			for (var i=0;i<pointList.length;i++)
			{
				var pointSplit = pointList[i].split(",");
				lx = pointSplit[0];
				ry = pointSplit[1];
				
				if (Math.abs(lx) > 90 && Math.abs(ry) > 90)
				{
					msgTxt = 'Both coordinates over 90 in set '+(i+1);
					msg.push(msgTxt);
				}
				
				if (Math.abs(lx) > 180 || Math.abs(ry) > 180)
				{
					msgTxt = 'Some coordinates over 180 in set '+(i+1);
					msg.push(msgTxt);
				}
				
				// if (Math.abs(lx) > 90 && Math.abs(lx))
				// {
					// msgTxt = 'Suspected tuple in set '+(i+1);
					// msg.push(msgTxt);
					// console.log(msgTxt);
				// }
			}
			
			if (msgTxt == 'OK')
			{
				msg.push(msgTxt);
			}
		}
		else
		{
			msg = 'Uneven tuples, only '+superList.length+' coordinates found';
			msg.push(msgTxt);
			//console.log(msg);
		}
		
		console.debug(msg);
		return msg;
	}
	
	function getCoordinateList (Feature)
	{
		var points = "";
		
		if (typeof Feature != 'undefined')
		{
			var myFeature = Feature.clone();
			myFeature = featureTransformToWGS84(myFeature);
			var pointList = myFeature.geometry.getVertices();
			
			
			for (var i=0;i<pointList.length;i++)
			{
				points += pointList[i].y.toPrecision(8) + ","+pointList[i].x.toPrecision(8)+" ";
			}
			
			//points += pointList[0].y.toPrecision(8) + ","+pointList[0].x.toPrecision(8);
		}
		return points;
		
	}
	
	this.getCoordinateList = function (FeatureID)
	{
		var Feature = vlayer.getFeatureById(FeatureID);
		return getCoordinateList(Feature);
	}
	
	this.checkPointList = function (List)
	{
		var pointList = "";
		var points = List.match(/(-?\d+\.\d+|-?\d+)/g); //-?\d+(\.\d+)?
		for (var i=0;i<points.length;i+=2)
		{
			if (i!=0) {pointList += " "};
			pointList += points[i];
			if (typeof points[i+1] !== 'undefined')
			{
				pointList += "," + points[i+1];
			}
		}
		return pointList;
	}
	
	this.addFeatureFromcoordinateList = function (List,NoFlip)
	{
		var pointList = this.checkPointList(List);
		
		//console.log(determineOrder(List));
		
		checkMsg = this.checkCoordList(List);
		if (checkMsg != 'OK')
		{
			jQuery(mapDiv).trigger('coordinateError',checkMsg);
			console.debug(checkMsg);
		}
		else
		{
		
			pointList = pointList.split(" ");
			var points = "";
			for (var i=0;i<pointList.length;i++)
			{
				var pointSplit = pointList[i].split(",");
				if (!NoFlip)
				{
					points += pointSplit[1]+" "+pointSplit[0]+",";
				}
				else
				{
					points += pointSplit[0]+" "+pointSplit[1]+",";
				}
			}
			if (drawMode == 'polygon' || drawMode == 'box')
			{
				var WKT = "POLYGON((" + points.substring(0,(points.length)-1) + "))";
			}
			else if (drawMode == 'point')
			{
				var WKT = "MULTIPOINT(" + points.substring(0,(points.length)-1) + ")";
			}
			else if (drawMode == 'line')
			{
				var WKT = "LINESTRING(" + points.substring(0,(points.length)-1) + ")";
			}
			//console.debug(WKT);
			var sMwkt = this.wktTransformToSperMerc(WKT);
			if (sMwkt.indexOf("NaN") == -1)
			{
				var Feature = this.wkt.read(this.wktTransformToSperMerc(WKT));
				vlayer.addFeatures([Feature]);
				modify.activate();
				return true;
			}
			else
			{
				return false;
			}
			
		}
	}
	
	this.removeAllFeaturesFromMap = function ()
	{
		vlayer.removeAllFeatures();
	}
	
	function startDrawing ()
	{
		if (typeof toolbarOptions != 'undefined')
		{
			if (toolbarOptions.showDrawTools)
			{
				jQuery("#drawtools").fadeIn();
				//$("#drawtools").show();
			}
		}
		
		if (!checkOnlyOnePolygon())
		{
			checkAllowModify(true);
			//draw.activate();
			drawControls[drawMode].activate()
			jQuery("#helptext").html('Drawing Mode<br>(Double click to stop)');
			
		}
	}
	
	this.startDrawing = function ()
	{
		startDrawing();
	}
	
	function stopDrawing ()
	{
		jQuery("#drawtools").fadeOut();
		//$("#drawtools").hide();
		modify.deactivate();
		//draw.deactivate();
		drawControls[drawMode].deactivate();
		filter.deactivate();
		modify.activate();
		jQuery("#helptext").text('Navigation Mode');
	}
	
	this.stopDrawing = function ()
	{
		stopDrawing ();
	}
	
	this.goHome = function ()
	{
		map.setCenter(new OpenLayers.LonLat(lon, lat).transform('EPSG:4326', 'EPSG:900913'), zoom);
	}	
	
	this.zoomToMaxExtent = function ()
	{
		map.zoomToMaxExtent()
	}
	
	this.panToFeature = function (FeatureID)
	{
		var Feature = vlayer.getFeatureById(FeatureID);
		map.panTo(Feature.geometry.getBounds().getCenterLonLat());
	}
	
	this.getSingleFeature = function ()
	{
		var Feature = vlayer.features[0];
		if (typeof Feature != 'undefined')
		{
			return Feature.id;
		}
	}
	
	this.zoomIn = function ()
	{
		map.zoomIn();
	}
	
	this.zoomOut = function ()
	{
		map.zoomOut();
	}
	
	this.deleteSelected = function ()
	{
		if (modify.feature)
		{
			deleteFeatureID = modify.feature.id
			modify.unselectFeature();
			vlayer.removeFeatures(vlayer.getFeatureById(deleteFeatureID));
			jQuery(mapDiv).trigger('featureAdded','');
		}
		checkOnlyOnePolygon();
	}
	
	function closeMe()
	{
		coordlist = this.getCoordinateList(vlayer.features[0]);
		jQuery(mapDiv).trigger('closeMe',coordlist);
	}
	
	function writeGML(Feature)
	{
		Feature.geometry.transform('EPSG:900913','EPSG:4326');
		return gml.write(Feature)
		
	}
	
	this.getWKT = function (FeatureID)
	{
		var Feature = vlayer.getFeatureById(FeatureID);
		return this.wkt.write(Feature);
	}

}