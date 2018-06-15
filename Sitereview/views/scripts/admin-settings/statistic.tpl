<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: statistic.tpl 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
 ?>

<script type="text/javascript">
  var changeListingType =function(listingtype_id){
    window.location.href= en4.core.baseUrl+'admin/sitereview/settings/statistic/listingtype_id/'+listingtype_id;
  }
</script>

<h2>
  <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) { echo $this->translate('Reviews & Ratings - Multiple Listing Types Plugin'); } else { echo $this->translate('Reviews & Ratings Plugin'); }?>
</h2>

<?php if( count($this->navigation) ): ?>
	<div class='seaocore_admin_tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?> </div>
<?php endif; ?>
<div class='clear'>
  <div class='settings'>
    <form class="global_form">
      <div>
        <h3><?php echo $this->translate('Statistics for Listings');?></h3>
        <p class="description"> <?php echo $this->translate('Below are some valuable statistics for the Listings submitted on this site.');?>
        </p>
        <br />
        <?php $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes(); ?>
        <?php if(Count($listingTypes) > 1): ?>
          <div>
            <label>
              <b><?php echo $this->translate("Listing Type:") ?></b>
            </label>
            <select onchange="changeListingType($(this).value)" class="sitereview_cat_select" name="listingtype_id">   
              <option value="0">All Types</option>            
              <?php $listinTypesArray[0] = "All Types"; ?>
              <?php foreach ($listingTypes as $listingType): ?>
                <?php $listinTypesArray[$listingType->listingtype_id] = $listingType->title_plural; ?>
                <option value="<?php echo $listingType->listingtype_id;?>" <?php if( $this->listingtype_id == $listingType->listingtype_id) echo "selected";?>><?php echo $this->translate($listingType->title_plural);?>
                </option>
              <?php endforeach; ?>
            </select>
          </div><br />        
        <?php endif; ?>
        
        <table class='admin_table sr_statistics_table' width="100%">
          <tbody>
            <tr>
            	<td width="50%"><?php echo $this->translate("Total $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalSitereview ?></td>
            </tr>          
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Editors");?> :</td>
            	<td><?php echo $this->totalEditors ?></td>
            </tr>                        
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Published $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalPublish ?></td>
            </tr>
            <tr>
            	<td width="50%"><?php echo $this->translate("Total $this->listing_plural_uc in Draft ");?> :</td>
            	<td><?php echo $this->totalDrafted ?></td>
            </tr>
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Closed $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalClosed ?></td>
            </tr>
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Open $this->listing_plural_uc");?> :</td>
            	<td>
	            	<?php echo $this->totalOpen ?>
            	</td>
            </tr>
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Approved $this->listing_plural_uc");?> :</td>
            	<td>
            		<?php echo $this->totalapproved ?>
            	</td>
            </tr>	
            
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Disapproved $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totaldisapproved ?></td>
            </tr>
            
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Featured $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalfeatured ?></td>
            </tr>
            
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Sponsored $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalsponsored ?></td>
            </tr>
     
           <tr>
            	<td width="50%"><?php echo $this->translate('Total Editor Reviews');?> :</td>
            	<td><?php echo $this->totalEditorReviews ?></td>
           </tr>
           
           <tr>
            	<td width="50%"><?php echo $this->translate('Total Editor Reviews in Draft');?> :</td>
            	<td><?php echo $this->totalDraftEditorReviews ?></td>
           </tr>           
      
           <tr>
            	<td width="50%"><?php echo $this->translate('Total User Reviews');?> :</td>
            	<td><?php echo $this->totalUserReviews ?></td>
           </tr>
           
           <tr>
            	<td width="50%"><?php echo $this->translate('Total Approved Non-logged in user Reviews');?> :</td>
            	<td><?php echo $this->totalApprovedVisitorsReviews ?></td>
           </tr>
      
           <tr>
            	<td width="50%"><?php echo $this->translate('Total Dis-approved Non-logged in user Reviews');?> :</td>
            	<td><?php echo $this->totalDisApprovedVisitorsReviews ?></td>
           </tr>              
            
           <tr>
            	<td width="50%"><?php echo $this->translate('Total Reviews');?> :</td>
            	<td><?php echo $this->totalReviews ?></td>
            </tr>
            
           <tr>
            	<td width="50%"><?php echo $this->translate('Total Discussions');?> :</td>
            	<td><?php echo $this->totalDiscussionTopics ?></td>
            </tr>
            
            <tr>
            	<td width="50%"><?php echo $this->translate('Total Discussions Posts');?> :</td>
            	<td><?php echo $this->totalDiscussionPosts ?></td>
            </tr>

            <tr>
            	<td width="50%"><?php echo $this->translate("Total Photos in $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalPhotos ?></td>
            </tr>
            
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Videos in $this->listing_plural_uc");?> :</td>
            	<td><?php echo $this->totalVideos ?></td>
            </tr>
            
            <tr>
							<td width="50%"><?php echo $this->translate('Total Comments Posts');?> :</td>
            	<td><?php echo $this->totalListingComments ?></td>
            </tr>
            
            <tr>
							<td width="50%"><?php echo $this->translate('Total Likes');?> :</td>
            	<td><?php echo $this->totalListingLikes ?></td>
            </tr>
            
            <tr>
            	<td width="50%"><?php echo $this->translate("Total Wishlists");?> :</td>
            	<td><?php echo $this->totalWishlists ?></td>
            </tr>
            
          </tbody>
        </table>
      </div>
    </form>
  </div>
</div>