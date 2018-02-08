<?php session_start(); ?>
<!DOCTYPE html>
<html ng-app='admin' lang='en'>
<head>
   <meta charset='utf-8'>
  <title>Admin Dashboard</title>
  <meta name='description' content=''>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, shrink-to-fit=no'>

  <!-- STYLE SHEETS -->
  <link href='style/normalize.css' rel='stylesheet' media='all'>
  <link rel='stylesheet' href='../app/vendor/3.3.7.bootstrap.min.css' media='all'>
  <link href='style/bootstrap.min.css' rel='stylesheet' media='all'>
  <link href='style/admin.css' rel='stylesheet' media='all'>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <!-- SCRIPTS -->
  <script src='../app/vendor/1.12.4.jquery.min.js'></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script src='../app/vendor/3.3.7.bootstrap.min.js'></script>
  <script src="../app/vendor/1.5.8.angular.js"></script>

    <!--UI-GRID-CDN-->
  <script src="../app/vendor/ui-grid.min.js"></script>
  <script src="../app/vendor/papaparse.min.js"></script>
  <script src="../app/vendor/bootbox.min.js"></script>
  <script src='../app/vendor/angular-route.min.js'></script>
  <script src='../app/vendor/angular-local-storage.min.js'></script>
  <script src='../app/vendor/ui-bootstrap-tpls-2.2.0.min.js'></script>
  <script src='../app/app.js' type='text/javascript'></script>
  <script src='../app/controllers/shared-ctrl.js' type='text/javascript'></script>
  <script src='../app/controllers/admin-ctrl.js' type='text/javascript'></script>
  <script src='../app/services/data-service.js' type='text/javascript'></script>
  <script src="../app/vendor/ng-flow/dist/ng-flow-standalone.min.js"></script>

    <script>
  $( function() {
    var dateFormat = "mm/dd/yy",
      from = $( "#from" )
        .datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 3
        })
        .on( "change", function() {
        alert("Hee");
          to.datepicker( "option", "minDate", getDate( this ) );
        }),
      to = $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3
      })
      .on( "change", function() { from.datepicker( "option", "maxDate", getDate( this ) ); });

    function getDate( element ) {
      var date;
      try { date = $.datepicker.parseDate( dateFormat, element.value ); } 
      catch( error ) { date = null; }
      return date;
    }
  });
  </script>

</head>
<body class= "{{display_mode}}" ng-controller='adminCtrl' ng-init='getSiteNames()'>

  <!-- Vertical Navigation -->
  <nav class='navbar navbar-default'>
    <div class='container-fluid'>
      <div class='navbar-header'>
        <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='#myNavbar'>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
        </button>
        <a class='navbar-brand' href='#'>{{app_name}}</a>
      </div>
      <div class='collapse navbar-collapse' id='myNavbar'>
        <ul class='nav navbar-nav'>
          <li class='active'><a>Administrator</a></li>
          <li><a href='supervisor-profile.php#/upload'>Supervisor</a></li>
          <li><a href='officer-profile.php#/categories'>Officer</a></li>
        </ul>
        <ul class='nav navbar-nav navbar-right'>
          <li class='dropdown'>
            <a class='dropdown-toggle' data-toggle='dropdown'>{{name}} {{login}} <b class='caret'></b></a>
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

  <!-- Horizontal Navigation -->
  <div class='container-fluid text-center'>
    <div class='row content'>
      <div class='col-sm-2 sidenav {{display_mode}}'>
        <ul class='nav nav-pills nav-stacked'>
          <li data-ng-class="{active: isActive('/user-management')}"><a href='#user-management'>User Management</a></li>
          <li data-ng-class="{active: isActive('/categories')}"><a href='#categories'>Document Categories</a></li>
          <li data-ng-class="{active: isActive('/settings')}"><a href='#settings'>Site Settings</a></li>
          <li data-ng-class="{active: isActive('/archive')}"><a href='#archive'>Archive Documents</a></li>
        </ul>
      </div>
      <div class='col-sm-10' >
      <!-- content will change according to route -->
      <section ng-view></section>

      </div>
    </div>
  </div>
</body>
</html>
