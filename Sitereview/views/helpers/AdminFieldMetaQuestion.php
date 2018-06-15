<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminFieldMeta1.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_View_Helper_AdminFieldMetaQuestion extends Zend_View_Helper_Abstract {

  public function adminFieldMetaQuestion($map) {
    $meta = $map->getChild();
    if(!empty($map->option_id)) {
			$profileTypeLabel = Engine_Api::_()->getApi('core', 'sitereview')->getProfileTypeName($map->option_id);
    }
    if (!($meta instanceof Fields_Model_Meta)) {
      return '';
    }

    // Prepare translations
    $translate = Zend_Registry::get('Zend_Translate');

    // Prepare params
    if ($meta->type == 'heading') {
      $containerClass = 'heading';
    } else {
      $containerClass = 'field';
    }

    $key = $map->getKey();
    $label = $this->view->translate($meta->label);
    $type = $meta->type;

    $typeLabel = Engine_Api::_()->fields()->getFieldInfo($type, 'label');
    $typeLabel = $this->view->translate($typeLabel);

    // Options data
    $optionContent = '';
    $dependentFieldContent = '';

    if ($meta->canHaveDependents()) {
      $extraOptionsClass = 'field_extraoptions ' . $this->_generateClassNames($key, 'field_extraoptions_');
      $optionContent .= <<<EOF
<div class="{$extraOptionsClass}" id="field_extraoptions_{$key}">
  <div class="field_extraoptions_contents_wrapper">
    <div class="field_extraoptions_contents">
EOF;

      $options = $meta->getOptions();

      if (!empty($options)) {
        $extraOptionsChoicesClass = 'field_extraoptions_choices ' . $this->_generateClassNames($key, 'field_extraoptions_choices_');
        $optionContent .= <<<EOF
      <ul class="{$extraOptionsChoicesClass}" id="admin_field_extraoptions_choices_{$key}">
EOF;
        foreach ($options as $option) {
          $optionId = $option->option_id;
          $optionLabel = $this->view->translate($option->label);
          $label_value = $profileTypeLabel.'_'.$label .'_'.$meta->field_id.'_' . $optionLabel . '_' . $optionId;
          $dependentFieldCount = count(Engine_Api::_()->fields()->getFieldsMaps($option->getFieldType())->getRowsMatching('option_id', $optionId));
          $dependentFieldCountString = ( $dependentFieldCount <= 0 ? '' : ' (' . $dependentFieldCount . ')' );

          $optionClass = 'field_option_select field_option_select_' . $optionId . ' ' . $this->_generateClassNames($key, 'field_option_select_');
          if ($meta->type == 'multi_checkbox' || $meta->type == 'multiselect') {
            $optionContent .= <<<EOF
        <li id="field_option_select_{$key}_{$optionId}" class="{$optionClass}">
          <span class="field_extraoptions_choices_options">
           <input type="checkbox" name="addtional[]" if(!empty($meta->required)) { checked } value="$label_value"/>
          </span>
          <span class="">
            {$optionLabel} {$dependentFieldCountString}
          </span>
        </li>
        
EOF;
          } else {
            $optionContent .= <<<EOF
        <li id="field_option_select_{$key}_{$optionId}" class="{$optionClass}">
          <span class="">
            {$optionLabel} {$dependentFieldCountString}
          </span>
        </li>
        
EOF;
          }
        }
        $optionContent .= <<<EOF
      </ul>
EOF;
        foreach ($options as $option) {
          $dependentFieldContent .= $this->view->adminFieldOption($option, $map);
        }
      }

      $optionContent .= <<<EOF
    </div>
  </div>
  <a href="javascript:void(0);" onclick="void(0);" onmousedown="void(0);">
    {$translate->_('edit choices')}
  </a>
</div>
EOF;
    }

    // Generate
    $contentClass = 'admin_field ' . $this->_generateClassNames($key, 'admin_field_');
    $content = <<<EOF
  <li id="admin_field_{$key}" class="{$contentClass}">
    <span class='{$containerClass}'>
      <div class='item_handle' style='display:none;'>
        &nbsp;
      </div>
      <div class='item_title'>
        {$label}
        <span>({$typeLabel})</span>
      </div>
      {$optionContent}
    </span>
    {$dependentFieldContent}
  </li>
EOF;

    return $content;
  }

  protected function _generateClassNames($key, $prefix = '') {
    list($parent_id, $option_id, $child_id) = explode('_', $key);
    return
            $prefix . 'parent_' . $parent_id . ' ' .
            $prefix . 'option_' . $option_id . ' ' .
            $prefix . 'child_' . $child_id
    ;
  }

}