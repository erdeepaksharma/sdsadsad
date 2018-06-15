/* $Id: core.js 6590 2013-04-01 00:00:00Z SocialEngineAddOns Copyright 2012-2013 BigStep Technologies Pvt. Ltd. $ */

var tab_content_id_sitestore=0;
en4.sitereview ={  
  maps:[],
  infowindow:[],
  markers:[]
};
  
en4.sitereview.ajaxTab ={
  click_elment_id:'',
  attachEvent : function(widget_id,params){
    params.requestParams.content_id = widget_id;
    var element;
    
    $$('.tab_'+widget_id).each(function(el){
      if(el.get('tag') == 'li'){
        element =el;
        return;
      }
    });
    var onloadAdd = true;
    if(element){
      if(element.retrieve('addClickEvent',false))
        return;
      element.addEvent('click',function(){
        if(en4.sitereview.ajaxTab.click_elment_id == widget_id)
          return;
        en4.sitereview.ajaxTab.click_elment_id = widget_id;
        en4.sitereview.ajaxTab.sendReq(params);
      });
      element.store('addClickEvent',true);
      var attachOnLoadEvent = false; 
      if( tab_content_id_sitestore == widget_id){ 
        attachOnLoadEvent=true;
      }else{
        $$('.tabs_parent').each(function(element){
          var addActiveTab= true;
          element.getElements('ul > li').each(function(el){
            if(el.hasClass('active')){
              addActiveTab = false;
              return;
            }
          }); 
          element.getElementById('main_tabs').getElements('li:first-child').each(function(el){
            if(el.getParent('div') && el.getParent('div').hasClass('tab_pulldown_contents')) 
              return;  
            el.get('class').split(' ').each(function(className){
              className = className.trim();
              if( className.match(/^tab_[0-9]+$/) && className =="tab_"+widget_id  ) {
                attachOnLoadEvent=true;
                if(addActiveTab || tab_content_id_sitestore == widget_id){
                  element.getElementById('main_tabs').getElements('ul > li').removeClass('active');
                  el.addClass('active');
                  element.getParent().getChildren('div.' + className).setStyle('display', null);        
                }
                return;
              }
            });          
          });
        });
      }
      if(!attachOnLoadEvent)
        return;
      onloadAdd = false;
      
    }
      
    en4.core.runonce.add(function() {
      if(onloadAdd)
        params.requestParams.onloadAdd=true;
      en4.sitereview.ajaxTab.click_elment_id = widget_id;
      en4.sitereview.ajaxTab.sendReq(params);
    });
    
    
  },
  sendReq: function(params){
    params.responseContainer.each(function(element){
      element.empty();
      new Element('div', {      
        'class' : 'sr_profile_loading_image'      
      }).inject(element);
    });
    var url = en4.core.baseUrl+'widget';
   
    if(params.requestUrl)
      url= params.requestUrl;
    
    var request = new Request.HTML({
      url : url,
      data : $merge(params.requestParams,{
        format : 'html',
        subject: en4.core.subject.guid,
        is_ajax_load:true
      }),
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        params.responseContainer.each(function(container){
          container.empty();
          Elements.from(responseHTML).inject(container);
          en4.core.runonce.trigger();
          Smoothbox.bind(container);
        });
       
      }
    });
    request.send();
  }
};

