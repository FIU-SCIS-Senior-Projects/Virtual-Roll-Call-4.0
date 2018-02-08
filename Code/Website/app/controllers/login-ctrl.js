//CONTROLLER for login
loginModule.controller('loginCtrl', ['$scope', 'localStorageService', 'dataService', '$window', '$controller', function($scope, localStorageService, dataService, $window, $controller) {

  /***** SHARED FUNCTIONS *****/
  var sharedCtrl = $controller('sharedCtrl', {$scope: $scope});

  /***** ALERT FUNCTIONS *****/
  //alert functions (displays accordingly in views)
  $scope.alert = sharedCtrl.alert;
  $scope.login_count = Array();
  $scope.login_count.push(0);
  $scope.tries = ["First (1st)", "Second (2nd)", "Third (3rd)", "Fourth (4th)", "Last"];
  $scope.maxTrials = 5;

  $scope.getSiteNames = function(){
    sharedCtrl.getSiteNames();
  };

   //when login button is clicked...
  $scope.login = function(){

      //get values from the login input fields
      var username = $scope.username;
      var password = $scope.password;
      
      var count = $scope.login_count[$scope.login_count.length-1];
      //authenticate user
      dataService.login(username, password)
      .then(
          //http post request succeeded 
          function(data){
             //if username/password found in the database
             if(data['Username'] && data['Password'])
             {
                 //saved user id and name to local storage
                 localStorageService.set('id', data['userID']);
                 localStorageService.set('fname', data['First_Name']);
                 localStorageService.set('lname', data['Last_Name']);
                 localStorageService.set('role', data['Role']);

                 var locks = data['Lock_Count'] == null ? 0 : parseInt(data['Lock_Count']);
                 
                 if ( locks > 0 ) 
                    dataService.resetLock(data['userID']);

                 //route to home page according to role
                 if(data['Role'] === 'Administrator')
                 {
                    $window.location.href = 'admin-profile.php';
                    localStorageService.set('login','administrator');
                 }
                 else if (data['Role'] === 'Supervisor'){
                    $window.location.href = 'supervisor-profile.php';
                    localStorageService.set('login','supervisor');
                 }
                 else{
                    $window.location.href = 'officer-profile.php';
                    localStorageService.set('login','officer');
                 }
             }
             else
             {
                  dataService.getTally(username)
                    .then( function(logs) { 

                          count = logs['count'] == null ? 1 : parseInt(logs['count']) + 1;
                          var found  = logs['count'] != null;
                          var locked = logs['locked'] == null ? 0 : parseInt(logs['locked']);
                          var userid = logs['userid'];
                          var created = logs['created'];

                          if ( logs['created'] != null)  {
                            created = new Date( logs['created'].replace(' ', 'T') );
                            var now = new Date();
                            var minutes = ((now - created) / 60000) - now.getTimezoneOffset();

                            if( minutes > 60 ) {
                                dataService.resetLock(data['userID']);
                                count = 1;
                            }
                          }
                          $scope.alert.closeAll();
                          if (userid != null)
                          {
                              if ( count < $scope.maxTrials )
                              {
                                  $scope.alert.addAlert('danger', $scope.tries[count-1] + " Warning: Invalid credentials : Please try again!");
                                  dataService.updateFailedLog(found, userid, count);
                              }
                              else
                              {
                                  if ( !locked ) 
                                    dataService.lockUser(userid);
                                  $scope.alert.addAlert('danger', "This account has been locked: Please contact your Supervisor!");
                              }
                          }
                          else
                              $scope.alert.addAlert('danger', "'" + username + "'' is not a valid username. Try again or contact your Supervisor!");
                      });
              }
            },
          //http post request failed
          function(error){
            console.log('Error: ' + error);
          });          
  };

  $scope.logout = function (){
      sharedCtrl.logout();
  };

}]);
