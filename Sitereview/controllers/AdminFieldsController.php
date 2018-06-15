<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminFieldsController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminFieldsController extends Fields_Controller_AdminAbstract {

  protected $_fieldType = 'sitereview_listing';
  protected $_requireProfileType = true;

  //MANAGE PROFILE FIELDS
  public function indexAction() {
    
    //Make navigation
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_fields');

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
    $form->setTitle('Add Listing Question');
    $form->removeElement('show');
    $form->addElement('hidden', 'show', array('value' => 0));

    $display = $form->getElement('display');
    $display->setLabel('Show on listing page?');
    $display->setOptions(array('multiOptions' => array(
            1 => 'Show on listing page',
            0 => 'Hide on listing page'
            )));

    $search = $form->getElement('search');
    $search->setLabel('Show on the search options?');
    $search->setOptions(array('multiOptions' => array(
            0 => 'Hide on the search options',
            1 => 'Show on the search options'
            )));

    $form->addElement('select', 'quick_info', array(
        'label' => 'Show in quick specifications widget?',
        'multiOptions' => array(
            1 => 'Show in quick specifications widget',
            0 => 'Hide from quick specifications widget'),
        'value' => 0
    ));
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
    $form->setTitle('Edit Listing Question');
    $form->removeElement('show');
    $form->addElement('hidden', 'show', array('value' => 0));

    $display = $form->getElement('display');
    $display->setLabel('Show on listing page?');
    $display->setOptions(array('multiOptions' => array(
            1 => 'Show on listing page',
            0 => 'Hide on listing page'
            )));

    $search = $form->getElement('search');
    $search->setLabel('Show on the search options?');
    $search->setOptions(array('multiOptions' => array(
            0 => 'Hide on the search options',
            1 => 'Show on the search options'
            )));

    $form->addElement('select', 'quick_info', array(
        'label' => 'Show in quick specifications widget?',
        'multiOptions' => array(
            1 => 'Show in quick specifications widget',
            0 => 'Hide from quick specifications widget'),
        'value' => 0
    ));
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
    $this->view->form = $form = new Sitereview_Form_Admin_Type();

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Create New Profile Type from Duplicate of Existing
    if ($form->getValue('duplicate') != 'null') {
      // Create New Option in engine4_sitereview_listing_fields_options
      $option = Engine_Api::_()->fields()->createOption($this->_fieldType, $field, array(
          'field_id' => $field->field_id,
          'label' => $form->getValue('label'),
              ));
      // Get New Option ID
      $db = Engine_Db_Table::getDefaultAdapter();
      $new_option_id = $db->select('option_id')
              ->from('engine4_sitereview_listing_fields_options')
              ->where('label = ?', $form->getValue('label'))
              ->query()
              ->fetchColumn();
      // Get list of Field IDs From Duplicated member Type
      $field_map_array = $db->select()
              ->from('engine4_sitereview_listing_fields_maps')
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
              ->from('engine4_sitereview_listing_fields_meta')
              ->where('field_id IN (' . implode(', ', $child_id_array) . ')')
              ->query()
              ->fetchAll();

      // Copy each row
      for ($c = 0; $c < $field_map_array_count; $c++) {
        $db->insert('engine4_sitereview_listing_fields_meta', array(
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
        $db->insert('engine4_sitereview_listing_fields_maps', array(
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

      Engine_Api::_()->getDbtable('listings', 'sitereview')->update(array('profile_type' => 0), array('profile_type = ?' => $option_id));

      //DELETE MAPPING
      Engine_Api::_()->getDbtable('categories', 'sitereview')->update(array('profile_type' => 0), array('profile_type = ?' => $option_id));
    }
    parent::typeDeleteAction();
  }
  
    //MANAGE PROFILE FIELDS
  public function showCustomfieldsAction() {
    
    //MAKE NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_import');
            
		$db = Engine_Db_Table::getDefaultAdapter();
		
		$import_id = $db->select('importfile_id')
				->from('engine4_sitereview_importfiles')
				->query()
				->fetchColumn();
		
		$tabel_isvalid = 0;
		$table_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_imports\'')->fetch();
		if(!empty($table_exist)) {
			$columns = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports")->fetchAll();
			$count_column = 0;
			foreach($columns as $column) {
				$count_column++;
			}
			if($count_column > 9) {
				$tabel_isvalid = 1;
			}
		}
		$this->view->import_id = $import_id;
            
    //SET DATA
    $mapData = Engine_Api::_()->getApi('core', 'fields')->getFieldsMaps($this->_fieldType);
    $metaData = Engine_Api::_()->getApi('core', 'fields')->getFieldsMeta($this->_fieldType);
    $optionsData = Engine_Api::_()->getApi('core', 'fields')->getFieldsOptions($this->_fieldType);

    //GET TOP LEVEL FIELDS
    $topLevelMaps = $mapData->getRowsMatching(array('field_id' => 0, 'option_id' => 0));
    $topLevelFields = array();
    foreach( $topLevelMaps as $map ) {
      $field = $map->getChild();
      $topLevelFields[$field->field_id] = $field;
    }
    $this->view->topLevelMaps = $topLevelMaps;
    $this->view->topLevelFields = $topLevelFields;
    
    //DO WE REQUIRE PROFILE TYPE?
    //NO
    if( !$this->_requireProfileType ) {
      $this->topLevelOptionId = '0';
      $this->topLevelFieldId = '0';
    }
    //YES
    else {

      //GET TOP LEVEL FIELD
      //ONLY ALLOW ONE TOP LEVEL FIELD
      if( count($topLevelFields) > 1 ) {
        throw new Engine_Exception('Only one top level field is currently allowed');
      }
      $topLevelField = array_shift($topLevelFields);
     
      //ONLY ALLOW THE "PROFILE TYPE" FIELD TO BE A TOP LEVEL FIELD (FOR NOW)
      if( $topLevelField->type !== 'profile_type' ) {
        throw new Engine_Exception('Only profile_type can be a top level field');
      }
      $this->view->topLevelField = $topLevelField;
      $this->view->topLevelFieldId = $topLevelField->field_id;

      //GET TOP LEVEL OPTIONS
      $topLevelOptions = array();
      foreach( $optionsData->getRowsMatching('field_id', $topLevelField->field_id) as $option ) {
        $topLevelOptions[$option->option_id] = $option->label;
      }
      $this->view->topLevelOptions = $topLevelOptions;

 
      //GET SECOND LEVEL FIELDS
      $secondLevelMaps = array();
      $secondLevelFields = array();
      
			$secondLevelMaps = $mapData->getRowsMatching('option_id', 0);
			if( !empty($secondLevelMaps) ) {
				foreach( $secondLevelMaps as $map ) {
					$secondLevelFields[$map->child_id] = $map->getChild();
				}
			}
      $this->view->secondLevelMaps = $secondLevelMaps;
    }

    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
    
    if ($this->getRequest()->getPost()) {
   
      if ($tabel_isvalid) {
        $db->query('DROP TABLE IF EXISTS `engine4_sitereview_imports`');
      }
   
      $table_exist = $db->query('SHOW TABLES LIKE \'engine4_sitereview_imports\'')->fetch();
			if (empty($table_exist)) {
				$db->query("CREATE TABLE IF NOT EXISTS `engine4_sitereview_imports` (
					`import_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					`slug` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
					`category` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
					`sub_category` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
					`subsub_category` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
					`body` text COLLATE utf8_unicode_ci NOT NULL,
					`location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `price` decimal(16,2) unsigned NOT NULL DEFAULT '0.00',
					`overview` text COLLATE utf8_unicode_ci,
					`tags` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
					`img_name` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
					PRIMARY KEY (`import_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
			}
      
      $string_custom_fields = '';
      $string_custom_fields_all = '';
      $dummyDataRow1 = '';
      $dummyDataRow2 = '';
      $dummyDataRow3 = '';
      if(isset($_POST['addtional'])) {
        $customfields = $_POST['addtional'];
			  foreach($customfields as $key => $customfield) {
					$fields = explode('|',$customfield);
					foreach ($fields as $field) {
						$string_custom_fields_all .= '|'.$field;
					}
				}
				
				$dummyDataRow1 = '';
				$dummyDataRow2 = '';
				$dummyDataRow3 = '';
				$fields = ltrim($string_custom_fields_all,'|');
				$fields = explode('|',$fields);
				$count_etnicity = 1;
				$count_lookingfor = 1;
				$count_partner = 1;
				$array_fields = array();
				$CommonCustomfields = array();
				foreach ($fields as $field) {
				  if(in_array($field,$array_fields)) {
				    continue;
				  }
				  $array_fields[] = $field;
				  $string_custom_fields .= '|'.$field;
					$column_name = $field;
					$db = Engine_Db_Table::getDefaultAdapter();
					$columnname_explode = explode('_',$column_name);
					$short_value = "";
					$indexCount = 1;
					foreach ($columnname_explode as $name) {
					  if(($indexCount == 1) || ($indexCount == 2) || ($indexCount == 4)) {
							$short_value .= $name[0].'_';
					  }
					  else {
					    $short_value .= $name.'_';
					  }
					  $indexCount++;
					}
					$short_value = rtrim($short_value,'_');
                                        $short_value = utf8_encode($short_value);
                    $search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
                    $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
                    $short_value = str_replace($search, $replace, $short_value);
                    $column_exist = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports LIKE '$short_value'")->fetch();
					if (empty($column_exist)) {
						$db->query("ALTER TABLE `engine4_sitereview_imports` ADD `$short_value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL");
					}
				  $CommonCustomfields[] = $field;
					$field = explode('_',$field);
					$field = $field[2];
					$fieldType = $db->select()->from('engine4_sitereview_listing_fields_meta', 'type')->where('field_id = ?', $field)->query()->fetchColumn();
					
					switch ($fieldType) {
						case 'first_name':
							$dummyDataRow1 .= 'Anjila'.'|';
							$dummyDataRow2 .= 'Michel'.'|';
							$dummyDataRow3 .= 'David'.'|';
							break;
						case 'last_name':
							$dummyDataRow1 .= 'Jolly'.'|';
							$dummyDataRow2 .= 'Hussan'.'|';
							$dummyDataRow3 .= 'Gillespie'.'|';
							break;
						case 'multiselect':
							$dummyDataRow1 .= 'Yes'.'|';
							$dummyDataRow2 .= 'Yes'.'|';
							$dummyDataRow3 .= 'Yes'.'|';
							break;
						case 'multi_checkbox':
							$dummyDataRow1 .= 'Yes'.'|';
							$dummyDataRow2 .= 'Yes'.'|';
							$dummyDataRow3 .= 'Yes'.'|';
							break;
						case 'ethnicity':
							if($count_etnicity == 1) {
								$dummyDataRow1 .= 'asian'.'|';
								$dummyDataRow2 .= 'asian'.'|';
								$dummyDataRow3 .= 'asian'.'|';
							}
							elseif($count_etnicity == 2) {
							$dummyDataRow1 .= 'black'.'|';
							$dummyDataRow2 .= 'black'.'|';
							$dummyDataRow3 .= 'black'.'|';
							}
							elseif($count_etnicity == 3) {
								$dummyDataRow1 .= 'hispanic'.'|';
								$dummyDataRow2 .= 'hispanic'.'|';
								$dummyDataRow3 .= 'hispanic'.'|';
							}
							elseif($count_etnicity == 4) {
								$dummyDataRow1 .= 'pacific'.'|';
								$dummyDataRow2 .= 'pacific'.'|';
								$dummyDataRow3 .= 'pacific'.'|';
							}
							elseif($count_etnicity == 5) {
								$dummyDataRow1 .= 'white'.'|';
								$dummyDataRow2 .= 'white'.'|';
								$dummyDataRow3 .= 'white'.'|';
							}
							elseif($count_etnicity == 6) {
								$dummyDataRow1 .= 'other'.'|';
								$dummyDataRow2 .= 'other'.'|';
								$dummyDataRow3 .= 'other'.'|';
							}
							$count_etnicity++;
							break;
						case 'looking_for':
							if($count_lookingfor == 1) {
								$dummyDataRow1 .= 'friendship'.'|';
								$dummyDataRow2 .= 'friendship'.'|';
								$dummyDataRow3 .= 'friendship'.'|';
							}
							elseif($count_lookingfor == 2) {
								$dummyDataRow1 .= 'dating'.'|';
								$dummyDataRow2 .= 'dating'.'|';
								$dummyDataRow3 .= 'dating'.'|';
							}
							elseif($count_lookingfor == 3) {
								$dummyDataRow1 .= 'relationship'.'|';
								$dummyDataRow2 .= 'relationship'.'|';
								$dummyDataRow3 .= 'relationship'.'|';
							}
							elseif($count_lookingfor == 4) {
								$dummyDataRow1 .= 'networking'.'|';
								$dummyDataRow2 .= 'networking'.'|';
								$dummyDataRow3 .= 'networking'.'|';
							}
							$count_lookingfor++;
							break;
						case 'gender':
							$dummyDataRow1 .= 'Male'.'|';
							$dummyDataRow2 .= 'Female'.'|';
							$dummyDataRow3 .= 'Male'.'|';
							break;
						case 'partner_gender':
							if($count_partner == 1) {
								$dummyDataRow1 .= 'men'.'|';
								$dummyDataRow2 .= 'men'.'|';
								$dummyDataRow3 .= 'men'.'|';
							}
							elseif($count_partner == 2) {
							$dummyDataRow1 .= 'women'.'|';
							$dummyDataRow2 .= 'women'.'|';
							$dummyDataRow3 .= 'women'.'|';
							}
							$count_partner++;
							break;
						case 'relationship_status':
							$dummyDataRow1 .= 'single'.'|';
							$dummyDataRow2 .= 'married'.'|';
							$dummyDataRow3 .= 'engaged'.'|';
							break;
						case 'political_views':
							$dummyDataRow1 .= 'mid'.'|';
							$dummyDataRow2 .= 'far_right'.'|';
							$dummyDataRow3 .= 'right'.'|';
							break;
						case 'religion':
							$dummyDataRow1 .= 'agnostic'.'|';
							$dummyDataRow2 .= 'taoist'.'|';
							$dummyDataRow3 .= 'buddhist'.'|';
							break;
						case 'birthdate':
							$dummyDataRow1 .= '2013-08-15'.'|';
							$dummyDataRow2 .= '2013-08-17'.'|';
							$dummyDataRow3 .= '2013-08-19'.'|';
							break;
						case 'date':
							$dummyDataRow1 .= '2013-08-15'.'|';
							$dummyDataRow2 .= '2013-08-17'.'|';
							$dummyDataRow3 .= '2013-08-19'.'|';
							break;
						case 'education_level':
							$dummyDataRow1 .= 'high_school'.'|';
							$dummyDataRow2 .= 'some_college'.'|';
							$dummyDataRow3 .= 'bachelors'.'|';
							break;
						case 'weight':
							$dummyDataRow1 .= 'slender'.'|';
							$dummyDataRow2 .= 'average'.'|';
							$dummyDataRow3 .= 'athletic'.'|';
							break;
						case 'income':
							$dummyDataRow1 .= '25_35'.'|';
							$dummyDataRow2 .= '35_50'.'|';
							$dummyDataRow3 .= '50_75'.'|';
							break;
						case 'radio':
							$field_option_array = $db->select()
							->from('engine4_sitereview_listing_fields_options')
							->where('field_id = ?', $field)
							->query()
							->fetchAll();
							$dummyDataRow1 .= $field_option_array[0]['label'].'|';
							$dummyDataRow2 .= $field_option_array[1]['label'].'|';
							if(isset($field_option_array[2])) {
								$dummyDataRow3 .= $field_option_array[2]['label'].'|';
							}
							else {
							  $dummyDataRow3 .= $field_option_array[0]['label'].'|';
							}
							break;
						case 'text':
							$dummyDataRow1 .= 'Dummy Value for Custom Field'.'|';
							$dummyDataRow2 .= 'Dummy Value for Custom Field'.'|';
							$dummyDataRow3 .= 'Dummy Value for Custom Field'.'|';
							break;
						case 'textarea':
							$dummyDataRow1 .= 'This product is awesome.'.'|';
							$dummyDataRow2 .= 'By purchasing this product you will get 20% Off.'.'|';
							$dummyDataRow3 .= 'Best Hotel I have ever seen.'.'|';
							break;
						case 'select':
							$field_option_array = $db->select()
							->from('engine4_sitereview_listing_fields_options')
							->where('field_id = ?', $field)
							->query()
							->fetchAll();
							$dummyDataRow1 .= $field_option_array[0]['label'].'|';
							$dummyDataRow2 .= $field_option_array[1]['label'].'|';
							if(isset($field_option_array[2])) {
								$dummyDataRow3 .= $field_option_array[2]['label'].'|';
							}
							else {
							  $dummyDataRow3 .= $field_option_array[0]['label'].'|';
							}
							break;
						case 'integer':
							$dummyDataRow1 .= '5'.'|';
							$dummyDataRow2 .= '6'.'|';
							$dummyDataRow3 .= '7'.'|';
							break;
						case 'float':
							$dummyDataRow1 .= '5.5'.'|';
							$dummyDataRow2 .= '6.5'.'|';
							$dummyDataRow3 .= '7.5'.'|';
							break;
						case 'about_me':
							$dummyDataRow1 .= 'I am an Engineer'.'|';
							$dummyDataRow2 .= 'I am a Doctor'.'|';
							$dummyDataRow3 .= 'I am a Teacher'.'|';
							break;
						case 'website':
							$dummyDataRow1 .= 'www.google.com'.'|';
							$dummyDataRow2 .= 'www.gmail.com'.'|';
							$dummyDataRow3 .= 'www.yahoo.com'.'|';
							break;
						case 'twitter':
							$dummyDataRow1 .= 'www.twitter.com'.'|';
							$dummyDataRow2 .= 'www.twitter.com'.'|';
							$dummyDataRow3 .= 'www.twitter.com'.'|';
							break;
						case 'facebook':
							$dummyDataRow1 .= 'www.facebook.com'.'|';
							$dummyDataRow2 .= 'www.facebook.com'.'|';
							$dummyDataRow3 .= 'www.facebook.com'.'|';
							break;
						case 'aim':
							$dummyDataRow1 .= 'Make a Doctor'.'|';
							$dummyDataRow2 .= 'Make a Engineer'.'|';
							$dummyDataRow3 .= 'Make a Councellor'.'|';
							break;
						case 'city':
							$dummyDataRow1 .= 'Ottawa'.'|';
							$dummyDataRow2 .= 'London'.'|';
							$dummyDataRow3 .= 'California'.'|';
							break;
						case 'country':
							$dummyDataRow1 .= 'In'.'|';
							$dummyDataRow2 .= 'BZ'.'|';
							$dummyDataRow3 .= 'BT'.'|';
							break;
						case 'zip_code':
							$dummyDataRow1 .= '323802'.'|';
							$dummyDataRow2 .= '202201'.'|';
							$dummyDataRow3 .= '202202'.'|';
							break;
						case 'location':
							$dummyDataRow1 .= 'CA'.'|';
							$dummyDataRow2 .= 'London'.'|';
							$dummyDataRow3 .= 'California'.'|';
							break;
						case 'zodiac':
							$dummyDataRow1 .= 'capricorn'.'|';
							$dummyDataRow2 .= 'aquarius'.'|';
							$dummyDataRow3 .= 'pisces'.'|';
							break;
						case 'eye_color':
							$dummyDataRow1 .= 'Black'.'|';
							$dummyDataRow2 .= 'Blue'.'|';
							$dummyDataRow3 .= 'Brown'.'|';
							break;
						case 'interests':
							$dummyDataRow1 .= 'Playing Cricket'.'|';
							$dummyDataRow2 .= 'Playing Football'.'|';
							$dummyDataRow3 .= 'Playing Hockey'.'|';
							break;
						case 'currency':
							$dummyDataRow1 .= '50'.'|';
							$dummyDataRow2 .= '100'.'|';
							$dummyDataRow3 .= '150'.'|';
							break;
						case 'occupation':
							$dummyDataRow1 .= 'arch'.'|';
							$dummyDataRow2 .= 'fash'.'|';
							$dummyDataRow3 .= 'fina'.'|';
							break;
						case 'checkbox':
							$dummyDataRow1 .= '1'.'|';
							$dummyDataRow2 .= '1'.'|';
							$dummyDataRow3 .= '1'.'|';
							break;
					}
				}
				$dummyDataRow1 = rtrim($dummyDataRow1,'|');
			  $dummyDataRow2 = rtrim($dummyDataRow2,'|');
			  $dummyDataRow3 = rtrim($dummyDataRow3,'|');
			}

			$path = $this->_getPath();
			$file_path = "$path/new_listing_import.csv";

			@chmod($path, 0777);
			@chmod($file_path, 0777);

			$listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();
			$file_string = "";
			if(isset($_POST['addtional'])) {
				if ($listingTypeCount == 1) {
					$file_string .= "Title|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|Photo Name.ext"."$string_custom_fields";
					$file_string .= "\n"."Apple I5 Processor|Electronics|Computers|Tablets|Very high speed computer|Overall Good|Apple|California|2989|Dummyvalue for Photo"."|"."$dummyDataRow1";
					$file_string .= "\n"."Canon|Camera|Camera Accessories|Tripods|Good picture quality|Awesome|photo|Delhi|198|Dummyvalue for Photo"."|"."$dummyDataRow2";
					$file_string .= "\n"."Cinnamon Hotel|Home Decor|Bed & Bath|Bath Towels|Good architecture and good service|Best Service|food|Mumbai|233|Dummyvalue for Photo"."|"."$dummyDataRow3";
				} else {
					$file_string .= "Title|Singular/Plural Slug of Existing Listing Type|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|Photo Name.ext"."$string_custom_fields";
					$file_string .= "\n"."Apple I5 Processor|Product|Electronics|Tablets|Computers|Very high speed computer|Overall Good|Apple|California|2989|Dummyvalue for Photo".'|'."$dummyDataRow1";
					$file_string .= "\n"."Canon|Product|Camera|Camera Accessories|Tripods|Good picture quality|Awesome|photo|Delhi|198|Dummyvalue for Photo"."|"."$dummyDataRow2";
					$file_string .= "\n"."Cinnamon Hotel|Product|Home Decor|Bed & Bath|Bath Towels|Good architecture and good service|Best Service|food|Mumbai|233|Dummyvalue for Photo"."|"."$dummyDataRow3";
				}
			}
			else {
				if ($listingTypeCount == 1) {
					$file_string = "Title|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|Photo Name.ext"."$string_custom_fields";
				} else {
					$file_string = "Title|Singular/Plural Slug of Existing Listing Type|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|Photo Name.ext"."$string_custom_fields";
				}
			}
		
		  if (is_dir(APPLICATION_PATH . "/public/sitereview_listing") != 1) {
        @mkdir(APPLICATION_PATH . "/public/sitereview_listing", 0777, true);
      }
      
      $path_importfile = $this->_getPathImport();
			$file_path_import = "$path_importfile/previous_listing_import.csv";
			@chmod($path_importfile, 0777);
			@chmod($file_path_import, 0777);
			$fp = fopen(APPLICATION_PATH . '/public/sitereview_listing/previous_listing_import.csv', 'w+');
			@chmod(APPLICATION_PATH . "/public/sitereview_listing", 0777);
			fwrite($fp, $file_string);
			fclose($fp);
      
			@chmod($path, 0777);
			@chmod($file_path, 0777);
			$fp = fopen(APPLICATION_PATH . '/temporary/new_listing_import.csv', 'w+');
			fwrite($fp, $file_string);
			fclose($fp);

      //KILL ZEND'S OB
      $isGZIPEnabled = false;
      if (ob_get_level()) {
        $isGZIPEnabled = true;
        @ob_end_clean();
      }

			$path = APPLICATION_PATH . "/temporary/new_listing_import.csv";
			header("Content-Disposition: attachment; filename=" . urlencode(basename($path)), true);
			header("Content-Transfer-Encoding: Binary", true);
			//header("Content-Type: application/x-tar", true);
			header("Content-Type: application/force-download", true);
			header("Content-Type: application/octet-stream", true);
			header("Content-Type: application/download", true);
			header("Content-Description: File Transfer", true);
      if(empty($isGZIPEnabled)){
        header("Content-Length: " . filesize($path), true);
      } 
			readfile("$path");

			exit();
    }
  }
  
  protected function _getPath($key = 'path') {

    $basePath = realpath(APPLICATION_PATH . "/temporary");
    return $this->_checkPath($this->_getParam($key, ''), $basePath);
  }
  
  protected function _getPathImport($key = 'path') {

    $basePath = realpath(APPLICATION_PATH . "/public/sitereview_listing");
    return $this->_checkPath($this->_getParam($key, ''), $basePath);
  }


  protected function _checkPath($path, $basePath) {

    //SANATIZE
    $path = preg_replace('/\.{2,}/', '.', $path);
    $path = preg_replace('/[\/\\\\]+/', '/', $path);
    $path = trim($path, './\\');
    $path = $basePath . '/' . $path;

    //Resolve
    $basePath = realpath($basePath);
    $path = realpath($path);

    //CHECK IF THIS IS A PARENT OF THE BASE PATH
    if ($basePath != $path && strpos($basePath, $path) !== false) {
      return $this->_helper->redirector->gotoRoute(array());
    }
    return $path;
  }
  
}