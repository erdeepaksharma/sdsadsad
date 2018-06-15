<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<?php
	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/styles/style_sitereview.css');
	$apiKey = Engine_Api::_()->seaocore()->getGoogleMapApiKey();
	$this->headScript()->appendFile("https://maps.googleapis.com/maps/api/js?libraries=places&key=$apiKey");

	$latitude = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.map.latitude', 0);  $longitude = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.map.longitude', 0);  
?>

<script type="text/javascript">

  var current_page = '<?php echo $this->current_page; ?>';
  
  var paginatePageLocations = function(page) {
  
		var  formElements = document.getElementById('filter_form');
		var parms = formElements.toQueryString(); 
		var param = (parms ? parms + '&' : '') + 'is_ajax=1&format=html&page=' + page+'&listingtype_id='+<?php echo $this->listingtype_id; ?>;
		document.getElementById('page_location_loding_image').style.display ='';
     var url = en4.core.baseUrl + 'widget/index/mod/sitereview/name/browselocation-sitereview';
    clearOverlays();
    gmarkers = [];
    var parent_list_location_map_anchor = document.getElementById('list_location_map_anchor').getParent();
    en4.core.request.send(new Request.HTML({
      method : 'post',
			'url' : url,
			'data' : param,
      onSuccess :function(responseTree, responseElements, responseHTML, responseJavaScript) {
				document.getElementById('page_location_loding_image').style.display ='none';
				parent_list_location_map_anchor.innerHTML = responseHTML;
				setMarker();
      }
    }));
  };
  
//  window.addEvent('load', function() {
//    var request = new Request.JSON({
//      url : en4.core.baseUrl + 'sitereview/index/get-listing-type',
//      data : {
//        format: 'json',
//        isAjax: 1,
//        type: 'layout_sitereview'
//      },
//      'onSuccess' : function(responseJSON) {
//        if( !responseJSON.getListingType ) {
//          document.getElement("." + responseJSON.getClassName + "_browselocation_sitereview").empty();
//        }
//      }
//    });
//    request.send();
//  });

  var pageAction = function(page) {
		paginatePageLocations(page);
  }
</script>

