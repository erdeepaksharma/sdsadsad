<?php
  $db = Zend_Db_Table_Abstract::getDefaultAdapter() ;

//Quary for update widget of activity feed. Member home page
$select = new Zend_Db_Select( $db ) ;
$select
    ->from( 'engine4_core_pages' )
    ->where( 'name = ?' , 'user_index_home' )
    ->limit( 1 ) ;
$page_id = $select->query()->fetchObject()->page_id ;

// @Make an condition
// @Make an condition
if ( !empty( $page_id ) ) {
	  //Quary for update widget of activity feed.
    $select = new Zend_Db_Select( $db ) ;
    $select->from( 'engine4_core_content' )->where( 'name = ?' , 'activity.feed' )->where( 'page_id = ?' ,
    $page_id)->limit(1);
    $results = $select->query()->fetchAll();
if (!empty($results)) {
$params =
'{"title":"","advancedactivity_tabs":["welcome","aaffeed","twitter"],"nomobile":"0",
"name":"advancedactivity.home-feeds"}';

		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`content_id` =
			"'.$results[0]["content_id"].'" LIMIT 1;');

            $db->insert( 'engine4_core_content' , array (
              'page_id' => $page_id,
              'type' => 'widget',
              'name' => 'advancedactivity.home-feeds',
              'parent_content_id' => $results[0]['parent_content_id'],
              'order' => $results[0]['order'],
              'params' => $params,
            ) ) ;
//            $db->insert( 'engine4_core_content' , array (
//              'page_id' => $page_id,
//              'type' => 'widget',
//              'name' => 'advancedactivity.greeting',
//              'parent_content_id' => $results[0]['parent_content_id'],
//              'order' => $results[0]['order']-2,
//              'params' => '{"title":""}',
//            ) ) ;
            $db->insert( 'engine4_core_content' , array (
              'page_id' => $page_id,
              'type' => 'widget',
              'name' => 'advancedactivity.on-this-day',
              'parent_content_id' => $results[0]['parent_content_id'],
              'order' => $results[0]['order']-1,
              'params' => '{"title":""}',
            ) ) ;

}
}


//Quary for update widget of activity feed. Home Page
$select = new Zend_Db_Select( $db ) ;
$select
    ->from( 'engine4_core_pages' )
    ->where( 'name = ?' , 'core_index_index' )
    ->limit( 1 ) ;
$page_id = $select->query()->fetchObject()->page_id ;

// @Make an condition
if ( !empty( $page_id ) ) {
	  //Quary for update widget of activity feed.
    $select = new Zend_Db_Select( $db ) ;
    $select->from( 'engine4_core_content' )->where( 'name = ?' , 'activity.feed' )->where( 'page_id = ?' ,
    $page_id)->limit(1);
    $results = $select->query()->fetchAll();
if (!empty($results)) {
$params = '{"title":"What\'s New","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds", "showTabs":"1" }';

		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`content_id` =
			"'.$results[0]["content_id"].'" LIMIT 1;');

            $db->insert( 'engine4_core_content' , array (
              'page_id' => $page_id,
              'type' => 'widget',
              'name' => 'advancedactivity.home-feeds',
              'parent_content_id' => $results[0]['parent_content_id'],
              'order' => $results[0]['order'],
              'params' => $params,
            ) ) ;

}
}


 //Quary for update widget of activity feed.
    $select = new Zend_Db_Select( $db ) ;
    $select->from( 'engine4_core_content' )->where( 'name = ?' , 'activity.feed' );
    $results = $select->query()->fetchAll();
