<?php include('header.php');?>
<style>
body {
  background:#555;
}
.console {
  width:640px;
  height:480px;
  background:#222;
  color:#fff;
  font-family:monospace;
  padding:10px;
  overflow-y: auto;
}
.console-input {
  width:640px;
  height:25px;
  background:#222;
  color:#fff;
  font-family:monospace;
  border:none;
  padding:10px;
}
input:focus,
select:focus,
textarea:focus,
button:focus {
    outline: none;
}
</style>

<div id="screen">
<div class="console">
  S*L*A*W Test frontend by Chris Bartek.<br><br>

  <em>register</em> [username] [password] [password again]<br>
  <em>login</em> [username] [password]<br>
  <em>list</em><br>
  <em>create</em> [name]<br>
  <em>join</em> [name]<br>

</div>
<input class="console-input"/>
</div>

<?php include('footer.php');?>
