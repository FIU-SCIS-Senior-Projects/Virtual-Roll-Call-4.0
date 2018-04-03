<script type="text/ng-template" id="warning-dialog.html">
  <div class="modal-header">
   <h3>You are Idle. Do Something!</h3>
  </div>
  <div idle-countdown="countdown" ng-init="countdown=60" class="modal-body">
   <p align="center">You will be logged out in {{countdown}} second(s).</p>
  </div>
</script>

<script type="text/ng-template" id="timedout-dialog.html">
  <div class="modal-header">
   <h3>You Have Timed Out!</h3>
  </div>
  <div class="modal-body">
    <p align="center">You were idle too long. Good bye!</p>
    <center><a href='../app/php/logout.php' ng-click="logout()">
      <span class='glyphicon glyphicon-log-out'></span> Ok </a> 
    </center>
 </div>
</script>
