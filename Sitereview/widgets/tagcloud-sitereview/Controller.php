<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_TagcloudSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->loaded_by_ajax = $is_ajax_load = $this->_getParam('loaded_by_ajax', true);
    $this->view->isajax = $isajax = $this->_getParam('isajax', false);
    $this->view->allParams = $allParams  = array('isajax' => 1, 'loaded_by_ajax' => 1);       
      
    if (Engine_Api::_()->core()->hasSubject('sitereview_listing')) {       

      //GET SUBJECT
      $sitereview = Engine_Api::_()->core()->getSubject();

      //GET OWNER INFORMATION
      $this->view->owner_id = $owner_id = $sitereview->owner_id;
      $this->view->owner = $sitereview->getOwner();

      //GET LISTING TYPE ID
      $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    } else {
      $this->view->owner_id = $owner_id = 0;

      //GET LISTING TYPE ID
      $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id');
      if (empty($listingtype_id)) {
        $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
      }
    }

    if (!empty($listingtype_id)) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $this->view->listing_singular_upper = strtoupper(Zend_Registry::get('listingtypeArray' . $listingtype_id)->title_singular);
    } else {
      return $this->setNoRender();
    }

    //HOW MANY TAGS WE HAVE TO SHOW
    $total_tags = $this->_getParam('itemCount', 25);

    $element = $this->getElement();
    if(strstr($element->getTitle(), '%s')) {
        if ($this->view->owner_id == 0) {
          $count_only = Engine_Api::_()->sitereview()->getTags($owner_id, 0, 1, $listingtype_id);  
          $element->setTitle(sprintf($element->getTitle(), (int) $count_only));
        } else {
          $element->setTitle(sprintf($element->getTitle(), $this->view->owner->getTitle()));
        }    
    }    

    //FETCH TAGS
    if ((!$is_ajax_load || ($is_ajax_load && $isajax)) || !Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        $tag_array = array();
        $tag_cloud_array = Engine_Api::_()->sitereview()->getTags($owner_id, $total_tags, 0, $listingtype_id);

        foreach ($tag_cloud_array as $vales) {
          $tag_array[$vales['text']] = $vales['Frequency'];
          $tag_id_array[$vales['text']] = $vales['tag_id'];
        }

        if (!empty($tag_array)) {
          $max_font_size = 18;
          $min_font_size = 12;
          $max_frequency = max(array_values($tag_array));
          $min_frequency = min(array_values($tag_array));
          $spread = $max_frequency - $min_frequency;
          if ($spread == 0) {
            $spread = 1;
          }
          $step = ($max_font_size - $min_font_size) / ($spread);

          $tag_data = array('min_font_size' => $min_font_size, 'max_font_size' => $max_font_size, 'max_frequency' => $max_frequency, 'min_frequency' => $min_frequency, 'step' => $step);
          $this->view->tag_data = $tag_data;
          $this->view->tag_id_array = $tag_id_array;
        }
        $this->view->tag_array = $tag_array;

        if (empty($this->view->tag_array)) {
          return $this->setNoRender();
        }
        
        $this->view->showcontent = true;
        if($isajax) {        
          $this->getElement()->removeDecorator('Container');
        }        
    }
  }

}