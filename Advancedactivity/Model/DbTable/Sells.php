<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Sells.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Advancedactivity_Model_DbTable_Sells extends Engine_Db_Table
{

  protected $_rowClass = 'Advancedactivity_Model_Sell';

  public function getSellsPaginator($params = array(), $customParams = null)
  {
    $paginator = Zend_Paginator::factory($this->getSellsSelect($params, $customParams));
    if( !empty($params['page']) ) {
      $paginator->setCurrentPageNumber($params['page']);
    }
    if( !empty($params['limit']) ) {
      $paginator->setItemCountPerPage($params['limit']);
    }
    return $paginator;
  }

  public function getSellsSelect($params = array(), $customParams = null)
  {
    $tableName = $this->info('name');
    $select = $this->select()
      ->from($this)
      ->order(!empty($params['orderby']) ? $tableName . '.' . $params['orderby'] . ' DESC' : $tableName . '.sell_id DESC' );
    if( !empty($params['user_id']) && is_numeric($params['user_id']) ) {
      $select->where($tableName . '.owner_id = ?', $params['user_id']);
    }



    return $select;
  }

 

}
