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
    <li><h3>Update Your Account Information</h3></li>
    <li>
      <label class="short" for="login[first_name]">First name:</label>
      <input type="text" id="login-email" name="login[first_name]" value="<?php echo $data['account']->firstName ?>" />
    </li>
    <li>
      <label class="short" for="login[last_name]">Last name:</label>
      <input type="text" id="login-email" name="login[last_name]" value="<?php echo $data['account']->lastName ?>" />
    </li>
    <li>
      <label class="short" for="login[email]">Email:</label>
      <input type="text" id="login-email" name="login[email]" value="<?php echo $data['account']->email ?>" />
    </li>
    <li>
      <label class="short" for="login[email]">Username:</label>
      <input type="text" id="login-email" name="login[username]" value="<?php echo $data['account']->username ?>" />
    </li>
    <li>
      <h3>Update Your Password</h3>
      <p>Leave blank to keep current password.</p>
    </li>
    <li>
      <label class="short" for="login[password]">Password:</label>
      <input type="password" id="account-password" name="login[password]" value="" />
      <p class="description">Enter a new password.</p>
    </li>
    <li>
      <label class="short" for="login[password2]">&nbsp;</label>
      <input type="password" id="account-password2" name="login[password2]" value="" />
      <p class="description">Repeat new password</p>
    </li>
    <li>
      <label class="short" for"submit">&nbsp;</label>
      <input type="submit" name="submit" value="Save" class="Cart66ButtonPrimary" />
    </li>
  </ul>
</form>

<?php if($data['url']): ?>
<p id="accountManagementLink"><a href="<?php echo $data['url'] ?>"><?php echo $data['text'] ?></a></p>
<?php endif; ?>