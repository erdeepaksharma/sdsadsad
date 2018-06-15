<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Job.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Job extends Core_Model_Item_Abstract {

  /**
   * Create document
   *
   * @param array file_pass 
   * @return create job and return info
   * */
  public function setFile($file_pass) {
    if ($file_pass instanceof Zend_Form_Element_File) {
      $file = $file_pass->getFileName();
    } else if (is_array($file_pass) && !empty($file_pass['tmp_name'])) {
      $file = $file_pass['tmp_name'];
    } else if (is_string($file_pass) && file_exists($file_pass)) {
      $file = $file_pass;
    } else {
      throw new Sitereview_Model_Exception('invalid argument passed to setFile');
    }
    $params = array(
        'parent_type' => 'sitereview_listing',
        'parent_id' => $this->getIdentity()
    );

    try {
      $job_return = Engine_Api::_()->storage()->create($file, $params);
    } catch (Exception $e) {
      $msg = $e->getMessage();
      return $msg;
    }

    if (!empty($job_return->file_id)) {

      //UPDATE FILE INFORMATION INTO DATABASE
      $this->file_id = $job_return->file_id;
      $this->save();
    }
  }

}