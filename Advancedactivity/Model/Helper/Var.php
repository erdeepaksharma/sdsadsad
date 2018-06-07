<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Var.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Helper_Var extends Advancedactivity_Model_Helper_Abstract
{
  /**
   *
   * @param string $value
   * @return string
   */
  public function direct($value, $noTranslate = false)
  {
    $translate = Zend_Registry::get('Zend_Translate');
    if ($translate instanceof Zend_Translate) {
      $text = strip_tags($value);
      if ($text !== (string)$value) {
        $value = $this->translateHTML($value);
      } else {
        $translateText =  $translate->translate($text);
        //The translation CSV files have some wrong entries like: "blog";"blog";"blogs", "photo";"photo";"photos", "event";"event";"events", whereas they should've been like: "event";"event". The below condition is to make translation work correctly for such entries.
        if (empty($translateText)) {
            $translateText = $translate->translate(array($text, $text, 1));
        }
        if (is_array($translateText)) {
          list($translateText) = $translateText;
        }

        $value = $translateText;
      }
    }
    return $value;
  }

  protected function translateHTML($htmlString)
  {
    $dom = new DOMDocument();
    if ($dom) {
      $dom->loadHtml($htmlString);
      $this->translateNodeText($dom);
      $string = $dom->saveHTML();
      $htmlString = mb_substr($string, 119, -15);
    }
    return $htmlString;
  }

  protected function translateNodeText($node)
  {
    if (!$node->hasChildNodes()) {
      return;
    }

    $translate = Zend_Registry::get('Zend_Translate');
    foreach ($node->childNodes as $childNode) {
      if ($childNode instanceof DOMText) {
        $text = $translate->translate($childNode->wholeText);
        //The translation CSV files have some wrong entries like: "blog";"blog";"blogs", "photo";"photo";"photos", "event";"event";"events", whereas they should've been like: "event";"event". The below condition is to make translation work correctly for such entries.
        if (empty($text)) {
          $text = $translate->translate(array($childNode->wholeText, $childNode->wholeText, 1));
        }
        if (is_array($text)) {
          $text = $text[0];
        }

        $node->replaceChild(new DOMText($text), $childNode);
      } else {
        $this->translateNodeText($childNode);
      }
    }
  }
}
