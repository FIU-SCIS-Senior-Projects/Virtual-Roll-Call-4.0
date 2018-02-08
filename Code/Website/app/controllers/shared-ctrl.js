sharedModule.controller('sharedCtrl', ['$scope', 'sharedService', 'localStorageService', '$window', '$location', function ($scope, sharedService, localStorageService, $window, $location) {

  var self = this;

  /***** GLOBALS *****/
  $scope.password_pattern = '^[a-zA-Z0-9]{8,}$';
  $scope.pattern_descr = 'Must contain at least 8 or more characters. Only alphanumeric characters allowed.';

  /***** ALERT FUNCTIONS *****/
  //alert functions (displays accordingly in views)
  self.alert = {
    alerts: [],
    addAlert: function (type, msg) { $scope.alert.alerts.push({ type: type, msg: msg }); },
    closeAlert: function (index) { $scope.alert.alerts.splice(index, 1); },
    closeAll: function () { $scope.alert.alerts = []; }
  };

  self.logout = function () {
    localStorageService.set('login', '');
    localStorageService.set('id', "");
    localStorageService.set('fname', "");
    localStorageService.set('lname', "");
    localStorageService.clearAll();
    delete $scope.login;
  };


  self.redirect = function (login) {
    if (login == "")
      $window.location.href = 'index.html';
  };

  /***** GET APP NAME *****/
  self.getSiteNames = function () {
    sharedService.getSiteNames()
      .then(
      function (data) {
        $scope.app_name = data['app_name'];
        $scope.dept_name = data['dept_name'];
      },
      function (error) {
        console.log('Error: ' + error);
      });
  };

  /***** CHANGE PASSWORD *****/
  $scope.changePassword = function () {

    //get values from the change password input fields
    var id = localStorageService.get('id');
    var current_password = $scope.curr_pass;
    var new_password = $scope.new_pass;
    var conf_new_password = $scope.conf_new_pass;

    //check for new password/confirmation mismatch
    if (new_password !== conf_new_password) {
      $scope.alert.closeAll();
      $scope.alert.addAlert('danger', "The new passwords don\'t match!");
    } else {
      sharedService.changePassword(id, current_password, new_password)
        .then(
        function (data) {

          //check for successful update and notify user accordingly
          if (data['Updated'] === true) {
            $scope.alert.closeAll();
            $scope.alert.addAlert('success', 'Your password was successfully updated!');
            $scope.curr_pass = $scope.new_pass = $scope.conf_new_pass = '';
          }
          else {
            //current password was incorrect
            $scope.alert.closeAll();
            $scope.alert.addAlert('danger', 'Invalid credentials. Please try again.');
            $scope.curr_pass = $scope.new_pass = $scope.conf_new_pass = '';
          }
        },
        //http post request failed
        function (error) {
          console.log('Error: ' + error);
        });
    }
  };

  //Get all the documents Categories
  self.getCategories = function ()
  {
      sharedService.getCategories()
        .then(
        function (data) {
            var categories = [];
            var doc_categories = [];
            for (var x in data)
            {
                var tmp = new Object();
                tmp.id = data[x].id;
                tmp.name = data[x].name;
                categories.push(tmp);
                if ( data[x].name !== "Free Text")
                  doc_categories.push(tmp);
            }
            $scope.categories = categories;
            $scope.doc_categories = doc_categories;
        },
        function (error) {});
  };

  self.getPendingCount = function (id)
  {
      sharedService.getPendingCount( id )
        .then( function (data) {
              var dict = {};
              for (var x in data)
                  dict[ data[x].category ] = data[x].pending;
              $scope.pending_count = dict;
            },
            function (error) { alert('Error: ' + error); });
  };

  /***** GET ALL USERS *****/
  self.getOfficers = function () {
    sharedService.getOfficers()
      .then( function (data) {
        //initialize an empty array to store results from the database
        var officers = [];
        //for each officer in the result
        for (var x in data) {
          //create an object and set object properties (i.e. officer data)
          var tmp = new Object();
          tmp.id = data[x].id;
          tmp.firstName = data[x].firstName;
          tmp.lastName = data[x].lastName;
          tmp.username = data[x].username;
          tmp.role = data[x].role;
          //store results in officers
          officers.push(tmp);
        }
        //update value in view for use in ng-repeat (to populate)
        $scope.officers = officers;
      },
      function (error) {
        console.log('Error: ' + error);
      });
  };

  /***** GET ALL MESSAGES *****/
  self.getMessages = function() {
    var id = localStorageService.get('id');

    sharedService.getMessages( id )
      .then(
        function (data) {
          var messages = [];

          for ( var x in data ) {
            var tmp = new Object();
            tmp.id = data[x].id;
            tmp.title = data[x].name;
            tmp.message = data[x].message;
            tmp.description = data[x].msg_description;
            tmp.officer_id = data[x].created_by;
            tmp.created_at = data[x].date;
            tmp.updated_at = data[x].updated_at;
            tmp.updated_by = data[x].updated_by;
              messages.push(tmp);
          }
          $scope.messages = messages;
      },
      function (error) {
        console.log('Error: ' + error);
      });
  };

  /***** GET ALL DOCUMENTS *****/
  self.getDocuments = function () {
    sharedService.getDocuments()
      .then(
      function (data) {
        //initialize an empty array to store results from the database
        var documents = [];
        //for each category in the result
        for (var x in data) {
          //create an object and set object properties (i.e. documents data)
          var tmp = new Object();
          tmp.id = data[x].id;
          tmp.name = data[x].name;
          tmp.cat_name = data[x].category;
          tmp.catid = data[x].catid;
          tmp.date = data[x].date;
          tmp.pinned = data[x].pinned;
          tmp.uploadedBy = data[x].uploadedBy;
          tmp.archived = data[x].archived;
          //store results in categories
          documents.push(tmp);
        }
        //update value in view for use in ng-repeat (to populate)
        $scope.documents = documents;
      },
      function (error) {
        console.log('Error: ' + error);
      });
  };

  self.updateDocument = function (id, categorie, name, pinned) {
    sharedService.updateDocument(id, categorie, name, pinned)
      .then(
      function (data) {
        if (data) {
          //$('#editModal').modal('hide');
          window.location.reload();
        }
      },
      function (error) {
        console.log(error);
      });
  };

  /*Night mode toggle */
  self.changeDisplayMode = function(){
    if(localStorageService.get('nightMode')){
      localStorageService.set('nightMode', false);
    }
    //else: daytime set, or not in local storage
    else {
      localStorageService.set('nightMode', true);
    }
    $scope.display_mode = self.getDisplayMode();
    $scope.night_mode = localStorageService.get('nightMode');

    window.location.reload();

  };

  self.getDisplayMode = function(){
    if(localStorageService.get('nightMode')){
      return "night-mode";
    }
    else {
        return "day-mode";
    }
  };

  self.getlogs = function () {

    sharedService.getlogs()
      .then(
      function (data) {
        //initialize an empty array to store results from the database
        var logs = [];
        //for each category in the result
        for (var x in data) {
          //create an object and set object properties (i.e. documents data)
          var tmp = new Object();
          tmp.Full_Name = data[x].Full_Name;
          tmp.Document_Name = data[x].Document_Name;
          tmp.DOC = data[x].DOC;
          tmp.Uploaded = data[x].Uploaded;
          tmp.Started = data[x].Started;
          tmp.Completed = data[x].Completed;
          tmp.Duration = data[x].Duration;
          tmp.Status = data[x].Status;
          tmp.pinned = data[x].pinned;
          tmp.uploadedBy = data[x].uploadedBy;
          //store results in categories
          logs.push(tmp);
        }
        //update value in view for use in ng-repeat (to populate)
        $scope.logs = logs;

        //broadcast event to fill grid data
        $scope.$broadcast('logs-data-ready', $scope.logs);
      },
      function (error) {
        console.log('Error: ' + error);
      });
  };
  return self;

}]);
