
/* $Id: composer_photo.js 9572 2011-12-27 23:41:06Z john $ */

(function() { // START NAMESPACE
  var $ = 'id' in document ? document.id : window.$;



  Composer.Plugin.AddFriendTag = new Class({

    Extends : Composer.Plugin.Interface,

    name : 'addfriendtag',

    options : {
      title : 'Add People',
      lang : {}
    }, 
    add_friend_suggest: false,
    add_friend:false,
    tag_ids:'',
    initialize : function(options) {
      this.elements = new Hash(this.elements);
      this.params = new Hash(this.params);
      this.parent(options);
    },
    
    attach : function() {
      var self = this;
      if(!this.elements.link){
        var composer=this.getComposer(); 
        var addLinkBefore=composer.getMenu().getElement(".aaf_cm_sep");
        this.elements.link = new Element('a', {
          'id' : 'compose-' + this.getName() + '-activator',
          'class' : 'compose-activator buttonlink aaf_st_enable',
          'href' : 'javascript:void(0);',
          'html' : this._lang(this.options.title),
          'events' : {
            'click' : this.toggleEvent.bind(this)
          }
        }).inject(addLinkBefore,'before');   
        
        var width = composer.elements.body.getSize().x;
        this.elements.suggestContainer= new Element('div', {
          'class' :'add_friend_suggest_container aaf_disable',
          'styles' : {
            'width' : (width-10 )+ 'px'
          }
        }).inject(this.elements.link,'after');  
        this.elements.suggestContainerSearchDiv= new Element('div', {          
          }).inject(this.elements.suggestContainer);  
        new Element('p', {
          'class':'label',
          'html' : this._lang('Enter the user name')
        }).inject(this.elements.suggestContainerSearchDiv);
        this.elements.searchText= new Element('input', {
          'type':'text',
          'id' :'aff_mobile_aft_search',
          'autocomplete' : 'off'
        }).inject(this.elements.suggestContainerSearchDiv);
        
        new Element('button', {
          'class' :'',
          'type': 'button',
          'html' : this._lang('Search'),
          'events' : {
            'click' : this.search.bind(this)
          }
        }).inject(this.elements.suggestContainerSearchDiv);
        
        new Element('a', {        
          'class':'aaf-add-friend-close',
          'href' : 'javascript:void(0);',
          'html' : this._lang('Close'),
          'events' : {
            'click' : this.toggleEvent.bind(this)
          }
        }).inject(this.elements.suggestContainerSearchDiv);
        
        this.elements.suggestContainerSearchListDiv= new Element('div', {          
          }).inject(this.elements.suggestContainer); 
        
        this.elements.loading = new Element('div', {
          'class' :'add_friend_suggest_container_loading',
          'html' : '<img src="application/modules/Seaocore/externals/images/core/loading.gif" alt="Loading" style="margin-top:10px;" />'    
        });
        // Submit
        composer.addEvent('editorSubmit', this.submit.bind(this));       
       
      }
         
      
      return this;
    },

    detach : function() {
      //   this.parent();
      return this;
    },
    toggleEvent :function(){
      $$(".stchekin_suggest_container").each(function(el){
        if(el.hasClass("dblock")){
          el.toggleClass('dnone');
          el.toggleClass('dblock'); 
        }
      });
      
      this.elements.suggestContainer.toggleClass('aaf_disable');
      this.elements.suggestContainer.toggleClass('aaf_enable');
      
      if(this.elements.suggestContainer.hasClass("aaf_enable")){
        if(this.elements.searchText)
          this.elements.searchText.value = '';       
        this.getFriends();
      }
    },
    loading :function(){
      this.elements.suggestContainerSearchListDiv.empty();     
      this.elements.loading.inject(this.elements.suggestContainerSearchListDiv); 
    },
    getFriends :function(params) {
      var self=this;      
      this.loading();
      var req = new Request.HTML({
        url : en4.core.baseUrl + 'advancedactivity/friends/suggest-mobile',
        data :$merge(params, {
          'format' : 'html',
           'subject' : en4.core.subject.guid
         
        }),
        onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
          self.elements.suggestContainerSearchListDiv.innerHTML =responseHTML;
          $$(".aaf_mobile_add_tag").each(function(el){
            el.addEvent('click', self.addTag.bind(self));
          }); 
          $$(".aff_list_pagination").each(function(el){
            el.addEvent('click', self.searchLink.bind(self));
          });
          $$(".aff_list_pagination_select").each(function(el){
            el.addEvent('change', self.searchSelect.bind(self));
          });
          
        }

      });
      en4.core.request.send(req);
      
    },
    search : function(){    
      this.getFriends({
        'page' : 1,
        'search': this.elements.searchText.value
      });
    },
    searchLink : function(event){
      var el = event.target;     
      this.getFriends({
        'page': el.get("rev"),
        'search': this.elements.searchText.value
      });
    },
    searchSelect : function(event){
      var el = event.target;
      this.getFriends({
        'page': el.value,
        'search': this.elements.searchText.value
      });      
    },
    
    addTag : function(event){
      var el = event.target;
      var id = el.get("rel");
      var label = el.get("rev");
      var self = this;
      
      if(this.tag_ids ==""){
        this.elements.tagcontainer = new Element('div', {
          'class':'aaf-add-friend-tagcontainer'
        });
        var tagspan = new Element('span', {        
          'class' : 'aff-tag-with',
          'html':this._lang('with:')
        }).inject(this.elements.tagcontainer);      
        this.elements.tagcontainer.inject(this.getComposer().getMenu(),'before');
        this.tag_ids = id;
      }else {
        if(this.hasTagged(id))
          return;
        this.tag_ids = this.tag_ids+','+id;
      }
      
      var tagspan = new Element('span', {        
        'class' : 'tag',
        'html':label
      });
     
      new Element('a', {
        'html':'X',
        'rel':id,
        'href' : 'javascript:void(0);',         
        'events' : {
          'click' : self.removeTag.bind(this)
        }
      }).inject(tagspan);
      
      tagspan.inject(this.elements.tagcontainer);      
    },
    removeTag : function(event){
      var el = event.target;
      var id = el.get("rel");
      if(this.hasTagged(id)){
        el.getParent().destroy();
        var toValueArray = this.tag_ids.split(",");
        var toValueIndex=0;
        for (var i = 0; i < toValueArray.length; i++){
          if (toValueArray[i]==id) toValueIndex =i;
        }
   
        toValueArray.splice(toValueIndex, 1);
        
        if(toValueArray.length >0){
          this.tag_ids = toValueArray.join();
        }else{
          this.tag_ids='';
          this.elements.tagcontainer.destroy();
        }
      }
    },
    hasTagged : function(id){
      
      var toValueArray = this.tag_ids.split(",");
      var hasTagged=false;
      for (var i = 0; i < toValueArray.length; i++){
        if (toValueArray[i]==id) {
          hasTagged = true;
          break;          
        }
      }
      return hasTagged;
    },
    submit :function(){
     
      this.makeFormInputs({
        toValues: this.tag_ids

      });           
    },
 
    makeFormInputs : function(data) {    
      $H(data).each(function(value, key) {
        this.setFormInputValue(key, value);
      }.bind(this));
    },
    // make tag hidden input and set value into composer form
    setFormInputValue : function(key, value) {
     
      var elName = 'aafComposerForm' + key.capitalize();
      var composerObj=this.getComposer();
      if( !composerObj.elements.has(elName) ) {     
        composerObj.elements.set(elName, new Element('input', {
          'type' : 'hidden',
          'name' :  key,
          'value' : value || ''
        }).inject(composerObj.getInputArea()));
      }
      composerObj.elements.get(elName).value = value;
    }
  });

  
})(); // END NAMESPACE
