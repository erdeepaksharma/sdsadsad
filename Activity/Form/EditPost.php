<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Post.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Form_EditPost extends Engine_Form
{
  public function init()
  {

    $this
      ->setMethod('POST')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'activity', 'controller' => 'index', 'action' => 'edit'), 'default', true))
      ->setAttrib('class', 'global_form_activity_edit_post')
    ;

    $this->addElement('Textarea', 'body', array(
      'attribs' => array('rows' => 3),
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_Html(array('AllowedTags' => 'br')),
      ),
    ));
    $this->addElement('hidden', 'action_id');

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Edit Post',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'class' => 'feed-edit-content-cancel',
      'href' => 'javascript:void(0);',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
