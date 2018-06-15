sm4.sitereview={ 
  
  listingInfo : {},
  //FUNCTIONS USED ON LISTING HOME PAGE...
  activepage_homeview: '',
  activepage_browseview: '',
  getLayoutSitereview : function (view_selected, listingtype_id, tabID) {  
    var currentpageid = $.mobile.activePage.attr('id')+ '-' + listingtype_id  + '-' + tabID;
    if (this.activepage_homeview == '') { 
      this.activepage_homeview = sm4.sitereview.listingInfo[currentpageid]['viewType'] + '-' + tabID;
    }
    
    if(this.activepage_homeview == view_selected + '-' + tabID) return
    else
      this.activepage_homeview = view_selected + '-' + tabID;    
    
      
    var params = $.extend({},sm4.sitereview.listingInfo[currentpageid]['params'], {
      'format' : 'html', 
      'isajax' : '1', 
      'viewType' : view_selected,
      'is_ajax_load' : '1',
      'page' : '1',
      'viewmore' : '0'
    });

    $.mobile.loading().loader("show");
    $.ajax({
      url: sm4.core.baseUrl + 'widget/index/mod/sitereview/name/listings-sitereview',
      type:'GET',         
      dataType: 'html',
      'data' : params,
      success : function(responseHTML) { 
        // $.mobile.activePage.find('#id').empty();
        $.mobile.loading().loader("hide");
        $.mobile.activePage.find('#listing_home_layout').html(responseHTML);
        sm4.core.runonce.trigger();
        sm4.core.refreshPage();
      }
    });
  },
  
  showMoreHomeListings : function (tabID, listingtype_id) { 
    var currentpageid = $.mobile.activePage.attr('id') + '-' + listingtype_id + '-' + tabID;
    var totalCount = sm4.sitereview.listingInfo[currentpageid]['totalCount'];
    var viewType = sm4.sitereview.listingInfo[currentpageid]['viewType'];
    $.mobile.activePage.find('.seaocore_loading').css('display', 'block');
    $.mobile.activePage.find('.feed_viewmore').css('display', 'none');
    var params = $.extend({},sm4.sitereview.listingInfo[currentpageid]['params'], {
      'format' : 'html', 
      'isajax' : '1', 
      'viewType' : viewType,
      'is_ajax_load' : '1',
      'page' : parseInt(sm4.sitereview.listingInfo[currentpageid]['params'].page) + parseInt(1),
      'viewmore' : '1', 
      'subject' :sm4.core.subject.guid
    });   
    $.ajax({
      type: "GET", 
      dataType: "html",
      url: sm4.core.baseUrl + 'widget/index/mod/sitereview/name/listings-sitereview',
      data: params,
      success:function( responseHTML, textStatus, xhr ) { 
            
        if ($.type($.mobile.activePage.find('div.tab_' + tabID).get(0)) != 'undefined') { 
          $.mobile.activePage.find('div.tab_' + tabID).find('ul').append(responseHTML)
          //$.mobile.activePage.find('ul').trigger("create");
          if (viewType == 'listview')
            $.mobile.activePage.find('div.tab_' + tabID).find('ul').listview('refresh');
        }
        else if ($.type($.mobile.activePage.find('#listing_home_layout').get(0)) != 'undefined') { 
					
					 $.mobile.activePage.find('#listing_home_layout').find('ul').append(responseHTML)
          //$.mobile.activePage.find('ul').trigger("create");
          if (viewType == 'listview')
            $.mobile.activePage.find('#listing_home_layout').find('ul').listview('refresh');
        }
        if (totalCount > (parseInt(params.page) * parseInt(params.limit))) {
          $.mobile.activePage.find('.seaocore_loading').css('display', 'none');
          $.mobile.activePage.find('.feed_viewmore').css('display', 'block');
        }
        else { 
          $.mobile.activePage.find('.seaocore_loading').css('display', 'none');
          $.mobile.activePage.find('.feed_viewmore').css('display', 'none');            
        }
        sm4.core.dloader.refreshPage();
        sm4.core.runonce.trigger();          
            
      }
    });
    
    
  },
  
  //FUNCTIONS USED ON BROWSE LISTING HOME PAGE...
  
  getBrowseListingsSitereview : function (view_selected, listingtype_id){  
    var currentpageid = $.mobile.activePage.attr('id')+ '-' + listingtype_id;
    if (this.activepage_browseview == '') { 
      this.activepage_browseview = sm4.sitereview.listingInfo[currentpageid]['allParams']['listingType'] + '-' + listingtype_id;
    }
    
    if(this.activepage_browseview == view_selected + '-' + listingtype_id) return
    else
      this.activepage_browseview = view_selected + '-' + listingtype_id; 
    var allParams = $.extend({},sm4.sitereview.listingInfo[currentpageid]['allParams'], {
      'format' : 'html', 
      'isajax' : '1', 
      'listingType' : view_selected,
      'is_ajax_load' : '1',
      'page' : 1,
      'viewmore' : '0'
    });       
    $.mobile.showPageLoadingMsg();
    $.ajax({
      url: sm4.core.baseUrl + 'widget/index/mod/sitereview/name/browse-listings-sitereview?' + $.mobile.activePage.find('#filter_form').serialize(),
      type:'GET',         
      dataType: 'html',
      'data' : allParams,
      success : function(responseHTML) { 
        $.mobile.hidePageLoadingMsg();
        $.mobile.activePage.find('#listing_browse_layout').html(responseHTML);
        sm4.core.runonce.trigger();
        sm4.core.refreshPage();
      }
    });
},
      
showMoreBrowseListings :function (tabID, listingtype_id) { 
  var currentpageid = $.mobile.activePage.attr('id')+ '-' + listingtype_id;
  $.mobile.activePage.find('.seaocore_loading').css('display', 'block');
  $.mobile.activePage.find('.feed_viewmore').css('display', 'none');
  var totalCount = sm4.sitereview.listingInfo[currentpageid]['totalCount'];
  var allParams = $.extend({},sm4.sitereview.listingInfo[currentpageid]['allParams'], {
    'format' : 'html', 
    'isajax' : '1',     
    'is_ajax_load' : '1',
    'page' : parseInt(sm4.sitereview.listingInfo[currentpageid]['allParams'].page) + parseInt(1),
    'viewmore' : '1', 
    'subject': sm4.core.subject.guid
    }); 
      
  $.ajax({
    type: "GET", 
    dataType: "html",
    url: sm4.core.baseUrl + 'widget/index/mod/sitereview/name/browse-listings-sitereview?' + $.mobile.activePage.find('#filter_form').serialize(),
    data: allParams,
    success:function( responseHTML, textStatus, xhr ) { 
            
      if ($.type($.mobile.activePage.find('div.tab_' + tabID)) != 'undefined') {
        $.mobile.activePage.find('#listing_browse_layout').find('ul').append(responseHTML)
        //$.mobile.activePage.find('ul').trigger("create");
        if (allParams.listingType == 'listview')
          $.mobile.activePage.find ('#listing_browse_layout').find('ul').listview('refresh');
      }
      if (totalCount > (parseInt(allParams.page) * parseInt(allParams.itemCount))) {
        $.mobile.activePage.find('.seaocore_loading').css('display', 'none');
        $.mobile.activePage.find('.feed_viewmore').css('display', 'block');
      }
      else {
        $.mobile.activePage.find('.seaocore_loading').css('display', 'none');
        $.mobile.activePage.find('.feed_viewmore').css('display', 'none');            
      }
      sm4.core.dloader.refreshPage();
      sm4.core.runonce.trigger();          
            
    }
  });
}
       
 
  
}
sm4.sitereview.searchArray ={};