if (!empty($results)) {
			foreach($results as $result) {
			$params =
'{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds", "showTabs":"1"}';
				$db->query('UPDATE  `engine4_core_content` SET  `name` =  "advancedactivity.home-feeds",
`params`=\''.$params.'\' WHERE `engine4_core_content`.`name` ="activity.feed";');
	   }
}

	  //Quary for update widget of seaocore activity feed.
    $select = new Zend_Db_Select( $db ) ;
    $select->from( 'engine4_core_content' )->where( 'name = ?' , 'seaocore.feed' );
    $results = $select->query()->fetchAll();
		if (!empty($results)) {
			foreach($results as $result) {
				$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds", "showTabs":"1"}';
					$db->query('UPDATE  `engine4_core_content` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_core_content`.`name` ="seaocore.feed";');
		  }
		}
		
	  //Quary for update widget of seaocore activity feed in the page plugin.
      $is_sitepageEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepage');
      if( !empty($is_sitepageEnabled) ) {
				$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitepage_content'")->fetch();
				if (!empty($table_exist)) {
					$select = new Zend_Db_Select( $db ) ;
					$select->from( 'engine4_sitepage_content' )->where( 'name = ?' , 'seaocore.feed' )->orWhere( 'name = ?' , 'activity.feed' );
					$results = $select->query()->fetchAll();
					if (!empty($results)) {
						foreach($results as $result) {
							$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds"}';
								$db->query('UPDATE  `engine4_sitepage_content` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_sitepage_content`.`name` ="seaocore.feed" OR `engine4_sitepage_content`.`name` ="activity.feed";');
						}
					}
				$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitepage_admincontent'")->fetch();
				if (!empty($table_exist)) {
					$select = new Zend_Db_Select( $db ) ;
					$select->from( 'engine4_sitepage_admincontent' )->where( 'name = ?' , 'seaocore.feed' )->orWhere( 'name = ?' , 'activity.feed' );
					$results = $select->query()->fetchAll();
					if (!empty($results)) {
						foreach($results as $result) {
							$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds"}';
								$db->query('UPDATE  `engine4_sitepage_admincontent` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_sitepage_admincontent`.`name` ="seaocore.feed" OR `engine4_sitepage_admincontent`.`name` ="activity.feed";');
						}
					}
				}
		}
}

	  //Quary for update widget of seaocore activity feed in the group plugin.
      $is_sitegroupEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroup');
      if( !empty($is_sitegroupEnabled) ) {
				$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitegroup_content'")->fetch();
				if (!empty($table_exist)) {
					$select = new Zend_Db_Select( $db ) ;
					$select->from( 'engine4_sitegroup_content' )->where( 'name = ?' , 'seaocore.feed' )->orWhere( 'name = ?' , 'activity.feed' );
					$results = $select->query()->fetchAll();
					if (!empty($results)) {
						foreach($results as $result) {
							$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds"}';
								$db->query('UPDATE  `engine4_sitegroup_content` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_sitegroup_content`.`name` ="seaocore.feed" OR `engine4_sitegroup_content`.`name` ="activity.feed";');
						}
					}
				$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitegroup_admincontent'")->fetch();
				if (!empty($table_exist)) {
					$select = new Zend_Db_Select( $db ) ;
					$select->from( 'engine4_sitegroup_admincontent' )->where( 'name = ?' , 'seaocore.feed' )->orWhere( 'name = ?' , 'activity.feed' );
					$results = $select->query()->fetchAll();
					if (!empty($results)) {
						foreach($results as $result) {
							$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds"}';
								$db->query('UPDATE  `engine4_sitegroup_admincontent` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_sitegroup_admincontent`.`name` ="seaocore.feed" OR `engine4_sitegroup_admincontent`.`name` ="activity.feed";');
						}
					}
				}
		}
}

