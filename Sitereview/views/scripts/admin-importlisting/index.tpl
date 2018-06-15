<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: index.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
  var changeListingType =function(listingtype_id){
    if($('import_button')) {
      $('import_button').style.display = 'none';
    }
    if($('review_import_button')) {
      $('review_import_button').style.display = 'none';
    }
    if($('blog_import_button')) {
      $('blog_import_button').style.display = 'none';
    }
    window.location.href= en4.core.baseUrl+'admin/sitereview/importlisting/index/listingtype_id/'+listingtype_id;

  }
</script>

<?php  $is_error = 0; ?>
<?php  $recipe_is_error = 0; ?>
<?php  $classified_is_error = 0; ?>
<?php  $blog_is_error = 0; ?>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>
<?php if (count($this->navigation)): ?>
  <div class='seaocore_admin_tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?> </div>
<?php endif; ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'log', 'action' => 'index'), $this->translate('Import History'), array('class' => 'buttonlink icon_sitereviews_log')) ?>

<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'manage'), $this->translate('Manage CSV Import Files'), array('class' => 'buttonlink icon_sitereview_admin_import_manage')) ?><br/><br/>
<?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
<?php $listingTypeTitle = ucfirst(Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn(1, 'title_singular'));?>

<?php if(empty($this->sitereviewlistingtypeInsalled)): ?>
		<div class="tip">
			<span>
				<?php echo $this->translate("<b>Note:</b> You can also import listings into various Listing Types on your site. This importing of listings into various Listing Type is dependent on the '%1sReviews & Ratings - Multiple Listing Types Extension%2s' and requires it to be installed and enabled on your site. Please install this plugin after downloading it from your Client Area on SocialEngineAddOns. You may purchase this plugin %3sover here%4s.", "<a href='http://www.socialengineaddons.com/reviewsextensions/socialengine-multiple-listing-types-extension' target='_blank'>", "</a>", "<a href='http://www.socialengineaddons.com/reviewsextensions/socialengine-multiple-listing-types-extension' target='_blank'>", "</a>");?>
			</span>
		</div> 
<?php endif; ?>

