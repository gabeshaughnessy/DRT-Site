<?php if(Cart66Common::isLoggedIn()): ?>
  <p>Hi <?php echo $data['account']->firstName; ?>. You are currently logged in.  
  <a href="<?php echo Cart66Common::appendQueryString('cart66-task=logout'); ?>">Log out</a></p>
<?php else: ?>
  <form id="Cart66AccountLogin" class="phorm2" action="" method="post">
    <input type="hidden" name="cart66-task" value="account-login" />
    <input type="hidden" name="redirect" value="<?php echo $data['redirect'] ?>">
    <ul>
      <li>
        <label class="" for="login[username]">Username:</label>
        <input type="text" id="login-username" name="login[username]" value="" />
      </li>
      <li>
        <label class="" for="login[password]">Password:</label>
        <input type="password" id="login-password" name="login[password]" value="" />
      </li>
      <li>
        <label class="" for"submit">&nbsp;</label>
        <input type="submit" name="submit" value="Enter" class="Cart66ButtonPrimary" />
        <a href='#' id='forgotLink'>Forgot my password</a>
      </li>
    </ul>
  </form>

  <form id="Cart66ForgotPassword" class="phorm2" action="" method='post'>
    <input type="hidden" name="cart66-task" value="account-reset" />
    <p class='Cart66Note'>Enter your username and we will send you a new password.<br/> 
      The email will be sent to the email address you used for your account.</p>
    <ul>
      <li>
        <label class="" for="login[username]">Username:</label>
        <input type="text" id="login-username" name="login[username]" value="" />
      </li>
      <li>
        <label class="" for"submit">&nbsp;</label>
        <input type="submit" name="submit" value="Send New Password" class="Cart66ButtonPrimary" />
      </li>
    </ul>
  </form>
  
  <script type='text/javascript'>
    jQuery(document).ready(function($) {
      $('#forgotLink').click(function() {
        $('#Cart66ForgotPassword').toggle();
      });
    });
  </script>
  
<?php endif; ?>

<?php if(isset($data['resetResult'])): ?>
  <?php $messageClass = ($data['resetResult']->success) ? 'Cart66Success' : 'Cart66Error'; ?>
  <div id='msg' class='<?php echo $messageClass ?>' style='width: 300px; margin: 10px 0px;'><p><?php echo $data['resetResult']->message ?></p></div>
<?php endif; ?>