//Quary for update widget of seaocore activity feed in the store plugin.
      $is_sitestoreEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitestore');
      if( !empty($is_sitestoreEnabled) ) {
				$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitestore_content'")->fetch();
				if (!empty($table_exist)) {
					$select = new Zend_Db_Select( $db ) ;
					$select->from( 'engine4_sitestore_content' )->where( 'name = ?' , 'seaocore.feed' )->orWhere( 'name = ?' , 'activity.feed' );
					$results = $select->query()->fetchAll();
					if (!empty($results)) {
						foreach($results as $result) {
							$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds"}';
								$db->query('UPDATE  `engine4_sitestore_content` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_sitestore_content`.`name` ="seaocore.feed" OR `engine4_sitestore_content`.`name` ="activity.feed";');
						}
					}
				$table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitestore_admincontent'")->fetch();
				if (!empty($table_exist)) {
					$select = new Zend_Db_Select( $db ) ;
					$select->from( 'engine4_sitestore_admincontent' )->where( 'name = ?' , 'seaocore.feed' )->orWhere( 'name = ?' , 'activity.feed' );
					$results = $select->query()->fetchAll();
					if (!empty($results)) {
						foreach($results as $result) {
							$params = '{"title":"Updates","advancedactivity_tabs":["aaffeed"],"nomobile":"0","name":"advancedactivity.home-feeds"}';
								$db->query('UPDATE  `engine4_sitestore_admincontent` SET  `name` =  "advancedactivity.home-feeds", `params`=\''.$params.'\' WHERE `engine4_sitestore_admincontent`.`name` ="seaocore.feed" OR `engine4_sitestore_admincontent`.`name` ="activity.feed";');
						}
					}
				}
		}
}
  // Add the Welcome Tab on Member Home Page.
  $select = new Zend_Db_Select($db);
  $select
          ->from('engine4_core_pages')
          ->where('name = ?', 'advancedactivity_index_welcometab')
          ->limit(1);
  $info = $select->query()->fetch();

  if (empty($info)) {
    $db->insert('engine4_core_pages', array(
        'name' => 'advancedactivity_index_welcometab',
        'displayname' => 'Wall - Welcome Tab',
        'title' => 'Wall - Welcome Tab',
        'description' => 'Wall - Welcome Tab',
        'custom' => 1,
    ));
    $page_id = $db->lastInsertId('engine4_core_pages');

    //CONTAINERS
    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 2,
        'params' => '',
    ));
    $container_id = $db->lastInsertId('engine4_core_content');

    $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $container_id,
        'order' => 6,
        'params' => '',
    ));
    $middle_id = $db->lastInsertId('engine4_core_content');

    if( !empty($middle_id) ) {
      // Widget: Welcome Message
      $db->insert('engine4_core_content', array(
	'page_id' => $page_id,
	'type' => 'widget',
	'name' => 'advancedactivity.welcome-message',
	'parent_content_id' => $middle_id,
	'order' => 1,
	'params' => '{"title":""}',
      ));

      // Widgets: Custom Block
      $db->insert('engine4_core_content', array(
	'page_id' => $page_id,
	'type' => 'widget',
	'name' => 'advancedactivity.custom-block',
	'parent_content_id' => $middle_id,
	'order' => 2,
	'params' => '{"title":""}',
      ));

      $is_PeopleyoumayknowEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('peopleyoumayknow');
      if( !empty($is_PeopleyoumayknowEnabled) ) {
	// Widgets: Invite Friend
	$db->insert('engine4_core_content', array(
	  'page_id' => $page_id,
	  'type' => 'widget',
	  'name' => 'peopleyoumayknow.suggestion-invites',
	  'parent_content_id' => $middle_id,
	  'order' => 3,
	  'params' => '{"title":""}',
	));
      }else {
	// Widgets: Invite Friend
	$db->insert('engine4_core_content', array(
	  'page_id' => $page_id,
	  'type' => 'widget',
	  'name' => 'suggestion.suggestion-invites',
	  'parent_content_id' => $middle_id,
	  'order' => 3,
	  'params' => '{"title":""}',
	));
      }

      // Widgets: Profile Photo
      $db->insert('engine4_core_content', array(
	'page_id' => $page_id,
	'type' => 'widget',
	'name' => 'advancedactivity.profile-photo',
	'parent_content_id' => $middle_id,
	'order' => 4,
	'params' => '{"title":""}',
      ));

      $is_PeopleyoumayknowEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('peopleyoumayknow');
      if( !empty($is_PeopleyoumayknowEnabled) ) {
	// Widgets: People You May Know
	$db->insert('engine4_core_content', array(
	  'page_id' => $page_id,
	  'type' => 'widget',
	  'name' => 'peopleyoumayknow.suggestion-friend',
	  'parent_content_id' => $middle_id,
	  'order' => 5,
	  'params' => '{"title":"","getLayout":"1","nomobile":"0","name":"peopleyoumayknow.suggestion-friend"}',
	));
      }else {
	// Widgets: People You May Know
	$db->insert('engine4_core_content', array(
	  'page_id' => $page_id,
	  'type' => 'widget',
	  'name' => 'Suggestion.suggestion-friend',
	  'parent_content_id' => $middle_id,
	  'order' => 5,
	  'params' => '{"title":"","getLayout":"1","nomobile":"0","name":"Suggestion.suggestion-friend"}',
	));
      }

      // Widgets: Explore Suggestion
      $db->insert('engine4_core_content', array(
	'page_id' => $page_id,
	'type' => 'widget',
	'name' => 'Suggestion.explore-friend',
	'parent_content_id' => $middle_id,
	'order' => 6,
	'params' => '{"title":"","itemCountPerPage":"20","nomobile":"0","name":"Suggestion.explore-friend"}',
      ));

      // Widgets: Search For People
      $db->insert('engine4_core_content', array(
	'page_id' => $page_id,
	'type' => 'widget',
	'name' => 'advancedactivity.search-for-people',
	'parent_content_id' => $middle_id,
	'order' => 7,
	'params' => '{"title":""}',
      ));

      // Widgets: Search For People
      $db->insert('engine4_core_content', array(
	'page_id' => $page_id,
	'type' => 'widget',
	'name' => 'sitelike.welcomemix-like',
	'parent_content_id' => $middle_id,
	'order' => 8,
	'params' => '{"title":""}',
      ));

    }
  }
//Review Plugin Work 
    $table_exist = $db->query("SHOW TABLES LIKE 'engine4_sitereview_listingtypes'")->fetch();
    if (!empty($table_exist)) {
  $listingTypes = $db->query("SELECT * FROM `engine4_sitereview_listingtypes`")->fetchAll();
  foreach ($listingTypes as $listingTypeArray) {
    Engine_Api::_()->advancedactivity()->contentTabSettings('sitereview_listtype_' . $listingTypeArray['listingtype_id'], 'add', array('module_name' => 'sitereviewlistingtype', 'resource_title' => ucfirst($listingTypeArray['title_plural'])));
  }
}