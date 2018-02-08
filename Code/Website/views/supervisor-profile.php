<?php session_start(); ?>
<!DOCTYPE html>
<html ng-app='supervisor' lang='en'>

<head>
  <meta charset='utf-8'>
  <title>VRC | Supervisor Dashboard</title>
  <meta name='description' content=''>
  <meta name='viewport' content='width=device-width, initial-scale=1.0, shrink-to-fit=no'>

  <!-- STYLE SHEETS -->
  <link href='style/normalize.css' rel='stylesheet' media='all'>
  <link rel='stylesheet' href='../app/vendor/3.3.7.bootstrap.min.css' media='all'>
  <link href='style/bootstrap.min.css' rel='stylesheet' media='all'>
  <link href='style/supervisor.css' rel='stylesheet' media='all'>
  <link rel='stylesheet' href='https://cdn.rawgit.com/angular-ui/bower-ui-grid/master/ui-grid.min.css' media='all'>

  <!-- SCRIPTS -->
  <script src='../app/vendor/1.12.4.jquery.min.js'></script>
  <script src='../app/vendor/3.3.7.bootstrap.min.js'></script>
  <script src="../app/vendor/1.5.8.angular.js"></script>
  <script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>

  <!--UI-GRID-CDN-->
  <script src="../app/vendor/ui-grid.min.js"></script>

  <!--PDF-MAKE-CDN-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.31/pdfmake.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.31/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.31/pdfmake.min.js.map"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.31/vfs_fonts.js"></script>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAuubk4Obni7qiK7Umj7CdvUUxO23688cM"></script>
  <script src="../app/vendor/papaparse.min.js"></script>
  <script src='../app/vendor/angular-route.min.js'></script>
  <script src='../app/vendor/jquery.table2excel.js'></script>
  <script src='../app/vendor/angular-local-storage.min.js'></script>
  <script src='../app/vendor/ui-bootstrap-tpls-2.2.0.min.js'></script>
  <script src='../app/app.js' type='text/javascript'></script>
  <script src='../app/controllers/shared-ctrl.js' type='text/javascript'></script>
  <script src='../app/controllers/supervisor-ctrl.js' type='text/javascript'></script>
  <script src='../app/services/data-service.js' type='text/javascript'></script>
  <script src="../app/vendor/ng-flow/dist/ng-flow-standalone.min.js"></script>
  <style> .grid { width: 100%; height: 750px; } </style>
</head>

<body class= "{{display_mode}}" ng-controller='supervisorCtrl' ng-init='getAppName()'>

  <!-- Vertical Navigation -->
  <nav class='navbar navbar-default'>
    <div class='container-fluid'>
      <div class='navbar-header'>
        <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='#myNavbar'>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
          <span class='icon-bar'></span>
        </button>
        <a class='navbar-brand' href='#'>Virtual Roll Call</a>
      </div>
      <div class='collapse navbar-collapse' id='myNavbar'>
      <ul class='nav navbar-nav'>
        <?php
         if (isset($_SESSION["officer_role"]) && $_SESSION["officer_role"] == 'Administrator')
          echo "<li><a href='admin-profile.php'>" . $_SESSION["officer_role"] . "</a></li>";
        ?>
        <li class='active'><a class='active'>Supervisor</a></li>
        <li><a href='officer-profile.php'>Officer</a></li>
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

  <!-- Horizontal Navigation -->
  <div class='container-fluid text-center'>
    <div class='row content'>
      <div class='col-sm-2 sidenav {{display_mode}}'>
        <ul class='nav nav-pills nav-stacked'>
          <li data-ng-class="{active: isActive('/upload')}"><a class='active' href='#upload'>Manage Documents</a></li>
          <li data-ng-class="{active: isActive('/manage-freetext')}"><a href='#manage-freetext'>Manage Free Messages</a></li>
          <li data-ng-class="{active: isActive('/manage-watch-orders')}"><a class='active' href='#manage-watch-orders'>Manage Watch Orders</a></li>
          <li data-ng-class="{active: isActive('/reset')}"><a href='#reset'>Reset Password</a></li>
          <li data-ng-class="{active: isActive('/log')}"><a href='#log'>Logs</a></li>
        </ul>
      </div>

      <!-- content will change according to route -->
      <section ng-view></section>

    </div>
  </div>
  <script>
    $(document).ready(function () {
      var table = $('#logs').DataTable();
      $('#btn-export').on('click', function () {
        $('#logs').append(table.$('tr').clone()).table2excel({
          exclude: ".excludeThisClass",
          name: "Worksheet Name",
          filename: "SomeFile" //do not include extension
        });
      });
    });
  </script>

</body>
</html>