var compareSiterivewContent;
var compareSiterivew = new Class({
  listTypes:{},
  scrollCarousel :{},
  listings:{},
  activeType:'lt1',
  initialize: function(params){
    this.compareDashboard =  this.setCompareDashboard();
    this.getListingsFromCookie();
    var self = this;
  
    Object.each(this.listTypes, function(listType, key){  
      var type = 'lt'+key;        
      self.createTab(key,listType); 
      var  scrollContentList = $('ltList_'+type);    
      Object.each(self.listings[type], function(contentList, key){   
        self.setItem(scrollContentList,contentList);          
      });
      self.displayTabsContainer(type); 
      self.toggoleTabsContainer();
    });
    
    this.getHideCampareBarCookie();
  },
  setCompareDashboard : function(){
    if(this.compareDashboard) return;
    var self = this;
    this.compareDashboard =  new Element('div', {
      'id' : 'sr_compare_dashboard',
      'styles' :{
        'display' : 'block'
      }
    });
    this.compareHeader =  new Element('div', {
      'id' : 'sr_compare_header',
      'styles' :{
        'cursor' : 'pointer'
      }
    });
    this.compareHeaderDown =  new Element('div', {
      'id' : 'sr_compareArrow',
      'events' : {
        'click' : self.toggoleTabsContainer.bind(this)
      }
    }).inject(this.compareHeader);
    this.compareBarHide =  new Element('div', {
      'id' : 'sr_compareBarHide',
      'class':'sr_compareBarHide',
      'title':en4.core.language.translate('Hide Compare Bar'),
      'html':'',
      'events' : {
        'click' : self.toggoleCompareBar.bind(this)
      }
    }).inject(this.compareHeader);
    this.compareHeader.inject(this.compareDashboard);
    
    this.compareTabs =  new Element('div', {
      'id' : 'sr_compare_tabs',
      'class':'sr_ui_tabs'
    });
    
    this.compareTitle =  new Element('div', {
      'id' : 'sr_compareTitle',
      'html':en4.core.language.translate('Compare')
    }).inject(this.compareTabs);
    
    this.tabsNav = new Element('ul', {
      'class':'sr_tabsNav sr_ui_tabs_nav sr_ui_widget_header sr_uiCornerAll' ,
      'styles' : {
        'cursor' : 'pointer' 
      }
    }).inject(this.compareTabs)
    this.tabsContainer =  new Element('div', {
      'id' : 'sr_tabs_container'  
    }).inject(this.compareTabs);
     
    this.compareTabs.inject(this.compareDashboard);
    this.compareDashboard.inject(document.body);
    this.compareDashboardMin = new Element('div', {
      'id' : 'sr_compare_dashboard_min',
      'class':'sr_compare_dashboard_min',
      'title':en4.core.language.translate('Show Compare Bar'),
      'html':'<span class="fleft">'+en4.core.language.translate('Compare')+'</span><i></i>',
      'styles' :{
        'display' : 'none'
      },
      'events' : {
        'click' : self.toggoleCompareBar.bind(this)
      }
    });
    
    this.compareDashboardMin.inject(document.body);
  },
  toggoleCompareBar : function(event){
    // var el =  $(event.target);
    if(this.compareDashboardMin.style.display =='none'){
      $('sr_compare_dashboard').style.display = 'none'; 
      this.compareDashboardMin.style.display = 'block';
      this.setHideCampareBarCookie(1);
    }else{
      this.compareDashboardMin.style.display = 'none';
      $('sr_compare_dashboard').style.display = 'block';
      this.setHideCampareBarCookie(0);
    }
  },
  toggoleTabsContainer: function(){
    if(this.compareHeaderDown.hasClass("down")){
      this.tabsContainer.style.display = 'none';
      this.compareHeaderDown.removeClass('down');       
    }else {           
      this.tabsContainer.style.display = 'block';
      this.compareHeaderDown.addClass('down');
    }
  },
  setCampareItem: function(event){
  
    var el =  $(event.target);    
    var  list_id = el.get('value');
    var typeContent= $('listingID'+list_id);    
    var type_id =typeContent.get('class').substr(11);
    var type ="lt"+type_id;
    this.createTab(type_id,typeContent.innerHTML);


    var listUrl=$('listingUrl'+list_id).innerHTML;
    var listingImgSrc=$('listingImgSrc'+list_id).innerHTML;
    var title=el.get('name');
    
    if($('listing'+list_id)){
      $('listing'+list_id).destroy();  
    }
    var  scrollContentList = $('ltList_'+type);
    var contentList ={
      type :type,
      type_id : type_id,
      list_id :list_id,
      listTitile: title,
      listUrl: listUrl,
      imgSrc :listingImgSrc
    }
    this.setItem(scrollContentList,contentList);
    this.listings[type][list_id]=contentList;   
    this.setListingIntoCookie(type);
     
    this.displayTabsContainer(type);
  },
  setItem : function(scrollContentList , params){
    var self = this; 
    self.updateCompareButton(params.list_id, true);
    var scrollContentListItem = new Element('li', {
      'class':'ltItem' ,
      'id':'listing'+params.list_id,
      'styles' :{
        'display' : 'list-item'
      }      
    });
    
    var compareThumb= new Element('div', {
      'class': 'compareThumb'
    });
    
    
    var compareThumbLink= new Element('a', {    
      'href' : params.listUrl 
    });
    
    new Element('img', {
      'src':params.imgSrc  
    }).inject(compareThumbLink);
    
    compareThumbLink.inject(compareThumb);
    
    compareThumb.inject(scrollContentListItem);
    
    var compareItemTitle = new Element('span', {     
      'class': 'compareItemTitle'
    }).inject(scrollContentListItem);
    
    new Element('a', {    
      'href' : params.listUrl,
      'html' :params.listTitile  
      
    }).inject(compareItemTitle);
    new Element('span', {
      'html' :params.type_id,
      'id':'removelisting'+params.list_id,
      'class': 'removeItem',
      'events':{
        'click': self.removeListingFromComparison.bind(this)
      }
    }).inject(scrollContentListItem);
    compareItemTitle.inject(scrollContentListItem); 
    

  
    scrollContentListItem.inject(scrollContentList);
    var horizontalScroll = this.scrollCarousel[params.type];
    horizontalScroll.cacheElements(); 
    if(horizontalScroll.elements.length > horizontalScroll.options.noOfItemPerPage){
      horizontalScroll.toNext();
    }
    horizontalScroll.resetScroll();
    var count = scrollContentList.getElements("li").length;
    $('numSelected_'+params.type).innerHTML ='('+count+')';

  },
  createTab: function(type_id , title) {
  
    var type ='lt'+type_id; 
    if($('ltList_'+type))
      return;
   
    var self = this;
    var choice = new Element('li', {
      'class': 'sr_uiStateDefault',
      'id': 'sr_compareTab_'+type,
      'rel': type,
      'rev' :title,
      'events' : {
        'click' : self.displayTabsContainer.bind(this,type)
      } 
    });
    
    var link= new Element('a', {
      'html':title,
      'class': 'listingTypeCompare',
      'href' : 'javascript:void(0);'         
      
    });
 
    new Element('span', {        
      'class' : 'numSelected',
      'id':'numSelected_'+type,
      'html':'(1)'
    }).inject(link);
  
    link.inject(choice);    
    choice.inject(this.tabsNav);  
   
    var content= new Element('div', {
      'id' : 'tabContent_'+type,
      'class':'sr_tabsPanel sr_ui_tabs_panel sr_uiWidgetContent sr_uiCornerBottom'
    });
    
    var prevLink =  new Element('a', {  
      'id' : 'tabContentPreviousLink_'+type,
      'class': 'comparePrev compareBrowse compareLeft'
    }).inject(content);
    
    var scrollContent= new Element('div', {    
      'id' :'sr_compareScroll_'+type,
      'class': 'sr_compareScroll'     
    });
    
    var nextLink= new Element('a', {      
      'id' : 'tabContentNextLink_'+type,
      'class': 'compareNext compareBrowse compareRight'
    });
    var scrollContentList = new Element('ul', {
      'class':'ltList',
      'id' : 'ltList_'+type            
    });
      
    scrollContentList.inject(scrollContent);    
    scrollContent.inject(content);
    
    nextLink.inject(content);
    
    var rightSideContent= new Element('div', {
      'id' :'rightSideContent_'+type,
      'class': 'sr_compare_buttons'
    });
    
    new Element('button', {
      'html': en4.core.language.translate('Compare All'),
      'id' :'compare_all_'+type,
      'class': 'sr_button',  
      'events' : {
        'click' : self.compareAll.bind(this,type_id)
      }
    }).inject(rightSideContent);
    new Element('button', {
      'html':en4.core.language.translate('Remove All'),
      'class': 'sr_button',         
      'events' : {
        'click' : self.removeAll.bind(this,type)
      }
      
    }).inject(rightSideContent);
    
    rightSideContent.inject(content);  
    content.inject(this.tabsContainer); 

    this.scrollCarousel[type] = new Fx.Scroll.Carousel('sr_compareScroll_'+type,{
      mode: 'horizontal',
      navs:{
        frwd:'tabContentNextLink_'+type,
        prev:'tabContentPreviousLink_'+type
      }
    });
   


    this.listTypes[type_id]=title;
    if(typeOf(this.listings[type]) == 'null')
      this.listings[type]={};
  },
  displayTabsContainer:function(type){  
    this.compareHeaderDown.addClass('down');
    this.tabsContainer.style.display = 'block';

    this.tabsContainer.getElements('.sr_tabsPanel').setStyle('display','none');       
    $('tabContent_'+type).setStyle('display','block');
    
    $('sr_compare_tabs').getElements('ul > li').removeClass('sr_ui_tabs_selected sr_uiStateActive');  
    $('sr_compareTab_'+type).addClass('sr_ui_tabs_selected sr_uiStateActive');
    
  },
  compareAll: function(type_id){
    var type= "lt"+type_id;
    var compareCount= $('ltList_'+type).getElements("li").length;

    if (compareCount < 2) { 
      var el = $('compare_all_'+type); 
      var p_msg= new Element('p', {
        'html': en4.core.language.translate('Please select more than one entry for the comparison.'),        
        'class': 'comparisonMessage sr_tooltipBox'  // sr_uiTabsSelected             
      }).inject(el,'before');
     
      p_msg.fade('in');
      (function(){ 
        p_msg.fade('out');
        (function(){ 
          p_msg.destroy();
        }).delay(2000);
      }).delay(5000);
      
    }else{
      window.location.href = this.compareUrl+'/id/'+type_id+'/'+$('sr_compareTab_'+type).get('rev');  
    }
  },
  removeListingFromComparison : function(event){
   
    var el = $(event.target);   
    var type,list_id,li_el;
    if(el.hasClass('removeListing')){
      list_id= el.get('id').substr(18); 
      type_id = el.get('rel');
      type ="lt"+type_id;
    }else if(el.hasClass('checkListing') && el.get('tag') == 'input'){    
      list_id = el.get('value');     
      var typeContent= $('listingID'+list_id);    
      var type_id =typeContent.get('class').substr(11);
      type ="lt"+type_id;
    }else{
      type = "lt"+el.innerHTML;
      li_el= el.getParent('li');
      list_id = li_el.get('id').substring(7);
    }
    li_el = $('listing'+list_id);
    li_el.fade('out');
    
    var self = this;
    (function(){ 
      var count= $('ltList_'+type).getElements("li").length;
      if(count <=1){
        self.removeAll(type);
      }else{
        li_el.destroy();        
        count = $('ltList_'+type).getElements("li").length;
        $('numSelected_'+type).innerHTML ='('+count+')';
        delete self.listings[type][list_id];
        var horizontalScroll = self.scrollCarousel[type];
        horizontalScroll.cacheElements();
        horizontalScroll.resetScroll();
        if((horizontalScroll.elements.length > horizontalScroll.options.noOfItemPerPage && horizontalScroll.currentIndex > 0)|| horizontalScroll.currentIndex > 0){
          horizontalScroll.toPrevious();
        }
        if(horizontalScroll.currentIndex==0)
          horizontalScroll.resetScroll();
        self.setListingIntoCookie(type);      
      }    
    }).delay(500);
    self.updateCompareButton(list_id, false);
  },
  removeAll:function(type){
    var self = this;
    $('ltList_'+type).getElements("li").each(function(li_el){
      var list_id = li_el.get('id').substring(7);
      self.updateCompareButton(list_id, false);    
    });
    $("tabContent_"+type).destroy();
    var els= this.tabsNav.getElements("li");
    var tab = $("sr_compareTab_"+type);
  
    for(i=0; i< els.length; i++){      
      if(els[i].get('id') == tab.get('id')){
        break;
      }
    }
    tab.destroy();
    if(this.tabsNav.getElements("li").length <1){
      this.toggoleTabsContainer();
    }else{
      var nextType;
      if(i==0){
        nextType=els[1].get('rel');
      } else {
        i =i-1;
        nextType=els[i].get('rel');
      }
   
      this.displayTabsContainer(nextType);
    }
   
    delete this.listings[type]; 
    var type_id =type.substring(2);
    delete this.listTypes[type_id];   
    this.setListingIntoCookie(type);
  },
  getListingsFromCookie: function(){
    var cookiesSuffix='';
    if(en4.user.viewer.id){
      cookiesSuffix='_'+en4.user.viewer.id;
    }
    var listTypes= Cookie.read('srCompareListType'+cookiesSuffix);
   
    var lists = {};
    if(listTypes){
      listTypes = JSON.decode(listTypes);
    
      Object.each(listTypes, function(value, key){
        var type= 'lt'+key;       
        cookiesName='srCompareList'+type+cookiesSuffix;    
        var listings=Cookie.read(cookiesName);       
        if(listings){
          lists[type]  = JSON.decode(listings);       
        }
      });
      this.listTypes=listTypes;      
      this.listings=lists;
            
    }
  }, 
  setListingIntoCookie: function(type){
        
    var lists= this.listings[type];
    var cookiesSuffix='';
    var duration=1;
    if(en4.user.viewer.id){
      cookiesSuffix='_'+en4.user.viewer.id;
      duration=30;
    }
    cookiesName='srCompareList'+type+cookiesSuffix;
    Cookie.write(cookiesName, JSON.encode(lists), {
      duration: duration //save for a day
    }); 
    Cookie.write('srCompareListType'+cookiesSuffix, JSON.encode(this.listTypes), {
      duration: duration //save for a day
    }); 

  },
  getHideCampareBarCookie: function(){
    var cookiesSuffix='';
    if(en4.user.viewer.id){
      cookiesSuffix='_'+en4.user.viewer.id;
    }
    var falge= Cookie.read('srCompareBar'+cookiesSuffix);
    if(falge==1){
      this.toggoleCompareBar();     
    }
  }, 
  setHideCampareBarCookie : function(falge){
    var cookiesSuffix='';
    var duration=1;
    if(en4.user.viewer.id){
      cookiesSuffix='_'+en4.user.viewer.id;
      duration=30;
    }
    cookiesName='srCompareBar'+cookiesSuffix;
    Cookie.write(cookiesName, falge, {
      duration: duration //save for a day
    }); 
  },
  compareButtonEvent:function(event){
    var el =  $(event.target);
    if(el.checked){
      this.setCampareItem(event);
    }else{
      this.removeListingFromComparison(event);
    }
  },
  updateCompareButton : function(list_id, checked){
    $$('.compareButtonListing'+list_id).each(function(element){
      element.checked = checked;
    });
  },
  updateCompareButtons:function(){
    var self = this;  
    Object.each(this.listTypes, function(listType, key){  
      var type = 'lt'+key;
      Object.each(self.listings[type], function(contentList){  
        self.updateCompareButton(contentList.list_id, true);    
      });   
    });
  }
});