<?php if (empty($this->is_ajax)) : ?>
  <div class="list_browse_location" id="list_browse_location" >
    <?php if (count($this->paginator) > 0): ?>
      <div class="list_map_container_right" id ="list_map_container_right"></div>
      <div id="list_map_container" class="list_map_container absolute" style="visibility:hidden;">
        <div class="list_map_container_topbar" id='list_map_container_topbar' style ='display:none;'>
          <a id="largemap" href="javascript:void(0);" onclick="smallLargeMap(1)" class="bold fleft">&laquo; <?php echo $this->translate('Large Map'); ?></a>
          <a id="smallmap" href="javascript:void(0);" onclick="smallLargeMap(0)" class="bold fleft"><?php echo $this->translate('Small Map'); ?> &raquo;</a>
        </div>

        <div class="list_map_container_map_area fleft seaocore_map" id="listlocation_map">
          <div class="list_map_content" id="listlocation_browse_map_canvas" ></div>
          <?php $siteTitle = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title; ?>
          <?php if (!empty($siteTitle)) : ?>
          <div class="seaocore_map_info"><?php echo $this->translate("Locations on %s","<a href='' target='_blank'>$siteTitle</a>");?></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="list_map_container_list" id="list_content_content">
  <?php endif; ?>

  <a id="list_location_map_anchor" class="pabsolute"></a>
		<?php if (count($this->paginator) > 0): ?>
			<ul class="sr_browse_list" id="seaocore_browse_list"><?php if (!empty($this->is_ajax)) : ?>	
				<li style="border:none;padding-top:1px;"><p>
				<?php echo $this->translate(array('%s '.$this->listing_singular_lc.' found.', '%s '.$this->listing_plural_lc.' found.', $this->totalresults),$this->locale()->toNumber($this->totalresults)) ?>
				</p></li>
				<?php foreach ($this->paginator as $item):?>
				<?php Engine_Api::_()->sitereview()->setListingTypeInRegistry($item->listingtype_id);
                $listingType = Zend_Registry::get('listingtypeArray' . $item->listingtype_id);?>
				<?php if(!empty($item->location) || !empty($this->locationVariable)) : ?>
					<li class="b_medium">
						<div class='sr_browse_list_photo b_medium'>
							<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
								<?php if($item->featured):?>
									<i class="sr_list_featured_label" title="<?php echo $this->translate('Featured'); ?>"></i>
                <?php endif;?>
								<?php if($item->newlabel):?>
                  <i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i>
								<?php endif;?>
							<?php endif;?>
							
							<?php echo $this->htmlLink($item->getHref(array('profile_link' => 1)), $this->itemPhoto($item, 'thumb.normal', '', array('align' => 'center')), array('title' => $item->getTitle(), 'target' => '_parent', 'class' => !empty($item->location)? "marker_photo_".$item->listing_id :'un_location_list')); ?>
							
							<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?>
								<?php if (!empty($item->sponsored)): ?>
										<div class="sr_list_sponsored_label" style="background: <?php echo $listingType->sponsored_color; ?>">
											<?php echo $this->translate('SPONSORED'); ?>                 
										</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
		
							<div class='sr_browse_list_info'>
								<div class='sr_browse_list_info_header'>
									<div class="sr_list_title_small o_hidden">
										<?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('title'
										=> $item->getTitle(), 'target' => '_parent', 'class' =>!empty($item->location)? "marker_".$item->listing_id :'un_location_list')); ?>
									</div>
							  </div>
								<div class='sr_browse_list_info_stat seaocore_txt_light'>
									<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('list.postedby', 1)):?>
										<?php echo $this->timestamp(strtotime($item->creation_date)) ?> - <?php echo $this->translate($this->listing_singular_upper. '_posted_by'); ?>
										<?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>,
									<?php endif;?>
									<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>,
									<?php if ($listingType->allow_review): ?>
										<?php echo $this->translate(array('%s review', '%s reviews', $item->review_count), $this->locale()->toNumber($item->review_count)) ?>,
									<?php endif; ?>
									<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>,
									<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?>
							</div>
							<?php if((!empty($item->location) && $listingType->location) || (!empty($item->price) && $listingType->price)): ?>
								<div class='sr_browse_list_info_stat seaocore_txt_light'>
									<?php if($item->price > 0 && $listingType->price): ?>
										<?php echo Engine_Api::_()->sitereview()->getPriceWithCurrency($item->price); ?>
									<?php endif; ?><?php if((!empty($item->location) && $listingType->location) && ($item->price > 0 && $listingType->price)): ?><?php echo $this->translate(", "); ?><?php endif; ?>
									<?php if(!empty($item->location) && $listingType->location): ?>
										<?php  echo $this->translate("Location: "); echo $this->translate($item->location); ?>
											- <b>
													<?php if (!empty($this->mobile)) : ?>
														<?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $item->listing_id, 'resouce_type' => 'sitereview_listing', 'is_mobile' => $this->mobile), $this->translate("Get Directions"), array('target' => '_blank')) ; ?>
													<?php else: ?>
														<?php if (!empty($this->is_ajax)) : ?>
															<?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $item->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('onclick' => 'owner(this);return false')) ; ?>
															<?php else : ?>
																<?php echo  $this->htmlLink(array('route' => 'seaocore_viewmap', "id" => $item->listing_id, 'resouce_type' => 'sitereview_listing'), $this->translate("Get Directions"), array('class' => 'smoothbox')) ; ?>
															<?php endif; ?>
													<?php endif; ?>
												</b>
									<?php endif; ?>
								</div>
								<?php if (!empty($item->distance) && isset($item->distance)): ?>
									<div class="seaocore_browse_list_info_stat">
										<?php $flage = Engine_Api::_()->seaocore()->geoUserSettings('sitereview');
										if (!$flage): ?>
											<b><?php echo $this->translate("approximately %s miles", round($item->distance, 2)); ?></b>
										<?php else: ?>
											<b><?php $distance = (1 / 0.621371192) * $item->distance; echo $this->translate("approximately %s kilometers", round($distance, 2)); ?></b>
										<?php endif; ?>
									</div>
								<?php endif; ?>
						  <?php endif; ?>
							<?php if (!empty($item->body)): ?>
								<div class="sr_browse_list_info_blurb">
									<?php echo $this->viewMore(strip_tags($item->body)) ?>
								</div>
							<?php elseif (!empty($item->description)): ?>
								<div class="sr_browse_list_info_blurb">
									<?php echo $this->viewMore(strip_tags($item->description)) ?>
								</div>
							<?php endif; ?>
							<div class="sr_browse_list_info_footer clr o_hidden mtop5">
								<div class="sr_browse_list_info_footer_icons">
									<?php   if( !empty($item->closed) ): ?>
										<i class="sr_icon seaocore_icon_disapproved" title="<?php echo $this->translate('Not approved');?>"></i>
									<?php endif;?>
									<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)) :?>
										<?php if (!empty($item->sponsored)): ?>
											<i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>
										<?php endif; ?>
										<?php if (!empty($item->featured)): ?>
											<i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>
										<?php endif; ?>
									<?php endif; ?>
								</div>			
							</div>
						</div>
					</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<div class="clr sr_browse_location_paging" style="margin-top:10px;">
				<?php echo $this->paginationControl($this->result, null, array("pagination/pagination.tpl", "sitereview"), array("orderby" => $this->orderby)); ?>
				<?php if( count($this->paginator) > 1 ): ?>
					<div class="fleft" id="page_location_loding_image" style="display: none;margin:5px;">
						<img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/loading.gif' alt="" />
					</div>
				<?php endif; ?>
			</div>	<?php endif; ?>
		<?php else: ?>
			<div class="tip"> 
				<span><?php echo $this->translate("No $this->listing_plural_lc have been posted yet."); ?></span>
			</div>
		<?php endif; ?>
		<?php if (empty($this->is_ajax)) : ?>	
	</div>
