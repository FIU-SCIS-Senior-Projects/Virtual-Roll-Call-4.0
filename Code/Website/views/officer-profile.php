<?php session_start(); ?>
<!DOCTYPE html>
<html ng-app='officer' lang='en'>
<head>
   <meta charset='utf-8'>
  <title>VRC | Officer Dashboard</title>
  <meta name='description' content=''>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, shrink-to-fit=no'>

  <!-- STYLE SHEETS -->
  <link href='style/normalize.css' rel='stylesheet' media='all'>
  <link rel='stylesheet' href='../app/vendor/3.3.7.bootstrap.min.css' media='all'>
  <link href='style/bootstrap.min.css' rel='stylesheet' media='all'>
  <link href='style/officer.css' rel='stylesheet' media='all'>
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' media='all'>

  <!-- SCRIPTS -->
  <script src='../app/vendor/1.12.4.jquery.min.js'></script>
   <script src='../app/vendor/3.3.7.bootstrap.min.js'></script>
   <script src="../app/vendor/1.5.8.angular.js"></script>

  <!--UI-GRID-CDN-->
   <script src="../app/vendor/ui-grid.min.js"></script>
  <script src='../app/vendor/angular-route.min.js'></script>
  <script src='../app/vendor/angular-local-storage.min.js'></script>
  <script src='../app/vendor/ui-bootstrap-tpls-2.2.0.min.js'></script>
  <script src='../app/app.js' type='text/javascript'></script>
  <script src='../app/controllers/shared-ctrl.js' type='text/javascript'></script>
  <script src='../app/controllers/officer-ctrl.js' type='text/javascript'></script>
  <script src='../app/services/data-service.js' type='text/javascript'></script>
  <script src="../app/vendor/ng-flow/dist/ng-flow-standalone.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAuubk4Obni7qiK7Umj7CdvUUxO23688cM"></script>
  <link rel="stylesheet" href="partials/css/style.css">

</head>
<body class = "{{display_mode}}" ng-controller='officerCtrl' ng-init='getSiteNames()'>

  <!-- Vertical Navigation -->
  <nav class='navbar navbar-default'>
    <div class='container-fluid'>
      <div class='navbar-header'>
        <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='#myNavbar'>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
        </button>
        <a class='navbar-brand' href='#'>Virtual Roll Call {{t}} </a>
      </div>
      <div class='collapse navbar-collapse' id='myNavbar'>
        <ul class='nav navbar-nav'>
          <?php
            if (isset($_SESSION["officer_role"]) )
            {
                if ($_SESSION["officer_role"] == 'Administrator')
                    echo "<li><a href='admin-profile.php'>Administrator</a></li>";
                if ($_SESSION["officer_role"] == 'Administrator' || $_SESSION["officer_role"] == 'Supervisor')
                    echo "<li><a href='supervisor-profile.php'>Supervisor</a></li>";
            }
        ?>
          <li class='active'><a class='active'>Officer</a></li>
        </ul>
        <ul class='nav navbar-nav navbar-right'>
          <li class='dropdown'>
            <a class='dropdown-toggle' data-toggle='dropdown'>{{name}} <b class='caret'></b></a>
            <ul class='dropdown-menu'>
              <li>
                <a href='' ng-click="changeDisplayMode()"><span class='glyphicon glyphicon-adjust'></span> Change to {{night_mode? "Day" : 'Night' }} Mode</a>
              </li>
              <li>
                <a href='#password'><span class='glyphicon glyphicon-cog'></span> Change Password</a>
              </li>
              <li class='divider'></li>
              <li>
                <a href='../app/php/logout.php' ng-click="logout()"><span class='glyphicon glyphicon-log-out'></span> Log Out</a>
              </li>
            </ul>
          </li>

        </ul>
      </div>
    </div>
  </nav>

  <!-- content will change according to route -->
  <section ng-view></section>

</body>
</html>