en4.core.runonce.add(function() {
  compareSiterivewContent  = new compareSiterivew();
  compareSiterivewContent.compareUrl=en4.core.baseUrl+'compare';
});



/*
---

script: Fx.Scroll.Carousel.js

description: Extends Fx.Scroll to work like a carousel

license: MIT-style license.

authors: Ryan Florence

docs: http://moodocs.net/rpflo/mootools-rpflo/Fx.Scroll.Carousel

requires:
- more/1.2.4.2: [Fx.Scroll]

provides: [Fx.Scroll.Carousel]

...
   */


Fx.Scroll.Carousel = new Class({
	
  Extends: Fx.Scroll,
	
  options: {
    mode: 'horizontal',
    childSelector: false,
    loopOnScrollEnd: true,
    noOfItemPerPage: 4,
    noOfItemScroll:1,
    navs:{
      frwd:'sr_crousal_frwd',
      prev:'sr_crousal_prev'
    }
  },
	
  initialize: function(element, options){
    this.parent(element, options);
    this.cacheElements();
    this.currentIndex = 0;
    this.resetScroll();
    var self=this;
    $(this.options.navs.frwd).addEvent('click', function(){
      self.toNext();  
      self.resetScroll();
    });
	
    $(this.options.navs.prev).addEvent('click', function(){
      self.toPrevious();
      self.resetScroll();
    });
  },
	
  cacheElements: function(){
    var cs = this.options.childSelector;
    if(cs){
      els = this.element.getElements(cs);
    } else if (this.options.mode == 'horizontal'){
      els = this.element.getElements('.ltItem');
    } else {
      els = this.element.getChildren();
    }
    this.elements = els;
  
    return this;
  },
	
  toNext: function(){
    if(this.checkLink()) return this;
    this.currentIndex = this.getNextIndex();
    this.toElement(this.elements[this.currentIndex]);
    this.fireEvent('next');
    return this;
  },
	
  toPrevious: function(){
    if(this.checkLink()) return this;
    this.currentIndex = this.getPreviousIndex();
    this.toElement(this.elements[this.currentIndex]);
    this.fireEvent('previous');
    return this;
  },
	
  getNextIndex: function(){
    //this.currentIndex++;
    this.currentIndex = this.currentIndex + this.options.noOfItemScroll;
    if(this.currentIndex == this.elements.length || this.checkScroll()){
      this.fireEvent('loop');
      this.fireEvent('nextLoop');
      return 0;
    } else {
      return this.currentIndex;
    }
  },
	
  getPreviousIndex: function(){
    //this.currentIndex--;
    this.currentIndex = this.currentIndex- this.options.noOfItemScroll;   
    var check = this.checkScroll();
    if(this.currentIndex < 0 || check) {
      this.fireEvent('loop');
      this.fireEvent('previousLoop');
      return (check) ? this.getOffsetIndex() : this.elements.length - 1;
    } else {
      return this.currentIndex;
    }
  },
	
  getOffsetIndex: function(){   
    var visible = (this.options.mode == 'horizontal') ? 
    this.element.getStyle('width').toInt() / this.elements[0].getStyle('width').toInt() :
    this.element.getStyle('height').toInt() / this.elements[0].getStyle('height').toInt();
    return this.currentIndex + 1 - visible;
  },
	
  checkLink: function(){
    return (this.timer && this.options.link == 'ignore');
  },
	
  checkScroll: function(){
    if(!this.options.loopOnScrollEnd) return false;
    if(this.options.mode == 'horizontal'){
      var scroll = this.element.getScroll().x;
      var total = this.element.getScrollSize().x - this.element.getSize().x;
    } else {
      var scroll = this.element.getScroll().y;
      var total = this.element.getScrollSize().y - this.element.getSize().y;
    }
    return (scroll == total);
  },
	
  getCurrent: function(){
    return this.elements[this.currentIndex];
  },
  resetScroll:function(){
    if(this.elements.length <= this.options.noOfItemPerPage){
      $(this.options.navs.frwd).style.visibility = 'hidden';
      $(this.options.navs.prev).style.visibility = 'hidden';
    }else{
      var visibleflag='visible';
      if(this.currentIndex == 0 || this.elements.length <= this.options.noOfItemPerPage ){
        visibleflag = 'hidden';
      }
      $(this.options.navs.prev).style.visibility = visibleflag;
      visibleflag='visible';
      if(((this.currentIndex + this.options.noOfItemPerPage) >= this.elements.length)  ){
        visibleflag = 'hidden';
      }
      $(this.options.navs.frwd).style.visibility = visibleflag;
    }
  }
	
});

