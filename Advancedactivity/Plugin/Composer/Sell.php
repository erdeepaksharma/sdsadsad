<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Tag.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Composer_Sell extends Core_Plugin_Composer
{
  

  public function onAttachSell($data)
  {
    try {
      $data["date"] = date('Y-m-d H:i:s');
      $data['title'] = strip_tags($data['title']);
      $data['description'] = strip_tags($data['description']);
      $sellTable = Engine_Api::_()->getItemTable('advancedactivity_sell');
      $row = $sellTable->createRow();
      $row->setFromArray($data);
      $row->save();
      $form = new Advancedactivity_Form_BuySell_Create();

      // Add fields
      $subElements = array_intersect_key($data, array_flip(preg_grep('/^field/', array_keys($data))));
      $subElementsValues = array('fields' => array());
      foreach( $subElements as $key => $value ) {
        $key2 = str_replace('fields-', '', $key);
        $subElementsValues['fields'][str_replace('fields-', '', $key)] = $value;
      }

      $customfieldform = $form->getSubForm('fields');
      $customfieldform->populate($subElementsValues);


      $customfieldform->setItem($row);
      $customfieldform->saveValues($data);
    } catch( Exception $e ) {
      die(" Exception " . $e);
    }
    return $row;
  }

}