<?php if($this->listEnabled && $this->first_listing_id): ?>
	<div class="importlisting_form">
		<div>
      <?php if(Count($listingTypes) > 1) :?>
			 <h3><?php echo $this->translate('Import Listings into a chosen Listing Type');?></h3>
			 <p>
				<?php echo $this->translate("This Importing tool is designed to migrate content directly from a Listing to a chosen Listing Type. Using this, you can convert all the listings on your site into a listing type of your choice. Please note that we try to import all the data corresponding to a Listing but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Listings.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php else:?>
       <h3><?php echo $this->translate("Import Listings into $listingTypeTitle Listings");?></h3>
			 <p>
			 	  <?php echo $this->translate("This Importing tool is designed to migrate content directly from a Listing to $listingTypeTitle Listings. Using this, you can convert all the listings on your site into $listingTypeTitle Listings. Please note that we try to import all the data corresponding to a Listing but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Listings.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
				</p>
      <?php endif;?>

			<br />
      <?php $checkVersion = Engine_Api::_()->sitereview()->checkVersion($this->listVersion, '4.2.8'); ?>
      <?php if($checkVersion == 0): ?>
        <div class="error-message">
          <span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/cross.png);'>
            <?php echo $this->translate("You do not have the latest version of the 'Listings / Catalog Showcase Plugin'. Please upgrade it to the latest version to enable the importing of listings into this plugin."); ?>
          </span>
        </div><br />
      <?php else:?>      
      
        <?php if(Count($listingTypes) > 1): ?>
          <div>
            <label>
              <b><?php echo $this->translate("Listing Type:") ?></b>
            </label>
            <select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
              <?php foreach ($listingTypes as $listingType): ?>
                <?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
                <option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
                </option>
              <?php endforeach; ?>
            </select>
          </div><br />     
        <?php endif; ?>
          
        <div id="activity_list-wrapper" class="form-wrapper">
          <div class="form-label" id="activity_list-label">&nbsp;</div>
          <div id="activity_list-element" class="form-element">
            <input type="hidden" name="activity_list" value="" /><input type="checkbox" name="activity_list" id="activity_list"/>
            <label for="activity_list" class="optional"><?php echo $this->translate("Import activity feeds also."); ?></label>
          </div>
        </div><br/>
          
        <div id="success_message" class='success-message'></div>
        <div id="unsuccess_message" class="error-message"></div>

        <div class="importlisting_elements" id="importlisting_elements" >

            <?php if(!$this->listingtypeArray->price) :?>
              <?php	$is_error = 1; ?>
              <?php	$error_msg1 = $this->translate("Price is disabled!"); ?>

              <span class="red" id="price_import_continue">
               <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
               <b><?php echo $error_msg1;?></b>
                <a onclick="continueImporting('price');return false;">
                  <?php echo $this->translate('Ok, please continue');?>
                </a>
             </span>
           <?php endif;?>

            <?php if(!$this->listingtypeArray->location) :?>
              <?php	$is_error = 1; ?>
              <?php	$error_msg1 = $this->translate("Location is disabled!"); ?>

              <span class="red" id="location_import_continue">
               <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
               <b><?php echo $error_msg1;?></b>

                <a onclick="continueImporting('location');return false;">
                  <?php echo $this->translate('Ok, please continue');?>
                </a>
             </span>
           <?php endif;?>

           <?php if(!$this->listingtypeArray->body_allow) :?>
              <?php	$is_error = 1; ?>
              <?php	$error_msg1 = $this->translate("Description is disabled!"); ?>

              <span class="red" id="description_import_continue">
               <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
               <b><?php echo $error_msg1;?></b>

                <a onclick="continueImporting('description');return false;">
                  <?php echo $this->translate('Ok, please continue');?>
                </a>
             </span>
           <?php endif;?>

            <?php if(($this->listingtypeArray->reviews == 0 || $this->listingtypeArray->reviews == 1)) :?>
              <?php	$is_error = 1; ?>
              <?php	$error_msg1 = $this->translate("Reviews is disabled!"); ?>

              <span class="red" id="reviews_import_continue">
               <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
               <b><?php echo $error_msg1;?></b>

                <a onclick="continueImporting('reviews');return false;">
                  <?php echo $this->translate('Ok, please continue');?>
                </a>
             </span>
           <?php endif;?>
          
          <?php	$error_msg1 = $this->translate("Owners Reviews is disabled!"); ?>

          <span class="red" id="owner_reviews_import_continue">
            <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
            <b><?php echo $error_msg1;?></b>

            <a onclick="continueImporting('ownerreviews');return false;">
              <?php echo $this->translate('Ok, please continue');?>
            </a>
          </span>

            <?php if(!$this->listingtypeArray->overview) :?>
              <?php	$is_error = 1; ?>
              <?php	$error_msg1 = $this->translate("Overview is disabled!"); ?>

              <span class="red" id="overview_import_continue">
               <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
               <b><?php echo $error_msg1;?></b>

                <a onclick="continueImporting('overview');return false;">
                  <?php echo $this->translate('Ok, please continue');?>
                </a>
             </span>
           <?php endif;?>

        </div>

        <div id="import_button" class="import_button" <?php if($is_error == 0): ?> style="display:block;" <?php else:?> style="display:none;" <?php endif;?>>
            <button type="button" id="continue" name="continue" onclick='startImport();'>
              <?php echo $this->translate('Start Import');?>
            </button>

        </div>

        <div id="import_again_button" class="import_button" style="display:none;">
          <?php if($is_error == 0): ?>
            <button type="button" id="continue" name="continue" onclick='startImport();'>
              <?php echo $this->translate('Import Again');?>
            </button>
          <?php endif;?>
        </div>
      <?php endif;?>
		</div>
	</div>
<?php endif;?>

<?php if($this->recipeEnabled && $this->first_recipe_id): ?>
	<div class="importlisting_form">
		<div>
      <?php if(Count($listingTypes) > 1) :?>
			 <h3><?php echo $this->translate('Import Recipes into a chosen Listing Type');?></h3>
			 <p>
				<?php echo $this->translate("This Importing tool is designed to migrate content directly from a Recipe to a chosen Listing Type. Using this, you can convert all the recipes on your site into a listing type of your choice. Please note that we try to import all the data corresponding to a Recipe but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Recipes.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php else:?>
       <h3><?php echo $this->translate("Import Recipes into $listingTypeTitle Listings");?></h3>
			 <p>
			 	  <?php echo $this->translate("This Importing tool is designed to migrate content directly from a Recipe to $listingTypeTitle Listings. Using this, you can convert all the recipes on your site into $listingTypeTitle Listings. Please note that we try to import all the data corresponding to a Recipe but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Recipes.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php endif;?>
      <br />

			<?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
			<?php if(Count($listingTypes) > 1): ?>
				<div>
					<label>
						<b><?php echo $this->translate("Listing Type:") ?></b>
					</label>
					<select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
						<?php foreach ($listingTypes as $listingType): ?>
							<?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
							<option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
							</option>
						<?php endforeach; ?>
					</select>
				</div><br />     
			<?php endif; ?>
        
      <div id="activity_recipe-wrapper" class="form-wrapper">
        <div class="form-label" id="activity_recipe-label">&nbsp;</div>
        <div id="activity_recipe-element" class="form-element">
          <input type="hidden" name="activity_recipe" value="" /><input type="checkbox" name="activity_recipe" id="activity_recipe"/>
          <label for="activity_recipe" class="optional"><?php echo $this->translate("Import activity feeds also."); ?></label>
        </div>
      </div><br/>        
	
			<div id="recipe_success_message" class='success-message'></div>
			<div id="recipe_unsuccess_message" class="error-message"></div>

			<div class="importlisting_elements" id="recipe_importlisting_elements" >

          <?php if(!$this->listingtypeArray->price) :?>
						<?php	$recipe_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Price is disabled!"); ?>

            <span class="red" id="recipe_price_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
							<a onclick="recipeContinueImporting('price');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

          <?php if(!$this->listingtypeArray->location) :?>
						<?php	$recipe_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Location is disabled!"); ?>

            <span class="red" id="recipe_location_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="recipeContinueImporting('location');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

          <?php if(!$this->listingtypeArray->body_allow) :?>
						<?php	$recipe_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Description is disabled!"); ?>

            <span class="red" id="recipe_description_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="recipeContinueImporting('description');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>


          <?php if(($this->listingtypeArray->reviews == 0 || $this->listingtypeArray->reviews == 1)) :?>
						<?php	$recipe_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Reviews is disabled!"); ?>

            <span class="red" id="recipe_reviews_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="recipeContinueImporting('reviews');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>


						<?php	$error_msg1 = $this->translate("Owners Reviews is disabled!"); ?>

            <span class="red" id="recipe_owner_reviews_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="recipeContinueImporting('ownerreviews');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>


          <?php if(!$this->listingtypeArray->overview) :?>
						<?php	$recipe_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Overview is disabled!"); ?>

            <span class="red" id="recipe_overview_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="recipeContinueImporting('overview');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

			</div>
	
			<div id="recipe_import_button" class="import_button" <?php if($recipe_is_error == 0): ?> style="display:block;" <?php else:?> style="display:none;" <?php endif;?>>
					<button type="button" id="recipe_continue" name="continue" onclick='startRecipeImport();'>
						<?php echo $this->translate('Start Import');?>
					</button>
				
			</div>
	
			<div id="recipe_import_again_button" class="import_button" style="display:none;">
				<?php if($recipe_is_error == 0): ?>
					<button type="button" id="recipe_continue" name="continue" onclick='startRecipeImport();'>
						<?php echo $this->translate('Import Again');?>
					</button>
				<?php endif;?>
			</div>
		</div>
	</div>
<?php endif;?>

<?php if($this->classifiedEnabled && $this->first_classified_id): ?>
	<div class="importlisting_form">
		<div>
      <?php if(Count($listingTypes) > 1) :?>
			 <h3><?php echo $this->translate('Import Classifieds into a chosen Listing Type');?></h3>
			 <p>
				<?php echo $this->translate("This Importing tool is designed to migrate content directly from a Classified to a chosen Listing Type. Using this, you can convert all the classifieds on your site into a listing type of your choice. Please note that we try to import all the data corresponding to a Classified but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Classifieds.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php else:?>
       <h3><?php echo $this->translate("Import Classifieds into $listingTypeTitle Listings");?></h3>
			 <p>
			 	  <?php echo $this->translate("This Importing tool is designed to migrate content directly from a Classified to $listingTypeTitle Listings. Using this, you can convert all the classifieds on your site into $listingTypeTitle Listings. Please note that we try to import all the data corresponding to a Classified but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Classifieds.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php endif;?>
      <br />

			<?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
			<?php if(Count($listingTypes) > 1): ?>
				<div>
					<label>
						<b><?php echo $this->translate("Listing Type:") ?></b>
					</label>
					<select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
						<?php foreach ($listingTypes as $listingType): ?>
							<?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
							<option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
							</option>
						<?php endforeach; ?>
					</select>
				</div><br />     
			<?php endif; ?>
        
      <div id="activity_classified-wrapper" class="form-wrapper">
        <div class="form-label" id="activity_classified-label">&nbsp;</div>
        <div id="activity_classified-element" class="form-element">
          <input type="hidden" name="activity_classified" value="" /><input type="checkbox" name="activity_classified" id="activity_classified"/>
          <label for="activity_classified" class="optional"><?php echo $this->translate("Import activity feeds also."); ?></label>
        </div>
      </div><br/>        
	
			<div id="classified_success_message" class='success-message'></div>
			<div id="classified_unsuccess_message" class="error-message"></div>

			<div class="importlisting_elements" id="classified_importlisting_elements" >

          <?php if(!$this->listingtypeArray->price) :?>
						<?php	$classified_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Price is disabled!"); ?>

            <span class="red" id="classified_price_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
							<a onclick="classifiedContinueImporting('price');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

          <?php if(!$this->listingtypeArray->location) :?>
						<?php	$classified_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Location is disabled!"); ?>

            <span class="red" id="classified_location_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="classifiedContinueImporting('location');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

          <?php if(!$this->listingtypeArray->body_allow) :?>
						<?php	$classified_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Description is disabled!"); ?>

            <span class="red" id="classified_description_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="classifiedContinueImporting('description');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

			</div>
	
			<div id="classified_import_button" class="import_button" <?php if($classified_is_error == 0): ?> style="display:block;" <?php else:?> style="display:none;" <?php endif;?>>
					<button type="button" id="classified_continue" name="continue" onclick='startClassifiedImport();'>
						<?php echo $this->translate('Start Import');?>
					</button>
				
			</div>
	
			<div id="classified_import_again_button" class="import_button" style="display:none;">
				<?php if($classified_is_error == 0): ?>
					<button type="button" id="classified_continue" name="continue" onclick='startClassifiedImport();'>
						<?php echo $this->translate('Import Again');?>
					</button>
				<?php endif;?>
			</div>
		</div>
	</div>
<?php endif;?>

<?php if($this->blogEnabled && $this->first_blog_id): ?>
	<div class="importlisting_form">
		<div>
      <?php if(Count($listingTypes) > 1) :?>
			 <h3><?php echo $this->translate('Import Blogs into a chosen Listing Type');?></h3>
			 <p>
				<?php echo $this->translate("This Importing tool is designed to migrate content directly from a Blogs to a chosen Listing Type. Using this, you can convert all the blogs on your site into a listing type of your choice. Please note that we try to import all the data corresponding to a Blog but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Blogs.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php else:?>
       <h3><?php echo $this->translate("Import Blogs into $listingTypeTitle Listings");?></h3>
			 <p>
			 	  <?php echo $this->translate("This Importing tool is designed to migrate content directly from a Blog to $listingTypeTitle Listings. Using this, you can convert all the blogs on your site into $listingTypeTitle Listings. Please note that we try to import all the data corresponding to a Blog but there is a possibility of some data losses too.<br />Below are the conditions which are required to be true for this import. Please check the points carefully and if some condition is yet to be fulfilled then do that first and then start importing your Blogs.<br />Once the import gets started, it is recommended not to close the lightbox, otherwise it will not be completed successfully and some data losses may occur.");?>
			</p>
      <?php endif;?>
      <br />

			<?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
			<?php if(Count($listingTypes) > 1): ?>
				<div>
					<label>
						<b><?php echo $this->translate("Listing Type:") ?></b>
					</label>
					<select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">            
						<?php foreach ($listingTypes as $listingType): ?>
							<?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
							<option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
							</option>
						<?php endforeach; ?>
					</select>
				</div><br />     
			<?php endif; ?>
        
      <div id="activity_blog-wrapper" class="form-wrapper">
        <div class="form-label" id="activity_blog-label">&nbsp;</div>
        <div id="activity_blog-element" class="form-element">
          <input type="hidden" name="activity_blog" value="" /><input type="checkbox" name="activity_blog" id="activity_blog"/>
          <label for="activity_blog" class="optional"><?php echo $this->translate("Import activity feeds also."); ?></label>
        </div>
      </div><br/>        
	
			<div id="blog_success_message" class='success-message'></div>
			<div id="blog_unsuccess_message" class="error-message"></div>

			<div class="importlisting_elements" id="blog_importlisting_elements" >

          <?php if(!$this->listingtypeArray->overview) :?>
						<?php	$blog_is_error = 1; ?>
						<?php	$error_msg1 = $this->translate("Overview is disabled!"); ?>

            <span class="red" id="blog_overview_import_continue">
             <img src='<?php echo $this->layout()->staticBaseUrl . "application/modules/Sitereview/externals/images/cross.png" ?>' />
             <b><?php echo $error_msg1;?></b>
            
							<a onclick="blogContinueImporting('Overview');return false;">
								<?php echo $this->translate('Ok, please continue');?>
							</a>
           </span>
         <?php endif;?>

			</div>
	
			<div id="blog_import_button" class="import_button" <?php if($blog_is_error == 0): ?> style="display:block;" <?php else:?> style="display:none;" <?php endif;?>>
					<button type="button" id="blog_continue" name="continue" onclick='startBlogImport();'>
						<?php echo $this->translate('Start Import');?>
					</button>
				
			</div>
	
			<div id="blog_import_again_button" class="import_button" style="display:none;">
				<?php if($blog_is_error == 0): ?>
					<button type="button" id="blog_continue" name="continue" onclick='startBlogImport();'>
						<?php echo $this->translate('Import Again');?>
					</button>
				<?php endif;?>
			</div>
		</div>
	</div>
<?php endif;?>

<div class="importlisting_form">
	<div>
		<h3><?php echo $this->translate('Import Listings from a CSV file');?></h3>

		<p>
		 <?php echo $this->translate("This tool allows you to import Listings corresponding to the entries from a .csv file. Here, you can also generate your own .csv template file by selecting the Profile Fields to be included in it by using the “Generate new CSV template file” link below. Before starting to use this tool, please read the following points carefully. ");?>
		</p>

		<ul class="importlisting_form_sitereview">

			<li>
				<?php echo $this->translate("Don't add any new column in the csv file from which importing has to be done.");?>
			</li>

			<li>
				<?php echo $this->translate("The data in the files should be pipe('|') separated and in a particular format or ordering. So, there should be no pipe('|') in any individual column of the CSV file . If you want to add comma(',') separated data in the CSV file, then you can select the comma(',') option during the CSV file upload process. Note: There is one drawback of using the comma(',') separated data that you will not be able to use comma in fields like description, price, overview etc. for the entries in the CSV file.");?>
			</li>

			<?php if($this->listingTypeCount > 1): ?>
        <li>
          <?php echo $this->translate("Listing titles, descriptions, categories and Listings URL alternate text for 'listing' of existing listing types are the required fields for all the entries in the file.");?>
        </li>

        <li>
          <?php echo $this->translate("Listings URL alternate text for 'listing' should exactly match with the existing Listings URL alternate text for 'listing' for all the listing types. Thus, you should the below URLs in your csv file:");?><br>
          
          <?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getAllListingTypes(); ?>
          <?php foreach($listingTypes as $listingType): ?><br/>
            <?php echo "$listingType->title_plural => $listingType->slug_singular"; ?>
          <?php endforeach; ?>
        </li>			
        
        <li>
          <?php echo $this->translate("Categories, sub-categories and 3rd level categories name should exactly match with the existing categories, sub-categories and 3rd level categories.");?>
        </li>      
        <?php else: ?>
         <li>
          <?php echo $this->translate("Listing title, description and category are the required fields for all the entries in the file.");?>
        </li>	

        <li>
          <?php echo $this->translate("Categories, sub-categories and 3rd level categories name should exactly match with the existing categories, sub-categories and 3rd level categories.");?>
        </li>       
			<?php endif; ?>
      <li>
        <?php echo $this->translate('Now, you can also import profile pictures for listings via the CSV file by following method:<br />
				a) Make a zipped folder containing all the photos to be uploaded and mention the name of the pictures in the csv file with associated listing to be imported. You can later upload the folder from the “Manage CSV Import Files” section. [Note: To allow photos folder to be uploaded on your site successfully, please make sure that the php_zip extension is installed on your server.]');?>
      </li>
			<li>
				<?php echo $this->translate("Before starting the import process, it is recommended that you should first create Categories, Profile Fields and do Category-Profile mappings from the 'Category-Listing Profile Mapping' section.");?>
			</li>

			<li>
				<?php echo $this->translate("In case you want to insert more than one tag for an entry, then the tags string should be separated by hash('#'). For example, if you want to insert 2 tags for an entry - 'tag1' and 'tag2', then tag string for that will be 'tag1#tag2'.");?>
			</li>

			<li>
				<?php echo $this->translate("You can import the maximum of 10,000 Listings at a time and if you want to import more, you would have to then repeat the whole process. For example, you have to import 15000 Listings. Then, you would have to create 2 CSV files - one having 10,000 entries and another having 5,000 entries corresponding to the Listings. After that, just import both the files using 'Import Listings' option.");?>
			</li>

			<li>
				<?php echo $this->translate("You can also 'Stop' and 'Rollback' the import process. 'Stop' will just stop the import process going on at that time from that file and 'Rollback' will undo or delete all the Listings created from that CSV import file till that time.");?>
			</li>

			<li>
				<?php echo $this->translate("Files must be in the CSV format to be imported.");?>
			</li>

		</ul>
		
		<br />
    
    <iframe src="about:blank" style="display:none" name="downloadframe"></iframe>
    
    <a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'download-sample')) ?><?php echo '?path=' . urlencode('example_listing_import.csv');?>" class="buttonlink icon_sitereviews_download_csv"><?php echo $this->translate('Download example CSV template file')?></a> 
	  
	  <a href="<?php echo $this->url(array('module' => 'sitereview', 'controller' => 'fields', 'action' => 'show-customfields')) ?>" class="buttonlink icon_sitereviews_generate_csv"><?php echo $this->translate('Generate new CSV template file')?></a>  
	  
	  <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sitereview', 'controller' => 'admin-importlisting', 'action' => 'import'), $this->translate('Import Listings'), array('class' => 'smoothbox buttonlink icon_sitereviews_import')) ?>
  
		<br />
		<br />
		
	</div>
</div>		

<script type="text/javascript">
	var assigned_previous_id = '<?php echo $this->assigned_previous_id; ?>';
  var recipe_assigned_previous_id = '<?php echo $this->recipe_assigned_previous_id; ?>';
  var classified_assigned_previous_id = '<?php echo $this->classified_assigned_previous_id; ?>';
  var blog_assigned_previous_id = '<?php echo $this->blog_assigned_previous_id; ?>';
  var click1 = '<?php echo $this->listingtypeArray->price;?>';
  var click2 = '<?php echo $this->listingtypeArray->location;?>';
	var click3 = '<?php echo $this->listingtypeArray->body_allow;?>';
	var click4 = '<?php echo $this->listingtypeArray->reviews;?>';
	var click5 = '<?php echo $this->listingtypeArray->overview;?>';
  var click6 = '<?php echo $this->listingtypeArray->allow_owner_review;?>';
  
	function startImport() 
	{	
    var import_confirmation =  confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to start importing Listings ?")) ?>');

    var activity_list = 0;
    if($('activity_list').checked == true) {
      activity_list = 1;
    }
    
		if(import_confirmation) {

			Smoothbox.open("<div><center><b>" + '<?php echo $this->string()->escapeJavascript($this->translate("Importing Listings...")) ?>' + "</b><br /><img src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/loader.gif' alt='' /></center></div>");

			en4.core.request.send(new Request.JSON({
				url : en4.core.baseUrl+'admin/sitereview/importlisting',
				method: 'get',
				data : {
					'start_import' : 1,
					'assigned_previous_id' : assigned_previous_id,
          'listingtype_id' : '<?php echo $this->listingtype_id?>',
          'activity_list' : activity_list,
          'module' : 'list',
					'format' : 'json'
				},
				onSuccess : function(responseJSON) {
					
					$('import_button').style.display = 'none';
					$('importlisting_elements').style.display = 'none';
					
					if (responseJSON.assigned_previous_id < responseJSON.last_listing_id) {
						$('import_again_button').style.display = 'block';
						assigned_previous_id = responseJSON.assigned_previous_id;
						
						$('unsuccess_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/cross.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Sorry for this inconvenience !!")) ?>' + "<br />"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is interrupted due to some reason. Please click on 'Import Again' button to start the importing from the same point again.")) ?>'+"</span><br />";
					}
					else {
						$('import_again_button').style.display = 'none';
						$('unsuccess_message').style.display = 'none';
						$('success_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/notice.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is done succesfully.")) ?>'+"</span><br />";
					}
					Smoothbox.close();
				}
			}))
		}
	}

	function startRecipeImport() 
	{	
    var import_confirmation =  confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to start importing Recipes ?")) ?>');

		if(import_confirmation) {

			Smoothbox.open("<div><center><b>" + '<?php echo $this->string()->escapeJavascript($this->translate("Importing Recipes...")) ?>' + "</b><br /><img src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/loader.gif' alt='' /></center></div>");

      var activity_recipe = 0;
      if($('activity_recipe').checked == true) {
        activity_recipe = 1;
      }

			en4.core.request.send(new Request.JSON({
				url : en4.core.baseUrl+'admin/sitereview/importlisting',
				method: 'get',
				data : {
					'start_import' : 1,
					'recipe_assigned_previous_id' : recipe_assigned_previous_id,
          'listingtype_id' : '<?php echo $this->listingtype_id?>',
          'activity_recipe' : activity_recipe,
          'module' : 'recipe',
					'format' : 'json'
				},
				onSuccess : function(responseJSON) {
					
					$('recipe_import_button').style.display = 'none';
					$('recipe_importlisting_elements').style.display = 'none';
					
					if (responseJSON.recipe_assigned_previous_id < responseJSON.last_recipe_id) {
						$('recipe_import_again_button').style.display = 'block';
						recipe_assigned_previous_id = responseJSON.recipe_assigned_previous_id;
						
						$('recipe_unsuccess_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/cross.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Sorry for this inconvenience !!")) ?>' + "<br />"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is interrupted due to some reason. Please click on 'Import Again' button to start the importing from the same point again.")) ?>'+"</span><br />";
					}
					else {
						$('recipe_import_again_button').style.display = 'none';
						$('recipe_unsuccess_message').style.display = 'none';
						$('recipe_success_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/notice.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is done succesfully.")) ?>'+"</span><br />";
					}
					Smoothbox.close();
				}
			}))
		}
	}
  
	function startClassifiedImport() 
	{	
    var import_confirmation =  confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to start importing Classifieds ?")) ?>');

		if(import_confirmation) {
      
      var activity_classified = 0;
      if($('activity_classified').checked == true) {
        activity_classified = 1;
      }

			Smoothbox.open("<div><center><b>" + '<?php echo $this->string()->escapeJavascript($this->translate("Importing Classifieds...")) ?>' + "</b><br /><img src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/loader.gif' alt='' /></center></div>");

			en4.core.request.send(new Request.JSON({
				url : en4.core.baseUrl+'admin/sitereview/importlisting',
				method: 'get',
				data : {
					'start_import' : 1,
					'classified_assigned_previous_id' : classified_assigned_previous_id,
          'listingtype_id' : '<?php echo $this->listingtype_id?>',
          'activity_classified' : activity_classified,
          'module' : 'classified',
					'format' : 'json'
				},
				onSuccess : function(responseJSON) {
					
					$('classified_import_button').style.display = 'none';
					$('classified_importlisting_elements').style.display = 'none';
					
					if (responseJSON.classified_assigned_previous_id < responseJSON.last_classified_id) {
						$('classified_import_again_button').style.display = 'block';
						classified_assigned_previous_id = responseJSON.classified_assigned_previous_id;
						
						$('classified_unsuccess_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/cross.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Sorry for this inconvenience !!")) ?>' + "<br />"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is interrupted due to some reason. Please click on 'Import Again' button to start the importing from the same point again.")) ?>'+"</span><br />";
					}
					else {
						$('classified_import_again_button').style.display = 'none';
						$('classified_unsuccess_message').style.display = 'none';
						$('classified_success_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/notice.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is done succesfully.")) ?>'+"</span><br />";
					}
					Smoothbox.close();
				}
			}))
		}
	}  

	function startBlogImport() 
	{	
    var import_confirmation =  confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to start importing Blogs ?")) ?>');

		if(import_confirmation) {

			Smoothbox.open("<div><center><b>" + '<?php echo $this->string()->escapeJavascript($this->translate("Importing Blogs...")) ?>' + "</b><br /><img src='<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sitereview/externals/images/loader.gif' alt='' /></center></div>");

      var activity_blog = 0;
      if($('activity_blog').checked == true) {
        activity_blog = 1;
      }

			en4.core.request.send(new Request.JSON({
				url : en4.core.baseUrl+'admin/sitereview/importlisting',
				method: 'get',
				data : {
					'start_import' : 1,
					'blog_assigned_previous_id' : blog_assigned_previous_id,
          'listingtype_id' : '<?php echo $this->listingtype_id?>',
          'activity_blog' : activity_blog,
          'module' : 'blog',
					'format' : 'json'
				},
				onSuccess : function(responseJSON) {
					
					$('blog_import_button').style.display = 'none';
					$('blog_importlisting_elements').style.display = 'none';
					
					if (responseJSON.blog_assigned_previous_id < responseJSON.last_blog_id) {
						$('blog_import_again_button').style.display = 'block';
						blog_assigned_previous_id = responseJSON.blog_assigned_previous_id;
						
						$('blog_unsuccess_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitereview/externals/images/cross.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Sorry for this inconvenience !!")) ?>' + "<br />"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is interrupted due to some reason. Please click on 'Import Again' button to start the importing from the same point again.")) ?>'+"</span><br />";
					}
					else {
						$('blog_import_again_button').style.display = 'none';
						$('blog_unsuccess_message').style.display = 'none';
						$('blog_success_message').innerHTML = "<span style='background-image:url(<?php echo $this->layout()->staticBaseUrl ?>application/modules/Seaocore/externals/images/core/notice.png);'>"+'<?php echo $this->string()->escapeJavascript($this->translate("Importing is done succesfully.")) ?>'+"</span><br />";
					}
					Smoothbox.close();
				}
			}))
		}
	}

  function continueImporting(importElement) {

    if(importElement == 'price' ) {
      $('price_import_continue').style.display = "none";
      click1 = 1;
    }

    if(importElement == 'location') {
      $('location_import_continue').style.display = "none";
      click2 = 1;
    }

    if(importElement == 'description') {
      $('description_import_continue').style.display = "none";
      click3 = 1;
    }

    if(importElement == 'reviews') {
      $('reviews_import_continue').style.display = "none";
      click4 = 1;
    }

    if(importElement == 'overview') {
      $('overview_import_continue').style.display = "none";
      click5 = 1;
    }

    if(importElement == 'ownerreviews') {
      $('owner_reviews_import_continue').style.display = "none";
      click6 = 1;
    }

    if(click1 == 1 && click2 == 1 && click3 == 1 && (click4 == 1 || click4 == 2 || click4 == 3) && click5 == 1 && click6 == 1) {
      $('import_button').style.display = 'block';
      $('importlisting_elements').style.display = 'none';
    }

  }

  function recipeContinueImporting(importElement) {

    if(importElement == 'price' ) {
      $('recipe_price_import_continue').style.display = "none";
      click1 = 1;
    }

    if(importElement == 'location') {
      $('recipe_location_import_continue').style.display = "none";
      click2 = 1;
    }

    if(importElement == 'description') {
      $('recipe_description_import_continue').style.display = "none";
      click3 = 1;
    }

    if(importElement == 'reviews') {
      $('recipe_reviews_import_continue').style.display = "none";
      click4 = 1;
    }

    if(importElement == 'overview') {
      $('recipe_overview_import_continue').style.display = "none";
      click5 = 1;
    }

    if(importElement == 'ownerreviews') {
      $('recipe_owner_reviews_import_continue').style.display = "none";
      click6 = 1;
    }

    if(click1 == 1 && click2 == 1 && click3 == 1 && (click4 == 1 || click4 == 2 || click4 == 3) && click5 == 1 && click6 == 1) {
      $('recipe_import_button').style.display = 'block';
      $('recipe_importlisting_elements').style.display = 'none';
    }
  }
  
  function classifiedContinueImporting(importElement) {

    if(importElement == 'price' ) {
      $('classified_price_import_continue').style.display = "none";
      click1 = 1;
    }

    if(importElement == 'location') {
      $('classified_location_import_continue').style.display = "none";
      click2 = 1;
    }

    if(importElement == 'description') {
      $('classified_description_import_continue').style.display = "none";
      click3 = 1;
    }

    if(click1 == 1 && click2 == 1 && click3 == 1) {
      $('classified_import_button').style.display = 'block';
      $('classified_importlisting_elements').style.display = 'none';
    }
  }  

  function blogContinueImporting(importElement) {

    if(importElement == 'overview') {
      $('blog_overview_import_continue').style.display = "none";
      click3 = 1;
    }

    if(click3 == 1) {
      $('blog_import_button').style.display = 'block';
      $('blog_importlisting_elements').style.display = 'none';
    }
  }

  var is_error = '<?php echo $is_error;?>';
  if( is_error == 0) {
    if($('importlisting_elements'))
    $('importlisting_elements').style.display = 'none';
  }

  var recipe_is_error = '<?php echo $recipe_is_error;?>';
  if( recipe_is_error == 0) {
    if($('recipe_importlisting_elements'))
    $('recipe_importlisting_elements').style.display = 'none';
  }

  var blog_is_error = '<?php echo $blog_is_error;?>';
  if( blog_is_error == 0) {
    if($('blog_importlisting_elements'))
    $('blog_importlisting_elements').style.display = 'none';
  }

</script>