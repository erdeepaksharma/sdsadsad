<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminFieldsReviewController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminFieldsReviewController extends Fields_Controller_AdminAbstract {

  protected $_fieldType = 'sitereview_review';
  protected $_requireProfileType = true;

  //MANAGE PROFILE FIELDS
  public function indexAction() {

    //Make navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_review');

    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_reviewmain', array(), 'sitereview_admin_reviewmain_fields');

    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
  }

  public function fieldCreateAction() {

    if ($this->_requireProfileType || $this->_getParam('option_id')) {
      $option = Engine_Api::_()->fields()->getOption($this->_getParam('option_id'), $this->_fieldType);
    } else {
      $option = null;
    }

    // Check type param and get form class
    $cfType = $this->_getParam('type');
    $adminFormClass = null;
    if (!empty($cfType)) {
      $adminFormClass = Engine_Api::_()->fields()->getFieldInfo($cfType, 'adminFormClass');
    }
    if (empty($adminFormClass) || !@class_exists($adminFormClass)) {
      $adminFormClass = 'Fields_Form_Admin_Field';
    }

    // Create form
    $this->view->form = $form = new $adminFormClass();

    //START CUSTOMIZATION BY SOCIALENGINEADDONS
    $form->setTitle('Add Review Question');
    $form->removeElement('show');
    $form->addElement('hidden', 'show', array('value' => 0));

    $display = $form->getElement('display');
    $display->setLabel('Show on review page?');
    $display->setOptions(array('multiOptions' => array(
            1 => 'Show on review page',
            0 => 'Hide on review page'
            )));

    $search = $form->getElement('search');
    $search->setLabel('Show on the search options?');
    $search->setOptions(array('multiOptions' => array(
            0 => 'Hide on the search options',
            1 => 'Show on the search options'
            )));
    //END CUSTOMIZATION BY SOCIALENGINEADDONS
    // Create alt form
    $this->view->formAlt = $formAlt = new Fields_Form_Admin_Map();
    $formAlt->setAction($this->view->url(array('action' => 'map-create')));

    // Get field data for auto-suggestion
    $fieldMaps = Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType);
    $fieldList = array();
    $fieldData = array();
    foreach (Engine_Api::_()->fields()->getFieldsMeta($this->_fieldType) as $field) {
      if ($field->type == 'profile_type')
        continue;

      // Ignore fields in the same category as we have selected
      foreach ($fieldMaps as $map) {
        if ((!$option || !$map->option_id || $option->option_id == $map->option_id ) && $field->field_id == $map->child_id) {
          continue 2;
        }
      }

      // Add
      $fieldList[] = $field;
      $fieldData[$field->field_id] = $field->label;
    }
    $this->view->fieldList = $fieldList;
    $this->view->fieldData = $fieldData;

    if (count($fieldData) < 1) {
      $this->view->formAlt = null;
    } else {
      $formAlt->getElement('field_id')->setMultiOptions($fieldData);
    }

    // Check method/data
    if (!$this->getRequest()->isPost()) {
      $form->populate($this->_getAllParams());
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $field = Engine_Api::_()->fields()->createField($this->_fieldType, array_merge(array(
                'option_id' => ( is_object($option) ? $option->option_id : '0' ),
                    ), $form->getValues()));

    // Should get linked in field creation
    //$fieldMap = Engine_Api::_()->fields()->createMap($field, $option);

    $this->view->status = true;
    $this->view->field = $field->toArray();
    $this->view->option = is_object($option) ? $option->toArray() : array('option_id' => '0');
    $this->view->form = null;

    // Re-render all maps that have this field as a parent or child
    $maps = array_merge(
            Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('field_id', $field->field_id), Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('child_id', $field->field_id)
    );
    $html = array();
    foreach ($maps as $map) {
      $html[$map->getKey()] = $this->view->adminFieldMeta($map);
    }
    $this->view->htmlArr = $html;
  }

  public function fieldEditAction() {
    $field = Engine_Api::_()->fields()->getField($this->_getParam('field_id'), $this->_fieldType);

    // Check type param and get form class
    $cfType = $this->_getParam('type', $field->type);
    $adminFormClass = null;
    if (!empty($cfType)) {
      $adminFormClass = Engine_Api::_()->fields()->getFieldInfo($cfType, 'adminFormClass');
    }

    if (empty($adminFormClass) || !@class_exists($adminFormClass)) {
      $adminFormClass = 'Fields_Form_Admin_Field';
    }

    // Create form
    $this->view->form = $form = new $adminFormClass();
    $form->setTitle('Edit Profile Question');

    //START CUSTOMIZATION BY SOCIALENGINEADDONS
    $form->setTitle('Edit Review Question');
    $form->removeElement('show');
    $form->addElement('hidden', 'show', array('value' => 0));

    $display = $form->getElement('display');
    $display->setLabel('Show on review page?');
    $display->setOptions(array('multiOptions' => array(
            1 => 'Show on review page',
            0 => 'Hide on review page'
            )));

    $search = $form->getElement('search');
    $search->setLabel('Show on the search options?');
    $search->setOptions(array('multiOptions' => array(
            0 => 'Hide on the search options',
            1 => 'Show on the search options'
            )));
    //END CUSTOMIZATION BY SOCIALENGINEADDONS
    // Get sync notice
    $linkCount = count(Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)
                    ->getRowsMatching('child_id', $field->field_id));
    if ($linkCount >= 2) {
      $form->addNotice($this->view->translate(array(
                  'This question is synced. Changes you make here will be applied in %1$s other place.',
                  'This question is synced. Changes you make here will be applied in %1$s other places.',
                  $linkCount - 1), $this->view->locale()->toNumber($linkCount - 1)));
    }

    // Check method/data
    if (!$this->getRequest()->isPost()) {
      $form->populate($field->toArray());
      $form->populate($this->_getAllParams());
      if (is_array($field->config)) {
        $form->populate($field->config);
      }
      $this->view->search = $field->search;
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    Engine_Api::_()->fields()->editField($this->_fieldType, $field, $form->getValues());

    $this->view->status = true;
    $this->view->field = $field->toArray();
    $this->view->form = null;

    // Re-render all maps that have this field as a parent or child
    $maps = array_merge(
            Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('field_id', $field->field_id), Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType)->getRowsMatching('child_id', $field->field_id)
    );
    $html = array();
    foreach ($maps as $map) {
      $html[$map->getKey()] = $this->view->adminFieldMeta($map);
    }
    $this->view->htmlArr = $html;
  }

  //ACTION FOR HEADING CREATION
  public function headingCreateAction() {
    parent::headingCreateAction();

    //GENERATE FORM
    $form = $this->view->form;

    if ($form) {
      $form->removeElement('show');
      $form->addElement('hidden', 'show', array('value' => 0, 'order' => '1000'));

      $form->removeElement('display');
      $form->addElement('hidden', 'display', array('value' => 1, 'order' => '1001'));
    }
  }

  //ACTION FOR HEADING EDITION
  public function headingEditAction() {
    parent::headingEditAction();

    //GENERATE FORM
    $form = $this->view->form;

    if ($form) {
      $form->removeElement('show');
      $form->addElement('hidden', 'show', array('value' => 0, 'order' => '1000'));

      $form->removeElement('display');
      $form->addElement('hidden', 'display', array('value' => 1, 'order' => '1001'));
    }
  }

  public function typeCreateAction() {
    $field = Engine_Api::_()->fields()->getField($this->_getParam('field_id'), $this->_fieldType);

    // Validate input
    if ($field->type !== 'profile_type') {
      throw new Exception(sprintf('invalid input, type is "%s", expected "profile_type"', $field->type));
    }

    // Create form
    $this->view->form = $form = new Sitereview_Form_Admin_Typereview();

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Create New Profile Type from Duplicate of Existing
    if ($form->getValue('duplicate') != 'null') {
      // Create New Option in engine4_sitereview_review_fields_options
      $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
          'field_id' => $field->field_id,
          'label' => $form->getValue('label'),
              ));
      // Get New Option ID
      $db = Engine_Db_Table::getDefaultAdapter();
      $new_option_id = $db->select('option_id')
              ->from('engine4_sitereview_review_fields_options')
              ->where('label = ?', $form->getValue('label'))
              ->query()
              ->fetchColumn();
      // Get list of Field IDs From Duplicated member Type
      $field_map_array = $db->select()
              ->from('engine4_sitereview_review_fields_maps')
              ->where('option_id = ?', $form->getValue('duplicate'))
              ->query()
              ->fetchAll();

      $field_map_array_count = count($field_map_array);
      // Check if the Member type is blank
      if ($field_map_array_count == 0) {
        // Create new blank option
        $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
            'field_id' => $field->field_id,
            'label' => $form->getValue('label'),
                ));
        $this->view->option = $option->toArray();
        $this->view->form = null;
        return;
      }

      for ($c = 0; $c < $field_map_array_count; $c++) {
        $child_id_array[] = $field_map_array[$c]['child_id'];
      }
      unset($c);

      $field_meta_array = $db->select()
              ->from('engine4_sitereview_review_fields_meta')
              ->where('field_id IN (' . implode(', ', $child_id_array) . ')')
              ->query()
              ->fetchAll();

      // Copy each row
      for ($c = 0; $c < $field_map_array_count; $c++) {
        $db->insert('engine4_sitereview_review_fields_meta', array(
            'type' => $field_meta_array[$c]['type'],
            'label' => $field_meta_array[$c]['label'],
            'description' => $field_meta_array[$c]['description'],
            'alias' => $field_meta_array[$c]['alias'],
            'required' => $field_meta_array[$c]['required'],
            'display' => $field_meta_array[$c]['display'],
            'publish' => $field_meta_array[$c]['publish'],
            'search' => $field_meta_array[$c]['search'],
            'show' => $field_meta_array[$c]['show'],
            'order' => $field_meta_array[$c]['order'],
            'config' => $field_meta_array[$c]['config'],
            'validators' => $field_meta_array[$c]['validators'],
            'filters' => $field_meta_array[$c]['filters'],
            'style' => $field_meta_array[$c]['style'],
            'error' => $field_meta_array[$c]['error'],
                )
        );
        // Add original field_id to array => new field_id to new corresponding row
        $child_id_reference[$field_meta_array[$c]['field_id']] = $db->lastInsertId();
      }
      unset($c);

      // Create new map from array using new field_id values and new Option ID
      $map_count = count($field_map_array);
      for ($i = 0; $i < $map_count; $i++) {
        $db->insert('engine4_sitereview_review_fields_maps', array(
            'field_id' => $field_map_array[$i]['field_id'],
            'option_id' => $new_option_id,
            'child_id' => $child_id_reference[$field_map_array[$i]['child_id']],
            'order' => $field_map_array[$i]['order'],
                )
        );
      }
    } else {
      // Create new blank option
      $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
          'field_id' => $field->field_id,
          'label' => $form->getValue('label'),
              ));
    }
    $this->view->option = $option->toArray();
    $this->view->form = null;

    // Get data
    $mapData = Engine_Api::_()->fields()->getFieldsMaps($this->_fieldType);
    $metaData = Engine_Api::_()->fields()->getFieldsMeta($this->_fieldType);
    $optionData = Engine_Api::_()->fields()->getFieldsOptions($this->_fieldType);

    // Flush cache
    $mapData->getTable()->flushCache();
    $metaData->getTable()->flushCache();
    $optionData->getTable()->flushCache();
  }

  //ACTION FOR PROFILE DELETION
  public function typeDeleteAction() {

    $option_id = $this->_getParam('option_id');

    if (!empty($option_id)) {

      Engine_Api::_()->getDbtable('reviews', 'sitereview')->update(array('profile_type_review' => 0), array('profile_type_review = ?' => $option_id));

      //DELETE MAPPING
      Engine_Api::_()->getDbtable('categories', 'sitereview')->update(array('profile_type_review' => 0), array('profile_type_review = ?' => $option_id));
    }
    parent::typeDeleteAction();
  }

}
