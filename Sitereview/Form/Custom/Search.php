<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Search.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Custom_Search extends Fields_Form_Search {

  protected $_type;

  public function setType($type) {
    $this->_type = $type;
    return $this;
  }

  public function init() {
    $this->addDecorators(array(
        'FormElements'
    ));

    $fields = Engine_Api::_()->getApi('core', 'fields')->getFieldsMeta($this->_type);
    foreach ($fields as $field) {
      if (!$field->search || !$field->alias) {
        continue;
      }
      $key = $field->alias;

      $params = $field->getElementParams($this->_type, array('required' => false));

      if ($field->type == 'date' || $field->type == 'birthdate' || $field->type == 'float') {
        $subform = new Engine_Form(array(
                    'description' => $params['options']['label'],
                    'elementsBelongTo' => $key,
                    'decorators' => array(
                        'FormElements',
                        array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
                        array('HtmlTag', array('tag' => 'div', 'class' => 'integer_field form-wrapper integer_field_unselected', 'id' => 'integer-wrapper'))
                    )
                ));

        unset($params['options']['label']);
        $params['options']['decorators'] = array('ViewHelper', array('HtmlTag', array('tag' => 'div', 'class' => 'form-element')));

        $subform->addElement($params['type'], 'min', $params['options']);
        $subform->addElement($params['type'], 'max', $params['options']);
        $this->addSubForm($subform, $key);
      } else {
        $this->addElement($params['type'], $key, $params['options']);
      }

      $element = $this->getElement($key);
    }

    $this->addElement('Button', 'done', array(
        'label' => 'Search',
        'type' => 'submit',
    ));
  }

}