<?php echo $this->Html->script('login.js');?>

<div class="col-md-8 col-md-offset-2 panel panel-default top-margin">
    <div class="col-md-6 panel panel-default">
    <h2>Join the Battle !</h2>
    <div class="panel-body">
        <?php echo $this->Form->create('Signup');?>
        <div class="form-group">
          <?php echo $this->Form->input('Email address', array('class' => 'form-control'));?>
        </div>
        <div class="form-group">
          <?php echo $this->Form->input('Password', array('class' => 'form-control', 'type' => 'password'));?>
        </div>
        <div class="form-group">
          <?php echo $this->Form->input('Confirm Password', array('class' => 'form-control', 'type' => 'password'));?>
        </div>
        <div class="text-center">
        <?php echo $this->Form->submit('Sign Up', array('class' => 'btn btn-primary'));
        echo $this->Form->end();?></div>
    </div>
</div>

<div class="col-md-6  panel panel-default">
<h2>Login and Go Fight!</h2>
<div class="panel-body">
  <?php echo $this->Form->create('Login');?>
    <div class="form-group">
      <?php echo $this->Form->input('Email address', array('class' => 'form-control'));?>
    </div>
    <div class="form-group">
      <?php echo $this->Form->input('Password', array('class' => 'form-control', 'type'=>'password'));?>
    </div>
    <div class="text-center">
    <?php echo $this->Form->submit('Log In', array('class' => 'btn btn-primary'));
    echo $this->Form->end();?>
        <a>Password forgotten?</a>
    <fb:login-button scope="public_profile,email" onlogin="checkLoginState();">
</fb:login-button>
    </div>
  </div>
  <div id="status">
</div>

</div>
</div>
  