</div>

<script type="text/javascript" >

  /* moo style */
  window.addEvent('domready',function() {
    //smallLargeMap(1);
    var Clientwidth = $('global_content').getElement(".layout_sitereview_browselocation_sitereview").clientWidth;

		var offsetWidth = $('list_map_container').offsetWidth;
		$('listlocation_browse_map_canvas').setStyle("height",offsetWidth);

    if (document.getElementById("smallmap"))
    document.getElementById("smallmap").style.display = "none";
    if ($('list_map_right'))
			$('list_map_right').style.display = 'none';

    <?php if($this->paginator->count()>0):?>
			<?php if( $this->enableLocation): ?>
				initialize();
			<?php endif; ?>  
    <?php endif;?>
  });
  
	if ($('seaocore_browse_list')) {

		var elementStartY = $('listlocation_map').getPosition().x ;
		var offsetWidth = $('list_map_container').offsetWidth;
		var actualRightPostion = window.getSize().x - (elementStartY + offsetWidth);


		function setMapContent () {

			if (!$('seaocore_browse_list')) {
				return;
			}
			
			var element=$("list_map_container");
			if (element.offsetHeight > $('seaocore_browse_list').offsetHeight) {
				if(!element.hasClass('absolute')) {
					element.addClass('absolute');
					element.removeClass('fixed');
				if(element.hasClass('bottom'))
					element.removeClass('bottom');
				}
				return;
			}
			
			var elementPostionStartY = $('seaocore_browse_list').getPosition().y ;
			var elementPostionStartX = $('list_map_container').getPosition().x ;
			var elementPostionEndY = elementPostionStartY + $('seaocore_browse_list').offsetHeight - element.offsetHeight;

			if( ((elementPostionEndY) < window.getScrollTop())) {
				if(element.hasClass('absolute'))
					element.removeClass('absolute');
				if(element.hasClass('fixed'))
					element.removeClass('fixed');
				if(!element.hasClass('bottom'))
					element.addClass('bottom');
			} 
			else if(((elementPostionStartY)  < window.getScrollTop())) {
				if(element.hasClass('absolute'))
					element.removeClass('absolute');
				if(!element.hasClass('fixed'))
					element.addClass('fixed');
				if(element.hasClass('bottom'))
					element.removeClass('bottom');
					element.setStyle("right",actualRightPostion);
					element.setStyle("width",offsetWidth);
			}
			else if(!element.hasClass('absolute')) {
				element.addClass('absolute');
				element.removeClass('fixed');
				if(element.hasClass('bottom'))
					element.removeClass('bottom');
			}
		}

		window.addEvent('scroll', function () {
			setMapContent();
		});
		
	}

  function smallLargeMap(option) {
		if(option == '1') {
		  $('listlocation_browse_map_canvas').setStyle("height",'400px');
			document.getElementById("largemap").style.display = "none";
			document.getElementById("smallmap").style.display = "block";
			if(!$('list_map_container').hasClass('list_map_container_exp'))
				$('list_map_container').addClass('list_map_container_exp');
		} else {
		$('listlocation_browse_map_canvas').setStyle("height",offsetWidth);
			document.getElementById("largemap").style.display = "block";
			document.getElementById("smallmap").style.display = "none";
			if($('list_map_container').hasClass('list_map_container_exp'))
				$('list_map_container').removeClass('list_map_container_exp');
			
		}
		setMapContent();
		google.maps.event.trigger(map, 'resize');
	}
