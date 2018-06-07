<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Activity.php 9799 2012-10-16 22:11:00Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_View_Helper_EditActivity extends Zend_View_Helper_Abstract
{
  protected $_composePartials = array();

  public function editActivity($action)
  {
    if( !$action->canEdit() ) {
      return;
    }
    $form = new Activity_Form_EditPost();
    $atcionValues = $action->toArray();
    $params = array_merge(
      $atcionValues, (array) $action->params, array(
      'action' => $action,
      'subject' => $action->getSubject(),
      'object' => $action->getObject()
    ));
    $params['body'] = '';
    $content = Engine_Api::_()->getApi('core', 'activity')
      ->assemble($action->getTypeInfo()->body, $params);
    $form->body->setAttrib('id', 'feed-edit-body-' . $action->getIdentity());
    $form->populate($atcionValues);
    return $this->view->partial(
        '_editPost.tpl', 'activity', array(
        'action' => $action,
        'form' => $form,
        'content' => $content,
        'composePartials' => $this->getComposePartials(),
        )
    );
  }

  private function getComposePartials()
  {
    if( $this->_composePartials ) {
      return $this->_composePartials;
    }
    // Assign the composing values
    $composePartials = array();
    foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
      if( empty($data['composer']) ) {
        continue;
      }
      foreach( $data['composer'] as $config ) {
        if( empty($config['allowEdit']) ) {
          continue;
        }
        if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
          continue;
        }
        $composePartials[] = $config['script'];
      }
    }
    $this->_composePartials = $composePartials;
    return $this->_composePartials;
  }
}
