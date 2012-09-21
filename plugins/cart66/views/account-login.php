<?php if(Cart66Session::get('zendesk_logout_error')): ?>
    <div class="alert-message">
      <?php _e('Zendesk logged you out with the following error','cart66'); ?>:<br>
      <?php echo Cart66Session::get('zendesk_logout_error'); ?>
    </div>
<?php 
      Cart66Session::drop('zendesk_logout_error');
endif; ?>
<?php if(Cart66Common::isLoggedIn()): ?>
  <p>Hi <?php echo $data['account']->firstName; ?>. <?php _e('You are currently logged in.', 'cart66'); ?>  
  <a href="<?php echo Cart66Common::appendQueryString('cart66-task=logout'); ?>"><?php _e('Log out', 'cart66'); ?></a></p>
<?php else: ?>
  <form id="Cart66AccountLogin" class="phorm2" action="" method="post">
    <input type="hidden" name="cart66-task" value="account-login" />
    <input type="hidden" name="redirect" value="<?php echo $data['redirect'] ?>">
    <ul>
      <li>
        <label class="" for="login-username"><?php _e( 'Username' , 'cart66' ); ?>:</label>
        <input type="text" id="login-username" name="login[username]" value="" />
      </li>
      <li>
        <label class="" for="login-password"><?php _e( 'Password' , 'cart66' ); ?>:</label>
        <input type="password" id="login-password" name="login[password]" value="" />
      </li>
      <li>
        <label class="" for="submit">&nbsp;</label>
        <input type="submit" name="submit" value="Enter" class="Cart66ButtonPrimary" />
        <a href='#' id='forgotLink'><?php _e( 'Forgot my password' , 'cart66' ); ?></a>
      </li>
    </ul>
  </form>

  <form id="Cart66ForgotPassword" class="phorm2" action="" method='post'>
    <input type="hidden" name="cart66-task" value="account-reset" />
    <p class='Cart66Note'><?php _e( 'Enter your username and we will send you a new password.<br/> The email will be sent to the email address you used for your account.' , 'cart66' ); ?></p>
    <ul>
      <li>
        <label class="" for="login-username"><?php _e( 'Username' , 'cart66' ); ?>:</label>
        <input type="text" id="login-username" name="login[username]" value="" />
      </li>
      <li>
        <input type="submit" name="submit" value="Send New Password" class="Cart66ButtonPrimary" />
      </li>
    </ul>
  </form>
  
  <script type="text/javascript">
    (function($){
      $(document).ready(function(){
        $('#forgotLink').click(function() {
          $('#Cart66ForgotPassword').toggle();
        });
      })
    })(jQuery);
  </script> 
  
<?php endif; ?>

<?php if(isset($data['resetResult'])): ?>
  <?php $messageClass = ($data['resetResult']->success) ? 'Cart66Success' : 'Cart66Error'; ?>
  <div id='msg' class='<?php echo $messageClass ?>' style='width: 300px; margin: 10px 0px;'><p><?php echo $data['resetResult']->message ?></p></div>
<?php endif; ?>