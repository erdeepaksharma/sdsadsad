<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Dayitem.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Settings_Dayitem extends Engine_Form {

  public function init() {

    $this->setMethod('post');
    $this->setTitle('Listing of the Day')
         ->setDescription('Displays Listing of the day as selected by you from below. You can use this widget to highlight any Listing posted at your site using the auto-suggest box below.');
    
    $this->addElement('Select', 'ratingType', array(
        'label' => 'Rating Type',
        'multiOptions' => array('rating_avg' => 'Avg Rating', 'rating_editor' => 'Editor Rating', 'rating_users' => 'User Rating', 'rating_both' => 'Both User and Editor Rating')
    ));

    //VALUE FOR BORDER COLOR.
    $this->addElement('Text', 'listing_title', array(
        'label' => 'Listing',
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => '/application/modules/Sitereview/views/scripts/admin-settings/add-day-item.tpl',
                    'class' => 'form element')))
    ));

    $this->addElement('text', 'listing_id', array());

    // Start time
    $start = new Engine_Form_Element_CalendarDateTime('starttime');
    $start->setLabel("Start Time");
    $start->setAllowEmpty(false);
    $this->addElement($start);
    
    // End time
    $end = new Engine_Form_Element_CalendarDateTime('endtime');
    $end->setLabel("End Time");
    $end->setAllowEmpty(false);
    $this->addElement($end);
    
    //SHOW PREFIELD START AND END DATETIME
    $httpReferer = $_SERVER['HTTP_REFERER'];
    if(!empty($httpReferer) && strstr($httpReferer,'?page=')) { 
      $httpRefererArray = explode('?page=', $httpReferer);
      $page_id = (int) $httpRefererArray['1'];
      if(!empty($page_id) && is_numeric($page_id)) {
        
        //GET CONTENT TABLE
        $tableContent = Engine_Api::_()->getDbtable('content', 'core');
        $tableContentName = $tableContent->info('name');

        //GET CONTENT
        $params = $tableContent->select()
                ->from($tableContentName, array('params'))
                ->where('page_id = ?', $page_id)
                ->where('name = ?', 'sitereview.item-sitereview')
                ->query()
                ->fetchColumn();      
        
        if(!empty($params)) {
          $params = Zend_Json_Decoder::decode($params);
          if(isset($params['starttime']) && !empty($params['starttime'])) {
            $start->setValue($params['starttime']);
          }

          if(isset($params['endtime']) && !empty($params['endtime'])) {
            $end->setValue($params['endtime']);
          }
        }
      }
    }    

    //$this->addElement('Hidden', 'nomobile', array());
  }

}
