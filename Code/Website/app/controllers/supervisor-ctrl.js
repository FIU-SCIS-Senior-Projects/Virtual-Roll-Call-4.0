//CONTROLLER for supervisor app
supervisorModule.controller('supervisorCtrl', ['$scope', 'localStorageService', 'dataService', '$controller', '$location', function($scope, localStorageService, dataService, $controller, $location){

  $("[data-toggle=popover]").popover();
  $('[data-toggle="tooltip"]').tooltip();

  //TO DO: SAVE AS GLOBALS OR IN SHAREDCTRL
  /***** GLOBALS *****/
  //get name from local storage for user profile customization
  var fname = localStorageService.get('fname');
  var lname = localStorageService.get('lname');
  $scope.login = localStorageService.get('login');
  $scope.name = fname + ' ' + lname;
  $scope.id = localStorageService.get('id');
  $scope.quiz = 0;

  if ( !$scope.role )
    $scope.role = localStorageService.get('role');

  $scope.password_pattern = '^[a-zA-Z0-9]{8,}$';
  $scope.pattern_descr = 'Must contain at least 8 or more characters. Only alphanumeric characters allowed.';

  /***** SHARED FUNCTIONS *****/
  var sharedCtrl = $controller('sharedCtrl', {$scope: $scope});
  sharedCtrl.redirect($scope.login);

  $scope.getSiteNames = function(){ sharedCtrl.getSiteNames(); };
  $scope.getOfficers = function(){ sharedCtrl.getOfficers(); };
  $scope.logout = function(){ sharedCtrl.logout(); }
  $scope.getCategories = function() { sharedCtrl.getCategories(); }
  $scope.getDocuments = function(){ sharedCtrl.getDocuments(); }
  $scope.getMessages = function() { sharedCtrl.getMessages(); }
  $scope.getlogs = function(){ sharedCtrl.getlogs(); }
  $scope.refresh = function(){ setTimeout(function(){ window.location.reload(); }, 100); }

  $scope.editDocument = function(id ,name, pinned, category)
  {
    pinned = (pinned == 1 ) ? true : false;
    $scope.doc_deletedId = id;
    $scope.doc_name = name;
    $scope.cat_name = category;
    $scope.doc_old_cat = "Old Category: " + category;
    $scope.doc_cat_name = category;
    $scope.doc_pinned = pinned;
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $('#editModal').modal('show');
  };

  $scope.editMessage = function(mid, officer, title, msg, description)
  {
      $scope.updateTitle = title;
      $scope.updateFreeMsg = msg;
      $scope.updateMsgId = mid;
      $scope.updateOfficerId = officer;
      $scope.updateDescription = description;
      $scope.display_mode_modal = sharedCtrl.getDisplayMode();
      $('#editMessageModal').modal('show');
  };

  $scope.closeQuizMaker = function () { $('#quizModal').modal('hide'); };
  $scope.openQuizMaker = function ()
  {
      $scope.quiz++;
      if ( $scope.quiz % 2 == 1 )
          $('#quizModal').modal('show');
  };

  $scope.removeMessage = function()
  {
      var dialogResult = window.confirm("Are you sure you want to remove '" + $scope.updateTitle + "' message?");
      if ( dialogResult )
      {
        var id = $scope.updateMsgId;
        dataService.removeMessage(id);
        $scope.refresh();
      }
  };

  $scope.updateMessage = function()
  {
      var id = $scope.updateMsgId;
      var officerId = $scope.updateOfficerId;
      var title = $scope.updateTitle;
      var message = $scope.updateFreeMsg;
      var description = $scope.updateDescription;
      dataService.updateMessage(id, officerId, title, message, description);
      $scope.refresh();
  };

  $scope.updateDocument = function()
  {
    var id = $scope.doc_deletedId;
    var category =  $scope.doc_cat_name;
    var name = $scope.doc_name;
    var pinned = $scope.doc_pinned;
    sharedCtrl.updateDocument(id, category, name, pinned);
  };

  $scope.removeUploadedDocument = function()
  {
    var id = $scope.doc_deletedId;
    var dialogResult = window.confirm("Are you sure you want to delete '" + $scope.doc_name + "'?");
    if ( dialogResult )
    {
      dataService.removeUploadedDocument(id);
      $scope.refresh();
    }
  };
/***********************
* Toggle between day and night mode*
***********************/
 function getDisplayMode(){ return sharedCtrl.getDisplayMode(); };
 $scope.changeDisplayMode = function changeDisplayMode() {
   sharedCtrl.changeDisplayMode();
 };

  $scope.display_mode = getDisplayMode();
  $scope.night_mode = localStorageService.get('nightMode');

  /***** BATCH ADD WATCH ORDERS MODAL *****/
  $scope.addBatchWatchOrders = function(){
    document.getElementById("parsedWatchOrdersPanel").style.display = "none";
    document.getElementById("successMessage").style.display = "none";
    document.getElementById("failMessage").style.display = "none";
    $scope.display_mode_modal = sharedCtrl.getDisplayMode();
    $scope.parsedWatchOrders = [];
    $('#batchWatchOrdersModal').modal('show');
  };

  /***** PARSE CSV FILE*****/
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
          $scope.parsedWatchOrders = results.data;
        });

        if(results.data.length == 0)
        {
          document.getElementById("parsedUsersPanel").style.display = "none";
          document.getElementById("successMessage").style.display = "none";
          document.getElementById("failMessage").style.display = "none";
          alert("No watch orders could be parsed from CSV file");
          return;
        }

        var message = "Parsed (" + results.data.length + ") watch orders from CSV file";
        document.getElementById("parseTitle").innerHTML = message;

        var orders = $scope.parsedWatchOrders;

        //Geocode watch address
        orders.forEach(function(order){

          order.push(0,0);
          geoCodeAddress(order).then(
            function(data){
              order = data;
            },
            function(error){
              console.log('Error: ' + error);
            });
        });

        $scope.parsedWatchOrders = orders;
      }
    });
    document.getElementById("parsedWatchOrdersPanel").style.display = "block";
    document.getElementById("successMessage").style.display = "none";
    document.getElementById("failMessage").style.display = "none";
  };


  function geoCodeAddress(order)
  {
    return new Promise(function(resolve, reject) {


    dataService.geoCodeAddress(order[1])
    .then(
      function(data){
        if(data.status === "OK"){
          var expDate = order[2];
          order[1] = data.results[0].formatted_address;   // pick first formatted address returned
          order[2] = data.results[0].geometry.location.lat;  // add lat coordinate
          order[3] = data.results[0].geometry.location.lng;   // add long coordinate
          order[4] = expDate;   // add exp date

          resolve(order);

        }
        else{
          console.log(data);
          reject(error);
        }
      },
      function(error){
        console.log('Error: ' + error);
        reject(error);
      });
    });
  }



  $scope.addSingleWatchOrder = function(){

    //get values from input fields
    var order = [$scope.order.desc, $scope.order.address];

    var geocoder = new google.maps.Geocoder();
    //change search boundary for South Florida
    var southWest = new google.maps.LatLng({lat: 5.395327, lng:  -80.583131});
    var northEast = new google.maps.LatLng({lat: 26.316590, lng: -80.075519});
    var searchBoundary = new google.maps.LatLngBounds(northEast, southWest);

    //convert address into geo coordinates
    geocoder.geocode({address: order[1], bounds: searchBoundary }, function(results, status) {

      if (status == google.maps.GeocoderStatus.OK) {

        order[1] = results[0].formatted_address;   //pick first formatted address returned
        order.push(results[0].geometry.viewport.f.b, results[0].geometry.viewport.b.b);   // add lat and long coordinates
        order.push($scope.order.expDate);
        addWatchOrder(order).then(
          function(data){
            if(data == true )
            {
              $scope.alert.closeAll();
              $scope.alert.addAlert('success', 'Watch order successfully added!');

              //clear input fields
              $scope.order.desc = '';
              $scope.order.address = '';
              $scope.order.expDate = '';

              $scope.getWatchOrders();
            }
            else
            {
              $scope.$apply(function () {
                $scope.alert.closeAll();
                $scope.alert.addAlert('danger', 'Could not add watch order! The watch order address or date is invalid.');
              });

            }

          });
        }
        else {
          $scope.$apply(function () {
            $scope.alert.closeAll();
            $scope.alert.addAlert('danger', 'Could not add watch order! The watch order address or date is invalid.');
          });
        }
      });

    };

    /***** ATTEMPT TO ADD ALL PARSED ORDERS FROM CSV FILE TO DATABASE *****/
    $scope.addParsedWatchOrders = function(){

      var orders = $scope.parsedWatchOrders;
      var success = 0;
      var fail = 0;
      var processed = 0;
      var total = orders.length;

      //delete all watch orders from db before adding new orders
      dataService.removeWatchOrders()
      .then(
        function(data){
          if(data['RemovedAll'] === true){
            orders.forEach(function(order){
              addWatchOrder(order).then(
                function(data){
                  if(data == true )
                  {
                    $scope.$apply(function () {
                      var index = $scope.parsedWatchOrders.indexOf(order);
                      $scope.parsedWatchOrders.splice(index,1);
                    });
                    success++;
                  }
                  else
                  fail++;

                  processed++;
                  if(processed == total)
                  {
                    $scope.getWatchOrders();
                    var successLabel = document.getElementById("successMessage");
                    var failLabel = document.getElementById("failMessage");
                    successLabel.style.display = "none";
                    failLabel.style.display = "none";

                    if(fail == 0){
                      var x = document.getElementById("parsedWatchOrdersPanel");
                      x.style.display = "none";
                      successLabel.innerHTML = success + " orders(s) successfully added";
                      successLabel.style.display = "block";
                    }
                    else {
                      if(success > 0)
                      {
                        successLabel.innerHTML = success + " orders(s) successfully added";
                        successLabel.style.display = "block";

                      }
                      failLabel.innerHTML = fail + " orders(s) could not be added";
                      failLabel.style.display = "block";
                    }
                  }
                });
              });
            }
          },
          function(error){
            console.log('Error: ' + error);
          });
        };


        /***** EDIT PARSED WATCH ORDER MODAL *****/
        $scope.editParsedWatchOrder = function(index, desc, address, expDate){
          $scope.updateIndex = index;
          $scope.updateDesc = desc;
          $scope.updateAddress = address;
          $scope.updateExpDate = expDate;
          $scope.display_mode_modal = sharedCtrl.getDisplayMode();
          $('#editParseModal').modal('show');
        };

        $scope.updateParseUser = function(){
          var index = $scope.updateIndex;
          var desc = $scope.updateDesc;
          var address = $scope.updateAddress;
          var expDate = $scope.updateExpDate;

          var temp = $scope.parsedWatchOrders[index];
          temp[0] = desc;
          temp[1] = address;
          temp[2] = expDate;

          geoCodeAddress(temp).then(
            function(data){
              $scope.parsedWatchOrders[index] = data;
            },
            function(error){
              console.log('Error: ' + error);
            });

          $('#editParseModal').modal('hide');

        };

        $scope.removeParsedWatchOrder = function(){
          var deleteOrder = window.confirm('Are you sure you want to delete this parsed watch order?');
          if(deleteOrder){
            $scope.parsedWatchOrders.splice($scope.updateIndex,1);
            $('#editParseModal').modal('hide');
          }
        };

        /***** EDIT WATCH ORDER MODAL *****/
        $scope.editWatchOrder = function(id, desc, address, expDate){
          $scope.updateID = id;
          $scope.updateDesc = desc;
          $scope.updateAddress = address;
          $scope.updateExpDate = expDate;
          $scope.display_mode_modal = sharedCtrl.getDisplayMode();
          $('#editModal').modal('show');
        };

        /***** EDIT WATCH ORDER DATA *****/
        $scope.updateWatchOrder = function(){

          var id = $scope.updateID;
          var desc = $scope.updateDesc;
          var address = $scope.updateAddress;
          var expDate = $scope.updateExpDate;
          var order = [desc, address, expDate, 0, 0];

          geoCodeAddress(order).then(
            function(data){
              address = data[1];
              lat = data[2];
              lng = data[3];

              dataService.updateWatchOrder(id, desc, address, lat, lng, expDate)
              .then(
                function(data){
                  if(data['Updated'] === true){
                    $scope.alert.closeAll();
                    $scope.alert.addAlert('success', 'Watch Order successfully updated!');
                    $scope.getWatchOrders();
                    //window.location.reload();
                    $('#editModal').modal('hide');
                  }
                  else{
                    $scope.alert.closeAll();
                    $scope.alert.addAlert('danger', 'Could not update watch order!');
                  //  window.location.reload();
                    $('#editModal').modal('hide');
                  }

                },
                function(error){
                  console.log('Error: ' + error);
                });

            },
            function(error){
              console.log('Error: ' + error);
            });



          };

        /***** ATTEMPT TO ADD A PARSED WATCH ORDER TO THE DATABASE *****/
        self.addWatchOrder = function(order){

          return new Promise(function(resolve, reject) {

            //get values from parsed watch order
            var desc = order[0].trim();
            var address = order[1].trim();

            if(!address || address == "Invalid Address" ){
              resolve(false);
              return;
            }
            var lat = order[2];
            var long = order[3];
            if(lat == 0 || long == 0 ){
              resolve(false);
              return;
            }
            var expDate = order[4].trim();
            var splitDate = expDate.split("-");

            //check for valid year
            if(Number(splitDate[0]) < 2017 || splitDate[0].length != 4 )
            {
              resolve(false);
              return;
            }
            //check for valid month
            if(Number(splitDate[1]) > 12 || Number(splitDate[1]) < 1 )
            {
              resolve(false);
              return;
            }
            //todo: some months will different number of days
            if(Number(splitDate[2]) > 31 || Number(splitDate[2]) < 1 )
            {
              resolve(false);
              return;
            }


            dataService.addWatchOrder(desc, address, lat, long, expDate)
            .then(
              function(data){
                if(data['Added'] === true){
                  resolve(true);
                }
                else{
                  resolve(false);
                }
              },
              function(error){
                console.log('Error: ' + error);
              });

            });
          };

          /***********************
          * GET WATCH ORDERS
          ***********************/
          $scope.getWatchOrders = function getWatchOrders(){
            dataService.viewWatchOrders()
            .then(
              function (data) {

                //initialize an empty array to store results from the database
                var watch_orders = [];
                var exp_watch_orders = [];

                //for each category in the result
                for (var x in data) {

                  //create an object and set object properties
                  var tmp = new Object();
                  tmp.Id = data[x].Id;
                  tmp.Desc = data[x].Desc;
                  tmp.Address = data[x].Address;
                  tmp.Lat = data[x].Lat;
                  tmp.Lng = data[x].Lng;
                  tmp.AddDate = data[x].AddDate;
                  tmp.ExpDate = data[x].ExpDate;
                  if(validDate(tmp)){
                    watch_orders.push(tmp);
                  }
                  else {
                    exp_watch_orders.push(tmp);
                  }
                }

                //update for use in view
                $scope.watch_orders = watch_orders;

                //remove expired watch orders
                removeExpiredWatchOrders(exp_watch_orders);
              },
              function (error) {
                console.log('Error: ' + error);
              });
            };

            //compare current date with expiration date
            self.validDate = function(order){

              var today = new Date();
              var currDay = today.getDate();
              var currMonth = today.getMonth();
              var currYear = today.getFullYear();

              var expDate = order.ExpDate.split("-");
              var expDay = Number(expDate[2]);
              var expMonth = Number(expDate[1]);
              var expYear = Number(expDate[0]);

              var expiration = new Date();
              expiration.setFullYear(expYear, expMonth - 1, expDay);

              if(expiration < today)
              {
                return false;
              }
              return true;

            };

            //Remove expired watch orders
            self.removeExpiredWatchOrders =function(exp_watch_orders){

                  exp_watch_orders.forEach(function(order){
                    dataService.removeWatchOrder(order.Id);
                  });
            };

            /***** REMOVE WATCH ORDER *****/
            $scope.removeWatchOrder = function(){
              //when delete button selected, prompt user for confirmation
              var deleteOrder = window.confirm('Are you sure you want to delete this watch order?');
              if(deleteOrder){
                var id = $scope.updateID;
                dataService.removeWatchOrder(id)
                .then(
                  function(data){
                    if(data['Removed'] === true){
                      $scope.alert.closeAll();
                      $scope.alert.addAlert('success', 'Watch Order successfully deleted!');
                      $scope.getWatchOrders();
                      //window.location.reload();
                      $('#editModal').modal('hide');
                    }else{
                      $scope.alert.closeAll();
                      $scope.alert.addAlert('danger', 'Could not delete watch order!');
                      //window.location.reload();
                      $('#editModal').modal('hide');
                    }
                  },
                  function(error){
                    console.log('Error: ' + error);
                  });}};

      /***** ALERT FUNCTIONS *****/
      //alert functions (displays accordingly in views)
      $scope.alert = sharedCtrl.alert;

      /***** SUPERVISOR FUNCTIONS *****/

      /***** APPLY ACTIVE BS CLASS *****/
      $scope.isActive = function(path) {
        return $location.path() === path; //TO DO: Pull this function into shared ctrl
      };

      /***** GET SELECTED USER DATA *****/
      //TO DO: ENCAPSULATE THIS FUNCTIONALITY & UPDATE VIEW
      $scope.selected = {};
      $scope.select= function(index){ $scope.pick = index;};

      //TO DO: Clear input fields if another row is selected

      //helper for ng-class
      $scope.is_selected = function(index){ return ($scope.pick === index) ? true : false; }

      //resets selection when value in search bar is entered
      $scope.$watch('search', function() {
        $scope.selected = {};
        $scope.pick = null;
      });

      /***** RESET USER PASSWORD *****/
      $scope.resetPassword = function(reset_password, reset_password_conf){
        var id = $scope.selected.id;

        //new password and confirmation must match
        if(reset_password !== reset_password_conf){
          $scope.alert.closeAll();
          $scope.alert.addAlert('danger', "The new passwords don\'t match!");
          this.reset_pass = this.reset_pass_conf = '';
        }else{
          this.reset_pass = this.reset_pass_conf = '';
          //call reset password function in data service
          dataService.resetPassword(id, reset_password)
          .then(
            function(data){
              if(data['Updated'] === true){
                $scope.alert.closeAll();
                $scope.alert.addAlert('success', 'Officer password was successfully updated!');
              }
            },
            function(error){ console.log('Error: ' + error); });
          }};

          /***** SORT TABLE HEADERS *****/
          $scope.sortTable = function(order){ $scope.orderBy = order; };

          // Listen event to set grid data after it is ready, event is triggered on sharedCtrl.getlogs()
          $scope.$on('logs-data-ready', function(event, data) {
            $scope.gridOptions.data = data;
          });

          $scope.gridOptions = {
            enableFiltering: true,
            columnDefs: [
              { field: 'Full_Name', displayName: 'Officer Name'},
              { field: 'Document_Name'},
              { field: 'DOC', displayName: 'Log Date'},
              { field: 'Uploaded', displayName: 'Uploaded Date'},
              { field: 'Started', displayName: 'Started'},
              { field: 'Completed', displayName: 'Completed'},
              { field: 'Duration', displayName: 'Duration'},
              { field: 'Status', displayName: 'Status'}
            ],
            enableGridMenu: true,
            enableSelectAll: true,
            exporterCsvFilename: 'Logs.csv',
            exporterPdfDefaultStyle: {fontSize: 9},
            exporterPdfTableStyle: {margin: [30, 30, 30, 30]},
            exporterPdfTableHeaderStyle: {fontSize: 10, bold: true, italics: true, color: 'red'},
            exporterPdfHeader: { text: "Logs Report", style: 'headerStyle' },
            exporterPdfFooter: function ( currentPage, pageCount ) {
              return { text: currentPage.toString() + ' of ' + pageCount.toString(), style: 'footerStyle' };
            },
            exporterPdfCustomFormatter: function ( docDefinition ) {
              docDefinition.styles.headerStyle = { fontSize: 22, bold: true };
              docDefinition.styles.footerStyle = { fontSize: 10, bold: true };
              return docDefinition;
            },
            //rowTemplate: '<div ng-style="{\'cursor\': row.cursor, \'z-index\': col.zIndex() }" class="ngCell {{col.cellClass}}" ng-cell></div>',

            exporterPdfOrientation: 'landscape',
            exporterPdfPageSize: 'LETTER',
            exporterPdfMaxGridWidth: 500,
            exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
            onRegisterApi: function(gridApi){ $scope.gridApi = gridApi; }
          };
        }]);
