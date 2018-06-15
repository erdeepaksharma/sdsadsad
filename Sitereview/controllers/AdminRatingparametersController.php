<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminRatingparametersController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminRatingparametersController extends Core_Controller_Action_Admin {

  //ACTION FOR MANAGING RATING PARAMETERS
  public function manageAction() {

    //GET NAVIGATION
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_review');

    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_reviewmain', array(), 'sitereview_admin_reviewmain_ratingparams');

    //GET LISTINGTYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);

    //CHECK REVIEW IS ALLOWED OR NOT
    $this->view->allowReviews = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'reviews');

    //GET REVIEW PARAMETER TABLE NAME
    $tableRatingParams = Engine_Api::_()->getDbtable('ratingparams', 'sitereview');
    $tableRatingParamsName = $tableRatingParams->info('name');

    $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');
    $categories = array();
    $category_info = $tableCategory->getCategories(null, 0, $this->view->listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'cat_order'));
    foreach ($category_info as $value) {

      $sub_cat_array = array();
      $category_info2 = $tableCategory->getSubCategories($value->category_id);
      foreach ($category_info2 as $subresults) {
        $treesubarray = array();
        $subcategory_info2 = $tableCategory->getSubCategories($subresults->category_id);
        $treesubarrays[$subresults->category_id] = array();
        foreach ($subcategory_info2 as $subvalues) {
          $tree_rating_params = array();
          $categoryIdsArray = array();
          $categoryIdsArray[] = $subvalues->category_id;
          $getTreeRatingParams = $tableRatingParams->reviewParams($categoryIdsArray, 'sitereview_listing');
          foreach ($getTreeRatingParams as $ratingParam) {
            $tree_rating_params[$subvalues->category_id][] = array(
                'tree_ratingparam_id' => $ratingParam->ratingparam_id,
                'tree_ratingparam_name' => $ratingParam->ratingparam_name,
            );
          }

          $treesubarrays[$subresults->category_id][] = $treesubarray = array(
              'tree_sub_cat_id' => $subvalues->category_id,
              'tree_sub_cat_name' => $subvalues->category_name,
              'order' => $subvalues->cat_order,
              'tree_rating_params' => $tree_rating_params
          );
        }

        $subcat_rating_params = array();
        $categoryIdsArray = array();
        $categoryIdsArray[] = $subresults->category_id;
        $getSubcatRatingParams = $tableRatingParams->reviewParams($categoryIdsArray, 'sitereview_listing');
        foreach ($getSubcatRatingParams as $ratingParam) {
          $subcat_rating_params[$subresults->category_id][] = array(
              'subcat_ratingparam_id' => $ratingParam->ratingparam_id,
              'subcat_ratingparam_name' => $ratingParam->ratingparam_name,
          );
        }

        $tmp_array = array('sub_cat_id' => $subresults->category_id,
            'sub_cat_name' => $subresults->category_name,
            'tree_sub_cat' => $treesubarrays[$subresults->category_id],
            'order' => $subresults->cat_order,
            'subcat_rating_params' => $subcat_rating_params,
        );
        $sub_cat_array[] = $tmp_array;
      }

      $cat_rating_params = array();
      $categoryIdsArray = array();
      $categoryIdsArray[] = $value->category_id;
      $getCatRatingParams = $tableRatingParams->reviewParams($categoryIdsArray, 'sitereview_listing');
      foreach ($getCatRatingParams as $ratingParam) {
        $cat_rating_params[$value->category_id][] = array(
            'cat_ratingparam_id' => $ratingParam->ratingparam_id,
            'cat_ratingparam_name' => $ratingParam->ratingparam_name,
        );
      }

      $categories[] = $category_array = array(
          'category_id' => $value->category_id,
          'category_name' => $value->category_name,
          'order' => $value->cat_order,
          'sub_categories' => $sub_cat_array,
          'cat_rating_params' => $cat_rating_params,
      );
    }

    $this->view->categories = $categories;
  }

  //ACTION FOR CREATE NEW REVIEW PARAMETER
  public function createAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GENERATE FORM
    $form = $this->view->form = new Sitereview_Form_Admin_Ratingparameter_Create();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    $this->view->options = array();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {

        //CHECK PARAMETERS
        $options = (array) $this->_getParam('optionsArray');
        $options = array_filter(array_map('trim', $options));
        $options = array_slice($options, 0, 100);
        $this->view->options = $options;
        if (empty($options) || !is_array($options) || count($options) < 1) {
          return $form->addError('You must add at least one parameter.');
        }

        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('')
      ));
    }

    $this->renderScript('admin-ratingparameters/create.tpl');
  }

  //ACTION FOR EDITING THE REVIEW PARAMETER NAME
  public function editAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    if (!($category_id = $this->_getParam('category_id'))) {
      die('No identifier specified');
    }

    //FETCH PARAMETERS ACCORDING TO THIS CATEGORY
    $categoryIdsArray = array();
    $categoryIdsArray[] = $category_id;
    $ratingParams = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, 'sitereview_listing');

    $this->view->options = array();
    $this->view->totalOptions = 1;

    //GENERATE A FORM
    $form = $this->view->form = new Sitereview_Form_Admin_Ratingparameter_Edit();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));
    $form->setField($ratingParams);

    //CHECK PARAMETERS
    $options = (array) $this->_getParam('optionsArray');
    $options = array_filter(array_map('trim', $options));
    $options = array_slice($options, 0, 100);
    $this->view->options = $options;
    $this->view->totalOptions = Count($options);

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {

        foreach ($values as $key => $value) {
          if ($key != 'options' && $key != 'dummy_text') {
            $ratingparam_id = explode('ratingparam_name_', $key);

            if (!empty($ratingparam_id)) {
              $reviewcat = Engine_Api::_()->getItem('sitereview_ratingparam', $ratingparam_id[1]);

              //EDIT CATEGORY NAMES
              if (!empty($reviewcat)) {
                $reviewcat->ratingparam_name = $value;
                $reviewcat->save();
              }
            }
          }
        }

        //INSERT THE REVIEW CATEGORY IN TO THE DATABASE
        foreach ($options as $index => $option) {
          $row = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->createRow();
          $row->category_id = $this->_getParam('category_id');
          $row->ratingparam_name = $option;
          $row->resource_type = 'sitereview_listing';
          $row->save();
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Parameters has been edited successfully.')
      ));
    }

    $this->renderScript('admin-ratingparameters/edit.tpl');
  }

  //ACTION FOR DELETING THE REVIEW PARAMETERS
  public function deleteAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    if (!($category_id = $this->_getParam('category_id'))) {
      die('No identifier specified');
    }

    //GENERATE FORM
    $form = $this->view->form = new Sitereview_Form_Admin_Ratingparameter_Delete();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();

      foreach ($values as $key => $value) {
        if ($value == 1) {
          $ratingparam_id = explode('ratingparam_name_', $key);
          $reviewcat = Engine_Api::_()->getItem('sitereview_ratingparam', $ratingparam_id[1]);

          //@to do [We have to put some check according to other module]
          $resource_type = 'sitereview_listing';

          //DELETE ENTRIES FROM RATING TABLE CORROSPONDING TO REVIEW CATEGORY ID
          Engine_Api::_()->getDbtable('ratings', 'sitereview')->delete(array('ratingparam_id = ?' => $ratingparam_id[1], 'resource_type =? ' => $resource_type));


          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();

          try {
            //DELETE THE REVIEW PARAMETERS
            $reviewcat->delete();
            $db->commit();
          } catch (Exception $e) {
            $db->rollBack();
            throw $e;
          }
        }
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Parameters has been deleted successfully.')
      ));
    }
    $this->renderScript('admin-ratingparameters/delete.tpl');
  }

}