</script>

<script type="text/javascript" >
	function owner(thisobj) {
		var Obj_Url = thisobj.href ;
		Smoothbox.open(Obj_Url);
	}
</script>

<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . "application/modules/Seaocore/externals/scripts/infobubble.js");
?>

<script type="text/javascript" >
    //<![CDATA[
  // this variable will collect the html which will eventually be placed in the side_bar
  var side_bar_html = "";

  // arrays to hold copies of the markers and html used by the side_bar
  // because the function closure trick doesnt work there
  var gmarkers = [];
  var infoBubbles;
  var markerClusterer = null;
  // global "map" variable
  var map = null;
  // A function to create the marker and set up the event window function
  function createMarker(latlng, name, html,title_page, page_id) {
    var contentString = html;
    if(name ==0) {
      var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title: title_page,
       // page_id : page_id,
        animation: google.maps.Animation.DROP,
        zIndex: Math.round(latlng.lat()*-100000)<<5
      });
    }
    else {
      var marker =new google.maps.Marker({
        position: latlng,
        map: map,
        title: title_page,
        //page_id: page_id,
        draggable: false,
        animation: google.maps.Animation.BOUNCE
      });
    }

    gmarkers.push(marker);
    google.maps.event.addListener(marker, 'click', function() {
			google.maps.event.trigger(map, 'resize');
			map.setCenter(marker.position);
			//map.setZoom(<?php //echo '5'; ?> );
      infoBubbles.open(map,marker);
      infoBubbles.setContent(contentString);
    });

    //Show tooltip on the mouse over.
	  $$('.marker_' + page_id).each(function(locationMarker) {
			locationMarker.addEvent('mouseover',function(event) {
				google.maps.event.trigger(map, 'resize');
				map.setCenter(marker.position);
				infoBubbles.open(map,marker);
				infoBubbles.setContent(contentString);
			});			
    });
    
    //Show tooltip on the mouse over.
	  $$('.marker_photo_' + page_id).each(function(locationMarker) {
			locationMarker.addEvent('mouseover',function(event) {
				google.maps.event.trigger(map, 'resize');
				map.setCenter(marker.position);
				infoBubbles.open(map,marker);
				infoBubbles.setContent(contentString);
			});
    });
  }

  function initialize() {

    // create the map
    var myOptions = {
      zoom: <?php echo '1';?>,
      center: new google.maps.LatLng(<?php echo $latitude ?>,<?php echo $longitude ?>),
      //  mapTypeControl: true,
      // mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
      navigationControl: true,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    
    map = new google.maps.Map(document.getElementById("listlocation_browse_map_canvas"),
    myOptions);

    google.maps.event.addListener(map, 'click', function() {
      <?php if( $this->enableLocation && $this->paginator->count() > 0): ?>
				infoBubbles.close();
      <?php endif; ?>
    });
    setMarker();
    
  }
  
	function clearOverlays() {
		infoBubbles.close();
		google.maps.event.trigger(map, 'resize');

		if (gmarkers) {
			for (var i = 0; i < gmarkers.length; i++ ) {
				gmarkers[i].setMap(null);
			}
		}
    if (markerClusterer) {
		  markerClusterer.clearMarkers();
		}
	}
	
  function setMapCenterZoomPoint(bounds, maplocation) {
    if (bounds && bounds.min_lat && bounds.min_lng && bounds.max_lat && bounds.max_lng) {
      var bds = new google.maps.LatLngBounds(new google.maps.LatLng(bounds.min_lat, bounds.min_lng), new google.maps.LatLng(bounds.max_lat, bounds.max_lng));
    }
    if (bounds &&  bounds.center_lat &&  bounds.center_lng) {
      maplocation.setCenter(new google.maps.LatLng( bounds.center_lat,  bounds.center_lng), 4);
    } else {
      maplocation.setCenter(new google.maps.LatLng(lat, lng), 4);
    }
    if (bds) {
      maplocation.setCenter(bds.getCenter());
      maplocation.fitBounds(bds);
    }
  }
  
  infoBubbles = new InfoBubble({
		maxWidth: 400,
		maxHeight: 400,
		shadowStyle: 1,
		padding: 0,
		backgroundColor: '<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('seaocore.tooltip.bgcolor', '#ffffff');?>',
		borderRadius: 5,
		arrowSize: 10,
		borderWidth: 1,
		borderColor: '#2c2c2c',
		disableAutoPan: true,
		hideCloseButton: false,
		arrowPosition: 50,
		//backgroundClassName: 'sitetag_checkin_map_tip',
		arrowStyle: 0
	});
</script>
<?php endif; ?>

<script type="text/javascript" >

  function setMarker() {

  <?php if (count($this->locations) > 0) : ?>
  <?php   foreach ($this->locations as $location) : ?>
    // obtain the attribues of each marker
    var lat = <?php echo $location->latitude ?>;
    var lng =<?php echo $location->longitude  ?>;
    var point = new google.maps.LatLng(lat,lng);
    var page_id = <?php echo $this->list[$location->listing_id]->listing_id  ?>;
    <?php if(!empty ($enableBouce)):?>
    var sponsored = <?php echo $this->list[$location->listing_id]->sponsored ?>
    <?php else:?>
    var sponsored =0;
    <?php endif; ?>
    // create the marker

    <?php $page_id = $this->list[$location->listing_id]->listing_id; ?>
    var contentString = '<div id="content">'+
      '<div id="siteNotice">'+
      '</div>'+'  <div class="sr_map_info_tip o_hidden m10" style="width:250px;">'+


      '<div class="sr_map_info_tip_top o_hidden">'+

      '<div class="fright">'+
      '<span >'+
            <?php if ($this->list[$location->listing_id]->featured == 1): ?>
                '<i title="<?php echo $this->translate('Featured');?>" class="sr_icon seaocore_icon_featured"></i>'+	            <?php endif; ?>
                '</span>'+
                  '<span>'+
            <?php if ($this->list[$location->listing_id]->sponsored == 1): ?>
                '<i title="<?php echo $this->translate('Sponsored');?>" class="sr_icon seaocore_icon_sponsored"></i>'+
            <?php endif; ?>
          '</span>'+
        '</div>'+
            '<div class="sr_map_info_tip_title"><a href="<?php echo $this->url(array('listing_id' => $this->list[$location->listing_id]->listing_id, 'slug' => $this->list[$location->listing_id]->getSlug()), "sitereview_entry_view_listtype_". $this->listingtype_id, true) ?>">'+"<?php echo $this->string()->escapeJavascript( $this->list[$location->listing_id]->getTitle()); ?>"+'</a></div>'+


      '<div class="clr"></div>'+
      '</div>'+

      '<div class="sr_map_info_tip_photo">'+
         '<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1)):?><?php if($this->list[$location->listing_id]->newlabel):?><i class="sr_list_new_label" title="<?php echo $this->translate('New'); ?>"></i><?php endif;?><?php endif;?>'+
          '<?php echo $this->htmlLink($this->list[$location->listing_id]->getHref(array('profile_link' => 1)), $this->itemPhoto($this->list[$location->listing_id], 'thumb.normal')) ?>'+
          '</div>'+
      '<div class="sr_map_info_tip_info">'+

      <?php if ($this->ratngShow): ?>
        <?php if (($this->list[$location->listing_id]->rating > 0)): ?>
            '<span class="clr">'+
            <?php for ($x = 1; $x <= $this->list[$location->listing_id]->rating; $x++): ?>
                '<span class="seao_rating_star_generic rating_star_y"></span>'+
            <?php endfor; ?>
            <?php if ((round($this->list[$location->listing_id]->rating) - $this->list[$location->listing_id]->rating) > 0): ?>
                '<span class="seao_rating_star_generic rating_star_half_y"></span>'+
            <?php endif; ?>
                '</span>'+
        <?php endif; ?>
      <?php endif; ?>
            '<div class="sr_map_info_tip_info_date">'+
              "<?php  $this->translate("Location: "); echo $this->string()->escapeJavascript($location->location); ?> "+
              <?php //if (!empty($this->getdirection)) : ?>
              <?php //echo  $this->htmlLink(array('route' => 'list_viewmap', 'controller' => 'index', 'action' => 'view-map', 'id' => $location->listing_id), $this->translate('Get Direction'), array('class' => 'smoothbox')) ?>
                '<?php //echo $this->htmlLink("https://maps.google.com/?daddr=".urlencode($location->location), $this->translate("Get Direction"), array('target' => 'blank')) ?>'
              <?php //endif; ?>
            '</div>'+
            '</div>'+
            '<div class="clr"></div>'+
            '</div>'+
            '</div>';
          var marker = createMarker(point,sponsored,contentString,"<?php echo str_replace('"',' ',$this->string()->escapeJavascript($this->list[$location->listing_id]->getTitle())); ?>", page_id);

  <?php   endforeach; ?>
  $('list_map_container').style.display = 'block';
  google.maps.event.trigger(map, 'resize');
  <?php else: ?>
  $('list_map_container').style.display = 'none';
  <?php endif; ?>
  //  markerClusterer = new MarkerClusterer(map, gmarkers, {
  //  });
  <?php if (!empty($this->locations)): ?>
    setMapCenterZoomPoint(<?php echo json_encode(Engine_Api::_()->seaocore()->getProfileMapBounds($this->locations));?>,map);
  <?php endif; ?>

   //$$('.un_location_list').each(function(el) { 
     $$('.un_location_list').addEvent('mouseover',function(event) {
      infoBubbles.close();
      });
    //  });
  }
</script>