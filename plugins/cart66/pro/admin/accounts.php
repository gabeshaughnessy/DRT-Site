<form action="<?php echo Cart66Common::replaceQueryString('page=cart66-accounts'); ?>" method='post'>
  <input type='hidden' name='cart66-action' value='save account' />
  <input type='hidden' name='account[id]' value='<?php echo $data['account']->id ?>' />
  <input type='hidden' name='plan[id]' value='<?php if(isset($data['plan'])) { echo $data['plan']->id; } ?>' />
  
  <div id="widgets-left" style="margin-right: 50px;">
    <div id="available-widgets">
    
      <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3>Account <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder" style="overflow: hidden; display: block;">
          
          <?php
            if(isset($data['errors']) && is_array($data['errors']) && count($data['errors']) > 0) {
              echo '<div style="padding: 10px;">';
              echo Cart66Common::showErrors($data['errors'], 'Unable to update account:');
              echo '</div>';
            }
          ?>
          
          <ul style="float: left;">
            <li>
              <label class="med" for="account[first_name]">First name:</label>
              <input type="text" name="account[first_name]" value="<?php echo $data['account']->firstName; ?>" id="account-first_name" />
            </li>
            <li>
              <label class="med" for="account[last_name]">Last name</label>
              <input type="text" name="account[last_name]" value="<?php echo $data['account']->lastName; ?>" id="account-last_name" />
            </li>
            <li>
              <label class="med" for="account[email]">Email:</label>
              <input type="text" name="account[email]" value="<?php echo $data['account']->email; ?>" id="account-email" />
            </li>
            <li>
              <label class="med" for="account[username]">Username:</label>
              <input type="text" name="account[username]" value="<?php echo $data['account']->username; ?>" id="account-username" />
            </li>
            <li>
              <label class="med" for="account[password]">Password:</label>
              <input type="text" name="account[password]" value="" id="account-password" />
              <?php if($data['account']->id > 0): ?>
                <p class="label_desc">Leave blank unless changing</p>
              <?php endif; ?>
            </li>
          </ul>
          
          <ul style="float: left;">
            <li>
              <label class="med" for="plan[subscription_plan_name]">Plan name:</label>
              <input style="width: 375px;" type="text" name="plan[subscription_plan_name]" value="<?php echo htmlspecialchars($data['plan']->subscriptionPlanName); ?>" id="plan-subscription_plan_name" />
            </li>
            <li>
              <label class="med" for="plan[feature_level]">Feature level:</label>
              <input style="width: 375px;" type="text" name="plan[feature_level]" value="<?php echo $data['plan']->featureLevel; ?>" id="plan-feature_level" />
            </li>
            <li>
              <label class="med" for="plan[active_until]">Active until:</label>
              <input type="text" name="plan[active_until]" value="<?php echo $data['activeUntil']; ?>" id="plan-active_until" />
            </li>
            <li><label class="med" for='account[notes]'>Notes:</label><br/>
            <textarea style="width: 375px; height: 140px; margin-left: 130px; margin-top: -20px;" 
            id="account-notes" name="account[notes]"><?php echo $data['account']->notes; ?></textarea>
            <p style="margin-left: 130px;" class="description">Notes about this account - not viewable by customer.</p></li>
          </ul>
          
          <ul style="clear: both;">
            <li>
              <label class="med" for="submit">&nbsp;</label>
              <?php if($data['account']->id > 0): ?>
                <a href='?page=cart66-accounts' class='button-secondary linkButton' style="">Cancel</a>
              <?php endif; ?>
              <input type="submit" name="Save" value="Save" id="submit" class="button-primary" style='width: 60px;' />
            </li>
          </ul>
          
        </div>
      </div>
      
    </div>
  </div>
</form>

<?php if(isset($data['accounts']) && is_array($data['accounts'])): ?>
  <table class="widefat" style="width: 95%" id="Cart66AccountList">
    <thead>
      <tr>
        <th colspan="8">Search: <input type="text" name="Cart66AccountSearchField" value="" id="Cart66AccountSearchField" /></th>
      </tr>
      <tr>
        <th>Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Subscription Name</th>
        <th>Feature Level</th>
        <th>Active Until</th>
        <th>Type</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($data['accounts'] as $acct): ?>
        <?php
          $editLink = $data['url'] . '&accountId=' . $acct->id;
          $deleteLink = $data['url'] . '&accountId=' . $acct->id . '&cart66-action=delete_account';
          $planName = 'No Active Subscription';
          $featureLevel = 'No Access';
          $activeUntil = 'Expired';
          if($sub = $acct->getCurrentAccountSubscription(true)) {
            $planName = $sub->subscriptionPlanName;
            $featureLevel = $sub->isActive() ? $sub->featureLevel : 'No Access - Expired';
            $activeUntil = date('m/d/Y', strtotime($sub->activeUntil));
            $type = $sub->isPayPalSubscription() ? 'PayPal' : 'Manual';
          }
          else {
            $planName = 'No plan available';
            $featureLevel = 'No feature level';
            $activeUntil = 'No Access';
            $type = 'None';
          }
        ?>
        <tr>
          <td><?php echo $acct->firstName ?> <?php echo $acct->lastName ?></td>
          <td><?php echo $acct->username ?></td>
          <td><a href="mailto:<?php echo $acct->email ?>"><?php echo $acct->email ?></a></td>
          <td><?php echo $planName; ?></td>
          <td>
            <?php echo $featureLevel; ?>
          </td>
          <td><?php echo $activeUntil; ?></td>
          <td><?php echo $type; ?></td>
          <td>
            <a href="<?php echo $editLink; ?>">Edit</a> |
            <a href="<?php echo $deleteLink; ?>" class="delete">Delete</a>
          
            <?php if(!empty($acct->notes)): ?>
            | <a href="#" class="Cart66ViewAccountNote" rel="note_<?php echo $acct->id ?>">Notes</a>
            <div class="Cart66AccountNote" id="note_<?php echo $acct->id; ?>">
              <a href="#" class="Cart66CloseNoteView" rel="note_<?php echo $acct->id ?>" alt="Close Notes Window"><img src="<?php 
                echo WP_PLUGIN_URL ?>/cart66/images/window-close.png" /></a>
              <h3><?php echo $acct->firstName ?> <?php echo $acct->lastName ?></h3>
              <p><?php echo nl2br($acct->notes); ?></p>
            </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <script language='javascript'>
    jQuery(document).ready(function($) {
      $('.delete').click(function() {
        return confirm('Are you sure you want to permanently delete this account?');
      });
    
      $('.Cart66ViewAccountNote').click(function() {
        var id = $(this).attr('rel');
        $('#' + id).css('display', 'block');
        return false;
      });
    
      $('.Cart66CloseNoteView').click(function() {
        var id = $(this).attr('rel');
        $('#' + id).css('display', 'none');
        return false;
      });
    
      $('#Cart66AccountSearchField').quicksearch('table tbody tr');
    });
  </script>
<?php endif; ?>