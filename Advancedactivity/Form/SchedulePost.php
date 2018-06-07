<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: SchedulePost.php 8968 2011-06-02 00:48:35Z john $
 * @author     John
 */
class Advancedactivity_Form_SchedulePost extends Engine_Form
{
  protected $_elementName;
  public function getItem()
  {
    return $this->_elementName;
  }

  public function setItem($item)
  {
    $this->_elementName = $item;
    return $this;
  }
  public function init()
  {

      $this->addElement('Dummy', 'schedule_post_description', array(
            'description' => "Schedule your post",
        ));
   // $dateInfo = Engine_Api::_()->advancedactivity()->dbToUserDateTime(array('starttime'=>date('Y-m-d H:i:s')));
    $schedule_time = new Engine_Form_Element_CalendarDateTime($this->_elementName);
  //  $schedule_time->setValue($dateInfo['starttime']);
   // $schedule_time->setAllowEmpty(true);
    $schedule_time->getDecorator("Description")->setOption("placement", "append");
    $this->addElement($schedule_time);
  }

}