/**
 * @description dropdown Navigation
 * @param {String} id id of ul element with navigation lists
 * @param {Object} settings object with settings
 */


var NavigationSitereview = function() {
  var main = { 
    obj_nav : $(arguments[0]) || $("nav"),
    settings : {
      show_delay : 0,
      hide_delay : 0,
      _ie6 : /MSIE 6.+Win/.test(navigator.userAgent),
      _ie7 : /MSIE 7.+Win/.test(navigator.userAgent)
    },
    init : function(obj, level) {
      obj.lists = obj.getChildren();
      obj.lists.each(function(el,ind){
        main.handlNavElement(el);
        if((main.settings._ie6 || main.settings._ie7) && level){
          main.ieFixZIndex(el, ind, obj.lists.size());
        }
      });
      if(main.settings._ie6 && !level){
        document.execCommand("BackgroundImageCache", false, true);
      }
    },
    handlNavElement : function(list) {
      if(list !== undefined){
        list.onmouseover = function(){
          main.fireNavEvent(this,true);
        };
        list.onmouseout = function(){
          main.fireNavEvent(this,false);
        };
        if(list.getElement("ul")){
          main.init(list.getElement("ul"), true);
        }
      }
    },
    ieFixZIndex : function(el, i, l) {
      if(el.tagName.toString().toLowerCase().indexOf("iframe") == -1){
        el.style.zIndex = l - i;
      } else {
        el.onmouseover = "null";
        el.onmouseout = "null";
      }
    },
    fireNavEvent : function(elm,ev) {
      if(ev){
        elm.addClass("over");
        elm.getElement("a").addClass("over");
        if (elm.getChildren()[1]) {
          main.show(elm.getChildren()[1]);
        }
      } else {
        elm.removeClass("over");
        elm.getElement("a").removeClass("over");
        if (elm.getChildren()[1]) {
          main.hide(elm.getChildren()[1]);
        }
      }
    },
    show : function (sub_elm) {
      if (sub_elm.hide_time_id) {
        clearTimeout(sub_elm.hide_time_id);
      }
      sub_elm.show_time_id = setTimeout(function() {
        if (!sub_elm.hasClass("shown-sublist")) {
          sub_elm.addClass("shown-sublist");
        }
      }, main.settings.show_delay);
    },
    hide : function (sub_elm) {
      if (sub_elm.show_time_id) {
        clearTimeout(sub_elm.show_time_id);
      }
      sub_elm.hide_time_id = setTimeout(function(){
        if (sub_elm.hasClass("shown-sublist")) {
          sub_elm.removeClass("shown-sublist");
        }
      }, main.settings.hide_delay);
    }
  };
  if (arguments[1]) {
    main.settings = Object.extend(main.settings, arguments[1]);
  }
  if (main.obj_nav) {
    main.init(main.obj_nav, false);
  }
};

function removeAdsWidget(widgetIdentity) {
  en4.core.request.send(new Request.JSON({
    url : en4.core.baseUrl+'sitereview/index/remove-ads-widget',
    data : {
      content_id : widgetIdentity,
      format : 'json'
    },
    onSuccess : function(responseJSON) {
      $('sitereview_ads_plugin_'+widgetIdentity).destroy();
      if($$(".tab_"+widgetIdentity)) {
        $$(".tab_"+widgetIdentity).destroy();
      }
    } 
  }));
}
