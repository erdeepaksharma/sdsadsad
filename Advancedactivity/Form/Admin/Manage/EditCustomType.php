<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: EditCustomType.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Form_Admin_Manage_EditCustomType extends Advancedactivity_Form_Admin_Manage_CustomType {

  public function init() {
    parent::init();
    $this
		->setTitle('Edit Content Type')
		->setDescription('Use the form below to edit the content type for enabling users to create their custom lists for filtering activity feeds over them.');

    $this->getElement('module_name')
            ->setIgnore(true)
            ->setAttrib('disable', true)
            ->clearValidators()
            ->setRequired(false)
            ->setAllowEmpty(true)
    ;
    if($this->getElement('resource_type'))
    $this->getElement('resource_type')
            ->setIgnore(true)
            ->setAttrib('disable', true)
            ->clearValidators()
            ->setRequired(false)
            ->setAllowEmpty(true)
    ;

//    $notInclude = array('activity', 'advancedactivity', 'sitealbum', 'sitelike', 'sitepagealbum', 'sitepagediscussion', 'sitepagelikebox', 'mobi', 'seaocore');
//    $module_table = Engine_Api::_()->getDbTable('modules', 'core');
//    $moduleName = $module_table->info('name');
//    $select = $module_table->select()
//            ->from($moduleName, array('name', 'title'))
//            ->where($moduleName . '.type =?', 'extra')
//            ->where($moduleName . '.name not in(?)', $notInclude)
//            ->where($moduleName . '.enabled =?', 1);
//
//    $contentModuloe = $select->query()->fetchAll();
//    $contentModuloeArray = array();
//    $contentModuloeArray[] = '';
//    foreach ($contentModuloe as $modules) {
//      $contentModuloeArray[$modules['name']] = $modules['title'];
//    }
//
//
//    $this->addElement('Select', 'module_name', array(
//        'label' => 'Content Module',
//        'allowEmpty' => false,
//        'onchange' => 'setModuleName(this.value)',
//        'multiOptions' => $contentModuloeArray,
//    ));
//
//    $module_name = Zend_Controller_Front::getInstance()->getRequest()->getParam('module_name', null);
//    if (!empty($module_name)) {
//      $this->module_name->setValue($module_name);
//      $contentItem = $this->getContentItem($module_name);
//      if (empty($contentItem))
//        $this->addElement('Dummy', 'dummy_title', array(
//            'description' => 'For this module not difine any item in manifest file.',
//        ));
//    }
//    $this->addElement('Select', 'resource_type', array(
//        'label' => 'Content Type',
//        'description' => "This is the value of 'items' key in the manifest file of this plugin. To view this value for a desired module, go to the directory of this module, and open the file 'settings/manifest.php'. In this file, search for 'items', and view its value. [Ex in case of blog module: Open file 'application/modules/Blog/settings/manifest.php', and go to around line 62. You will see the 'items' key array with value 'blog'. Thus, the Database Table Item for blog module is: 'blog']",
//        //  'required' => true,
//        'multiOptions' => $contentItem,
//    ));
//    $this->addElement('Text', 'resource_title', array(
//        'label' => 'Content Title',
//        'description' => 'Enter the content name for which you use this. Ex: You may use the document module for ‘Tutorials’ on your site.',
//        'required' => true
//    ));
//
//
//    $this->addElement('Checkbox', 'enabled', array(
//        'description' => 'Custom List',
//        'label' => 'Enable custom list for this content type.',
//        'value' => 1
//    ));
//    // Element: execute
//    $this->addElement('Button', 'execute', array(
//        'label' => 'Save Settings',
//        'type' => 'submit',
//        'ignore' => true,
//        'decorators' => array('ViewHelper'),
//    ));
//
//    // Element: cancel
//    $this->addElement('Cancel', 'cancel', array(
//        'label' => 'cancel',
//        'prependText' => ' or ',
//        'ignore' => true,
//        'link' => true,
//        'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action' => 'content-lists')),
//        'decorators' => array('ViewHelper'),
//    ));
//
//    // DisplayGroup: buttons
//    $this->addDisplayGroup(array('execute', 'cancel'), 'buttons', array(
//        'decorators' => array(
//            'FormElements',
//            'DivDivDivWrapper',
//        )
//    ));
  }

//  public function getContentItem($moduleName) {
//    $file_path = APPLICATION_PATH . "/application/modules/" . ucfirst($moduleName) . "/settings/manifest.php";
//    $contentItem = array();
//    if (@file_exists($file_path)) {
//      $ret = include $file_path;
//      if (isset($ret['items'])) {
//
//        foreach ($ret['items'] as $item)
//          $contentItem[$item] = $item . " ";
//      }
//    }
//    return $contentItem;
//  }
}

?>