<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: AafFluentList.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_View_Helper_AafFluentList extends Zend_View_Helper_Abstract {

  /**
   * Generates a fluent list of item. Example:
   *   You
   *   You and Me
   *   You, Me, and Jenny
   * 
   * @param array|Traversable $items
   * @return string
   */
  public function aafFluentList($items, $translate = false) {
    if (0 === ($num = count($items))) {
      return '';
    }

    $comma = $this->view->translate(',');
    $and = $this->view->translate('and');
    $index = 0;
    $content = '';
    foreach ($items as $item) {
      if ($num > 2 && $index > 0)
        $content .= $comma . ' '; else
        $content .= ' ';
      if ($num > 1 && $index == $num - 1)
        $content .= $and . ' ';

      $href = null;
      $title = null;
      $guid = null;
      if (is_object($item)) {
        if (method_exists($item, 'getTitle') && method_exists($item, 'getHref')) {
          $href = $item->getHref();
          $title = $item->getTitle();
        } else if (method_exists($item, '__toString')) {
          $title = $item->__toString();
        } else {
          $title = (string) $item;
        }

        if (method_exists($item, 'getGuid')) {
          $guid = $item->getType().' '.$item->getIdentity();
        }
      } else {
        $title = (string) $item;
      }

      if ($translate) {
        $title = $this->view->translate($title);
      }

      if (null === $href) {
        $content .= $title;
      } else {
        $content .= $this->view->htmlLink($href, $title, array('class' => 'sea_add_tooltip_link',
            'rel' => $guid));
      }

      $index++;
    }

    return $content;
  }

}