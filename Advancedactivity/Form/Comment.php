<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Comment.php 9800 2012-10-17 01:16:09Z richard $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Advancedactivity_Form_Comment extends Activity_Form_Comment
{
  public function init()
  {
    //$allowed_html = Engine_Api::_()->getApi('settings', 'core')->core_general_commenthtml;
    //$viewer = Engine_Api::_()->user()->getViewer();
    $this->addElement('Dummy', 'user_photo', array(
      //'description' => "Schedule your post",
      'decorators' => array(array('ViewScript', array(
            'viewScript' => 'application/modules/Advancedactivity/views/scripts/_formCommentUser.tpl',
            'class' => 'form element')))
    ));
    parent::init();
  }

}
