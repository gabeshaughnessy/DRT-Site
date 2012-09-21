<?php
if(isset($data['errors']) && count($data['errors'])) {
  echo Cart66Common::showErrors($data['errors'], "<p><b>We're sorry. Your account was not updated for the following reasons:</b></p>");
}
if(isset($data['message'])) {
  echo '<div class="Cart66Success">' . $data['message'] . '</div>';
}
?>

<form id="Cart66AccountLogin" class="phorm2" action="" method="post">
  <input type="hidden" name="cart66-task" value="account-update" />
  <ul class='shortLabels'>
    <li><h3><?php _e( 'Update Your Account Information' , 'cart66' ); ?></h3></li>
    <li>
      <label class="short" for="login-first_name"><?php _e( 'First name' , 'cart66' ); ?>:</label>
      <input type="text" id="login-first_name" name="login[first_name]" value="<?php echo $data['account']->firstName ?>" />
    </li>
    <li>
      <label class="short" for="login-last_name"><?php _e( 'Last name' , 'cart66' ); ?>:</label>
      <input type="text" id="login-last_name" name="login[last_name]" value="<?php echo $data['account']->lastName ?>" />
    </li>
    <li>
      <label class="short" for="login-email"><?php _e( 'Email' , 'cart66' ); ?>:</label>
      <input type="text" id="login-email" name="login[email]" value="<?php echo $data['account']->email ?>" />
    </li>
    <li>
      <label class="short" for="login-username"><?php _e( 'Username' , 'cart66' ); ?>:</label>
      <input type="text" id="login-username" name="login[username]" value="<?php echo $data['account']->username ?>" />
    </li>
    <li>
      <h3><?php _e( 'Update Your Password' , 'cart66' ); ?></h3>
      <p><?php _e( 'Leave blank to keep current password.' , 'cart66' ); ?></p>
    </li>
    <li>
      <label class="short" for="login-password"><?php _e( 'Password' , 'cart66' ); ?>:</label>
      <input type="password" id="login-password" name="login[password]" value="" />
      <p class="description"><?php _e( 'Enter a new password.' , 'cart66' ); ?></p>
    </li>
    <li>
      <label class="short" for="login-password2">&nbsp;</label>
      <input type="password" id="login-password2" name="login[password2]" value="" />
      <p class="description"><?php _e('Repeat new password', 'cart66'); ?></p>
    </li>
    <li>
      <label class="short" for="submit">&nbsp;</label>
      <input type="submit" name="submit" value="<?php _e('Save', 'cart66'); ?>" class="Cart66ButtonPrimary" />
    </li>
  </ul>
</form>

<?php if($data['url']): ?>
<p id="accountManagementLink"><a href="<?php echo $data['url'] ?>"><?php echo $data['text'] ?></a></p>
<?php endif; ?>