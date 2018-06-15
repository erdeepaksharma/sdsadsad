<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FieldValueLoopQuickinfo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_View_Helper_FieldValueLoopQuickInfoSM extends Fields_View_Helper_FieldAbstract {

  public function fieldValueLoopQuickInfoSM($subject, $partialStructure, $itemCount = 5) {
    if (empty($partialStructure)) {
      return '';
    }

    if (!($subject instanceof Core_Model_Item_Abstract) || !$subject->getIdentity()) {
      return '';
    }

    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    // Generate
    $content = '';
    $lastContents = '';
    $lastHeadingTitle = null; //Zend_Registry::get('Zend_Translate')->_("Missing heading");

    $viewer = Engine_Api::_()->user()->getViewer();
    $show_hidden = $viewer->getIdentity() ? ($subject->getOwner()->isSelf($viewer) || 'admin' === Engine_Api::_()->getItem('authorization_level', $viewer->level_id)->type) : false;

    $count = 0;
    foreach ($partialStructure as $map) {

      // Get field meta object
      $field = $map->getChild();
      $value = $field->getValue($subject);
      if (!$field || $field->type == 'profile_type' || !$field->quick_info)
        continue;
      if (!$field->display && !$show_hidden)
        continue;

      // Heading
      if ($field->type == 'heading') {
        if (!empty($lastContents)) {
          $content .= $this->_buildLastContents($lastContents, $lastHeadingTitle);
          $lastContents = '';
        }
        $lastHeadingTitle = $this->view->translate($field->label);
      }

      // Normal fields
      else {
        $tmp = $this->getFieldValueString($field, $value, $subject, $map, $partialStructure);
        if (!empty($tmp)) {

          $notice = !$field->display && $show_hidden ? sprintf('<div class="tip"><span>%s</span></div>', $this->view->translate('This field is hidden and only visible to you and admins:')) : '';
          $label = $this->view->translate($field->label);
          $lastContents .= <<<EOF
  <li>
    {$notice}
    <span>
      {$label}:
    </span>
    <span>
      {$tmp}
    </span>
  </li>
EOF;
        }

        $lastContents .= '';
        $lastContents;
      }

      if ($field->type != 'heading') {
        $count++;
      }

      if ($count >= $itemCount) {
        break;
      }
    }

    if (!empty($lastContents)) {
      $content .= $this->_buildLastContents($lastContents, $lastHeadingTitle);
    }

    return $content;
  }

  public function getFieldValueString($field, $value, $subject, $map = null, $partialStructure = null) {
    if ((!is_object($value) || !isset($value->value)) && !is_array($value)) {
      return null;
    }

    // @todo This is not good practice:
    // if($field->type =='textarea'||$field->type=='about_me') $value->value = nl2br($value->value);

    $helperName = Engine_Api::_()->fields()->getFieldInfo($field->type, 'helper');
    if (!$helperName) {
      return null;
    }

    $helper = $this->view->getHelper($helperName);
    if (!$helper) {
      return null;
    }

    $helper->structure = $partialStructure;
    $helper->map = $map;
    $helper->field = $field;
    $helper->subject = $subject;
    $tmp = $helper->$helperName($subject, $field, $value);
    unset($helper->structure);
    unset($helper->map);
    unset($helper->field);
    unset($helper->subject);

    return $tmp;
  }

  protected function _buildLastContents($content, $title) {
    if (!$title) {
      return '<div class="siteuser_cover_profile_fields"><ul>' . $content . '</ul></div>';
    }
    return <<<EOF
        <div class="siteuser_cover_profile_fields">
          <h4>
            <span>{$title}</span>
          </h4>
          <ul>
            {$content}
          </ul>
        </div>
EOF;
  }

}