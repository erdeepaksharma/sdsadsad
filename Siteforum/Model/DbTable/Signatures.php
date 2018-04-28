<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Signatures.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Signatures extends Engine_Db_Table {

    protected $_name = 'forum_signatures';
    protected $_rowClass = 'Siteforum_Model_Signature';

    public function getColumnValue($user_id, $column_name) {

        return $this->select()
                        ->from($this->info('name'), array("$column_name"))
                        ->where('user_id = ?', $user_id)
                        ->limit(1)
                        ->query()
                        ->fetchColumn();
    }

    public function setColumnValue($params = array()) {

        $row = $this->fetchRow($this->select()->where('user_id = ?', $params['user_id']));

        if (empty($row)) {
            $list = $this->createRow();
            $list->setFromArray(array(
                'user_id' => $params['user_id'],
                'body' => $params['body'],
                'creation_date' => new Zend_Db_Expr('NOW()'),
                'modified_date' => new Zend_Db_Expr('NOW()'),
            ));
            $list->save();
        } else {
            $this->update(array('body' => $params['body'], 'modified_date' => new Zend_Db_Expr('NOW()'),), array('user_id = ?' => $params['user_id']));
        }
    }

}
