//CONTROLLER for admin app
adminModule.directive('mydatepicker', function () {
  return {
    restrict: 'A',
    require: 'ngModel',

    link: function ($scope, element, attrs, adminCtrl) {
      element.datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: function (from) {
          $scope.from = from;
          $scope.$apply();
        }
      });
    },
  };
});

adminModule.directive('mydatepickerto', function () {
  return {
    restrict: 'A',
    require: 'ngModel',
    link: function ($scope, element, attrs, adminCtrl) {
      element.datepicker({
        dateFormat: 'mm/dd/yy',
        onSelect: function (to) {
          $scope.to = to;
          $scope.$apply();
        }
      });
    }
  };
});


adminModule.controller('adminCtrl', ['$scope', 'dataService', 'localStorageService', '$window', '$controller', '$location', function($scope, dataService, localStorageService, $window, $controller, $location, Idle, Keepalive, $uibModal){

  $("[data-toggle=popover]").popover();
  $('[data-toggle="tooltip"]').tooltip();
  /***** GLOBALS *****/
  //get name from local storage for user profile customization
  var fname = localStorageService.get('fname');
  var lname = localStorageService.get('lname');
  if ( !$scope.role )
      $scope.role = localStorageService.get('role');
  $scope.login = localStorageService.get('login');

  $scope.name = fname + ' ' + lname;


  $(document).on('show.bs.modal', '.modal', function (event) {
    var zIndex = 1040 + (10 * $('.modal:visible').length);
    $(this).css('z-index', zIndex);
    setTimeout(function() {
      $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
    }, 0);
  });

  /***** SHARED FUNCTIONS *****/
  var sharedCtrl = $controller('sharedCtrl', {$scope: $scope});
  sharedCtrl.redirect($scope.login);

  /***** GET SITE NAME *****/
  $scope.getSiteNames = function(){
    sharedCtrl.getSiteNames();
  };

  $scope.deleteArchive = function (){
    dataService.deleteArchive($scope.from,$scope.to)
    .then(function(data){
      $scope.files_deleted = data;
    })
  }

  $scope.logout = function(){ sharedCtrl.logout(); }
  $scope.getOfficers = function(){ sharedCtrl.getOfficers(); };
  $scope.getShifts = function(){sharedCtrl.getShifts();};
  $scope.getActiveShifts = function(){sharedCtrl.getActiveShifts();};
  $scope.getCategories = function(){ sharedCtrl.getCategories(); };
  $scope.populateMultiShifts = function(){sharedCtrl.populateMultiShifts();};
  $scope.getTimeoutMinutes = function(){sharedCtrl.getTimeoutMinutes();};
  $scope.getSiteNames = function(){sharedCtrl.getSiteNames();};
  $scope.getLatLong = function(){sharedCtrl.getLatLong();};
  $scope.timeoutInit = function(){sharedCtrl.timeoutInit();};

  /***********************
  * Toggle between day and night mode*
  ***********************/
  $scope.changeDisplayMode = function changeDisplayMode() {
    sharedCtrl.changeDisplayMode();
  };

  function getDisplayMode(){
    return sharedCtrl.getDisplayMode();
  };

  $scope.display_mode = getDisplayMode();
  $scope.night_mode = localStorageService.get('nightMode');

  //alert functions (displays accordingly in views)
  $scope.alert = sharedCtrl.alert;


  /***** MULTISELECT GLOBAL SETTINGS *****/
  $scope.shiftModel = []; 
  $scope.shiftSettings = { 
    checkBoxes: true, 
    dynamicTitle: true, 
    showUncheckAll: true, 
    showCheckAll: true 
  };

  /***** ADMINISTRATOR FUNCTIONS *****/
  /***** APPLY ACTIVE BS CLASS *****/
  $scope.isActive = function(path) {
    return $location.path() === path; //TO DO: Pull this function into shared ctrl
  };
  $scope.user = {};

  /***** ADD NEW USER *****/
  $scope.addUser = function(){

    //get values from input fields
    var first_name = $scope.fName;
    var last_name = $scope.lName;
    var username = $scope.username;
    var password = $scope.password;
    var shift = $scope.selectedShift.id;
    var role = $scope.role;

    dataService.addUser(first_name, last_name, username, password, role, shift)
    .then(
      function(data){
        if(data['Added'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'User successfully added!');
          //clear input fields
          $scope.fName = $scope.lName = $scope.username = $scope.password = $scope.role = $scope.selectedShift ='';
          //update edit users table to reflect the addition of new user (TO DO: this can be improved; only get new users instead of all)
          sharedCtrl.getOfficers();
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not add user!');
        }
      },
      function(error){
        console.log('Error: ' + error);
  });};

  $scope.addMessage = function() {
      alert("here");
  };

  /***** REMOVE USER *****/
  $scope.removeUser = function(){
    //when delete button selected, prompt user for confirmation
    var deleteUser = $window.confirm('Are you sure you want to delete this user?');
    if(deleteUser){
      var id = $scope.updateID;
      dataService.removeUser(id)
      .then(
        function(data){
          if(data['Removed'] === true){
            $scope.alert.closeAll();
            $scope.alert.addAlert('success', 'User successfully deleted!');
            sharedCtrl.getOfficers();
            //window.location.reload();
            $('#editModal').modal('hide');
          }else{
            $scope.alert.closeAll();
            $scope.alert.addAlert('danger', 'Could not delete user!');
            //window.location.reload();
            $('#editModal').modal('hide');
          }
        },
        function(error){
          console.log('Error: ' + error);
  });}};


  /***** REMOVE SHIFT *****/
  $scope.removeShift = function(){
    //when delete button selected, prompt user for confirmation
    var deleteShift = $window.confirm('Are you sure you want to delete this shift?');
    if(deleteShift){
      var id = $scope.updateID;
      dataService.removeShift(id)
      .then(
        function(data){
          if(data['Removed'] === true){
            $scope.alert.closeAll();
            $scope.alert.addAlert('success', 'Shift successfully deleted!');
            sharedCtrl.getShifts();
            //window.location.reload();
            $('#editModal').modal('hide');
          }else{
            $scope.alert.closeAll();
            $scope.alert.addAlert('danger', 'Could not delete shift!');
            //window.location.reload();
            $('#editModal').modal('hide');
          }
        },
        function(error){
          console.log('Error: ' + error);
  });}};

  /***** EDIT USER MODAL *****/
  $scope.editUser = function(update_id, update_first, update_last, update_username, update_role, update_shift){
    $scope.updateID = update_id;
    $scope.updateFirst = update_first;
    $scope.updateLast = update_last;
    $scope.updateUsername = update_username;
    $scope.updateRole = update_role;
    $scope.updateShift = update_shift;
    $scope.oldShift = "Old shift: " + update_shift;
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $('#editModal').modal('show');
  };


   /***** EDIT SHIFT MODAL *****/
  $scope.editShift = function(id, shift_name, from_time, to_time, status){
    $scope.updateID = id;
    $scope.updateName = shift_name;
    $scope.updateFrom = from_time;
    $scope.updateTo = to_time;
    $scope.updateStatus = status;
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $('#editModal').modal('show');
  };

  /***** EDIT USER DATA *****/
  $scope.updateUser = function(){
    var id = $scope.updateID;
    var first_name = $scope.updateFirst;
    var last_name = $scope.updateLast;
    var username =$scope.updateUsername;
    var role = $scope.updateRole;
    var shift = $scope.updateShift.id;
    dataService.updateUser(id, first_name, last_name, username, role, shift)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'User successfully updated!');
          sharedCtrl.getOfficers();
          //window.location.reload();
          $('#editModal').modal('hide');
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update user!');
          //window.location.reload();
          $('#editModal').modal('hide');
        }
      },
      function(error){
        console.log('Error: ' + error);
  });};

  /***** EDIT SHIFT DATA *****/
  $scope.updateShift = function(){
    var id = $scope.updateID;
    var shift_name = $scope.updateName;
    var from_time = $scope.updateFrom;
    var to_time =$scope.updateTo;
    var status = $scope.updateStatus;

    dataService.updateShift(id, shift_name, from_time, to_time, status)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Shift successfully updated!');
          sharedCtrl.getShifts();
          $('#editModal').modal('hide');
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update shift!');
          $('#editModal').modal('hide');
        }
      },
      function(error){
        console.log('Error: ' + error);
  });};

  /***** BATCH ADD USERS MODAL *****/
  $scope.addBatchUsers = function(){
    document.getElementById("parsedUsersPanel").style.display = "none";
    document.getElementById("successMessage").style.display = "none";
    document.getElementById("failMessage").style.display = "none";
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $scope.parsedUsers = [];
    $('#batchUsersModal').modal('show');
  };

  $scope.parseCSV = function(){

    if (!$('#files')[0].files.length)
    {
      alert("Please choose a CSV file to parse.");
      return;
    }

    var file = $('#files')[0].files[0];

    if(file.type != "text/csv" && file.type != ".csv" && file.type != "application/vnd.ms-excel"
    && (file.type == "" && !file.name.includes(".csv")))
    {
      alert("The file must of type CSV.");
      return;
    }
    Papa.parse(file, {
      complete: function(results) {
        $scope.$apply(function () {
          $scope.parsedUsers = results.data;
        });

        if(results.data.length == 0)
        {
          document.getElementById("parsedUsersPanel").style.display = "none";
          document.getElementById("successMessage").style.display = "none";
          document.getElementById("failMessage").style.display = "none";
          alert("No users could be parsed from CSV file");

          return;
        }

        var message = "Parsed (" + results.data.length + ") users from CSV file";
        document.getElementById("parseTitle").innerHTML = message;
        //alert(message);

      }
    });
    document.getElementById("parsedUsersPanel").style.display = "block";
    document.getElementById("successMessage").style.display = "none";
    document.getElementById("failMessage").style.display = "none";
  };

  $scope.addParsedUsers = function(){

    var users = $scope.parsedUsers;
    var success = 0;
    var fail = 0;
    var processed = 0;
    var total = users.length;

    users.forEach(function(user){
      addUsersBatch(user).then(
        function(data){
          if(data == true )
          {
            var index = $scope.parsedUsers.indexOf(user);
            $scope.parsedUsers.splice(index,1);
            success++;
          }
          else {
            fail++;
          }
          processed++;
          if(processed == total)
          {
            var successLabel = document.getElementById("successMessage");
            var failLabel = document.getElementById("failMessage");
            successLabel.style.display = "none";
            failLabel.style.display = "none";

            if(fail == 0){
              var x = document.getElementById("parsedUsersPanel");
              x.style.display = "none";
              successLabel.innerHTML = success + " user(s) successfully added";
              successLabel.style.display = "block";
            }
            else {
              if(success > 0)
              {
                successLabel.innerHTML = success + " user(s) successfully added";
                successLabel.style.display = "block";

              }
              failLabel.innerHTML = fail + " user(s) could not be added";
              failLabel.style.display = "block";
            }

          }
        });
    });
  };

  self.addUsersBatch = function(user){
    return new Promise(function(resolve, reject) {

      //get values from parsed user
      var first_name = user[0].trim();
      var last_name = user[1].trim();
      var username = user[2].trim();
      var password = user[3].trim();
      var shift = user[4].trim();
      var role = user[5].trim();

      if(password.length < 8){
        resolve(false);
        return;
      }

      if(!username || !first_name || !last_name){
        resolve(false);
        return;
      }

      if(!(role == "Administrator" || role == "Supervisor" || role == "Officer") ){
        resolve(false);
        return;
      }

      dataService.addUser(first_name, last_name, username, password, role, shift)
      .then(
        function(data){
          if(data['Added'] === true){
            sharedCtrl.getOfficers();
            resolve(true);
            console.log("true");
          }
          else{
            //  $scope.alert.closeAll();
            //$scope.alert.addAlert('danger', 'Could not add user!');
            resolve(false);
            console.log("fail");
            //return false;
          }
        },
        function(error){
          console.log('Error: ' + error);
        });

      });
  };

  /***** EDIT USER MODAL *****/
  $scope.editParsedUser = function(index, update_first, update_last, update_username, update_password, update_shiftid, update_role){
    $scope.index = index;
    $scope.updateFirst = update_first;
    $scope.updateLast = update_last;
    $scope.updateUsername = update_username;
    $scope.updatePassword = update_password;
	$scope.updateShiftId = update_shiftid;
    $scope.updateRole = update_role;
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $('#editParseModal').modal('show');
  };

  $scope.updateParseUser = function(){
    var index = $scope.index;
    var first_name = $scope.updateFirst;
    var last_name = $scope.updateLast;
    var username =$scope.updateUsername;
    var password =$scope.updatePassword;
    var shiftid = $scope.updateShiftId;
	var role = $scope.updateRole;

    $scope.parsedUsers[index][0] = first_name;
    $scope.parsedUsers[index][1] = last_name;
    $scope.parsedUsers[index][2] = username;
    $scope.parsedUsers[index][3] = password;
	$scope.parsedUsers[index][4] = shiftid;
    $scope.parsedUsers[index][5] = role;

    $('#editParseModal').modal('hide');
  };

  $scope.removeParsedUser = function(){
    var deleteUser = $window.confirm('Are you sure you want to delete this parsed user?');
    if(deleteUser){
      $scope.parsedUsers.splice($scope.index,1);
      $('#editParseModal').modal('hide');
    }
  };

  /***** ADD NEW CATEGORY *****/
  $scope.addCategory = function(new_cat){
    //get values from input fields
    var category_shifts = $scope.shiftModel;
    var shift_codes = [];

    //populate shift_codes with all shift codes for this category
    for (var i = 0 ; i<category_shifts.length ; i++){
      shift_codes[i] = category_shifts[i].id;
    }

    var category = new_cat;
    dataService.addCategory(category, shift_codes)
    .then(
      function(data){
        //check for successful add and notify user accordingly
        if(data['Added'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Category successfully added!');
          sharedCtrl.getCategories();
          //clear input fields
          $scope.new_category = ''; 
        }
        else{
          //the add was unsucessful
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not add category! The category name may already exist.');;
        }
      },
      function(error){
        console.log('Error: ' + error);
  });};

  /***** EDIT CATEGORY MODAL *****/
  $scope.editCategory = function(update_id, update_name){
    $scope.updateID = update_id;
    $scope.updateName = update_name;
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $('#editModal').modal();
  };

  /***** REMOVE CATEGORY *****/
  $scope.removeCategory = function(){
    //when delete button selected, prompt user for confirmation
    var deleteCategory = $window.confirm('Are you sure you want to delete this category?');
    //if confirmed...
    if(deleteCategory){
      var id = $scope.updateID;
      dataService.removeCategory(id)
      .then(
        function(data){
          if(data['Removed'] === true){
            $scope.alert.closeAll();
            $scope.alert.addAlert('success', 'Category successfully removed!');
            sharedCtrl.getCategories();
            window.location.reload();
            //$('#editModal').modal('hide');
          }else{
            $scope.alert.closeAll();
            $scope.alert.addAlert('danger', 'Could not remove category!');
            window.location.reload();
            //$('#editModal').modal('hide');
          }
        },
        function(error){
          console.log('Error: ' + error);
  });}};

  /***** UPDATE CATEGORY *****/
  $scope.updateCategory = function(){

    var id = $scope.updateID;
    var name = $scope.updateName;
    var category_shifts = $scope.shiftModel;
    var shift_codes = [];

    //populate shift_codes with all shift codes for this category
    for (var i = 0 ; i<category_shifts.length ; i++){
      shift_codes[i] = category_shifts[i].id;
    }

    dataService.updateCategory(id, name, shift_codes)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Category successfully updated!');
          $scope.getCategories();
          window.location.reload();
          //$('#editModal').modal('hide');
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update category! The category name may already exist.');
          window.location.reload();
          //$('#editModal').modal('hide');
        }
      },
      function(error){
        console.log('Error: ' + error);
      });
  };

  /***** UPDATE APP NAME *****/
  $scope.updateAppName = function(name){
    dataService.updateAppName(name)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Application name successfully updated!');
          $scope.application_name = '';
          sharedCtrl.getSiteNames();
          sharedCtrl.getTimeoutMinutes();
          sharedCtrl.getLatLong();
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update application name!');
        }
      },
      function(error){
        console.log('Error: ' + error);
      });
  };

  /***** UPDATE DEPT NAME *****/
  $scope.updateDeptName = function(name){
    dataService.updateDeptName(name)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Department name successfully updated!');
          $scope.department_name = '';
          sharedCtrl.getSiteNames();
          sharedCtrl.getTimeoutMinutes();
          sharedCtrl.getLatLong();

        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update department name!');
        }
      },
      function(error){
        console.log('Error: ' + error);
      });
    };

  /***** UPDATE SESSION TIMEOUT*****/
  $scope.updateTimeout = function(time){
    dataService.updateTimeout(time)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Session timeout successfully updated!');
          $scope.session_timeout = '';
          sharedCtrl.getSiteNames();
          sharedCtrl.getTimeoutMinutes();
          sharedCtrl.getLatLong();
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update session timeout!');
        }
      },
      function(error){
        console.log('Error: ' + error);
      });
    };



  /***** UPDATE LATITUDE AND LONGITUDE*****/
  $scope.updateLatLang = function(latitude, longitude){
    dataService.updateLatLang(latitude, longitude)
    .then(
      function(data){
        if(data['Updated'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Latitude/longitude successfully updated!');
          $scope.latitude = $scope.longitude = '';
          sharedCtrl.getSiteNames();
          sharedCtrl.getTimeoutMinutes();
          sharedCtrl.getLatLong();
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', 'Could not update latitude/longitude!');
        }
      },
      function(error){
        console.log('Error: ' + error);
      });
    };


  $scope.addShift = function(){ 
    //get data from inpt fields
    var shift_name = $scope.sName;
    var from_time = $scope.fTime;
    var to_time = $scope.tTime;
    var status = $scope.sStatus;

    dataService.addShift(shift_name, from_time, to_time, status)
    .then(
      function(data){
        if(data['Added'] === true){
          $scope.alert.closeAll();
          $scope.alert.addAlert('success', 'Shift added successfully!');
          //clear input fields
          $scope.sName = $scope.fTime = $scope.tTime = $scope.sStatus= '';
          //update edit shifts table to reflect the addition of new shifts 
          sharedCtrl.getShifts();
        }
        else{
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger','Unable to add shift!');
        }
      },
      function(error){
        console.log('Error: ' + error);
      }
    )
  }  
}]);
