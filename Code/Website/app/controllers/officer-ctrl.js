   //CONTROLLER for officer app
officerModule.controller('officerCtrl', ['$scope', 'localStorageService', 'dataService', '$controller', '$routeParams',
  function ($scope, localStorageService, dataService, $controller, $routeParams) {

    //TO DO: SAVE AS GLOBALS OR IN SHAREDCTRL
    /***** GLOBALS *****/
    //get name from local storage for user profile customization

    var fname = localStorageService.get('fname');
    var lname = localStorageService.get('lname');
    var id = localStorageService.get('id');
    $scope.login = localStorageService.get('login');
    $scope.name = fname + ' ' + lname;
    $scope.id = id;
    var role = localStorageService.get('role');

    $scope.admin = 0;
    $scope.super = 0;
    if ( role === 'Administrator' ) $scope.admin = 1;
    if ( role === 'Supervisor' )    $scope.super = 1;

    $scope.password_pattern = '^[a-zA-Z0-9]{8,}$';
    $scope.pattern_descr = 'Must contain at least 8 or more characters. Only alphanumeric characters allowed.';
    /***** SHARED FUNCTIONS *****/
    var sharedCtrl = $controller('sharedCtrl', { $scope: $scope });
    sharedCtrl.redirect($scope.login);
    sharedCtrl.getPendingCount( id );

    $scope.display_mode = getDisplayMode();
    $scope.night_mode = localStorageService.get('nightMode');

    $scope.getSiteNames = function () { sharedCtrl.getSiteNames(); };
    $scope.logout = function () { sharedCtrl.logout(); }
    $scope.getAuthorizedCategories = function(){sharedCtrl.getAuthorizedCategories(id);};
    $scope.getLatLong = function(){sharedCtrl.getLatLong();};
    $scope.timeoutInit = function(){sharedCtrl.timeoutInit();};
    $scope.getCategories = function () 
    {  
        sharedCtrl.getCategories(); 
        $scope.pinned_documents = null;
        $scope.unpinned_documents = null;
    };
    $scope.refresh = function(){ setTimeout(function(){ window.location.reload(); }, 100); }
    $scope.takeQuiz = function ( doc, qa, officerId, documentId, categoryId, doc_status, answers, score )
    {
        if ( doc_status === 'Pending' )
          alert('Quiz available Only after revewing document!');
        else
        {
            var IDs = [officerId, documentId, categoryId, doc_status];
            var questions = JSON.parse ( qa );
            var officer_answers = [];
            var correct_answers = [];
            $scope.quiz_submitted = false;

            if ( doc_status !== 'Done' )
            {
                var order = [0,1,2,3];
                for ( var x = 0; x < questions.length; x++ )
                {
                    var answer = [ questions[x]["answer_1"], questions[x]["answer_2"],
                                   questions[x]["answer_3"], questions[x]["answer_4"] ];
                    order = shuffle(order);
                    correct_answers.push(questions[x]["answer_1"]);
                    questions[x]["answer_1"] = answer[order[0]];
                    questions[x]["answer_2"] = answer[order[1]];
                    questions[x]["answer_3"] = answer[order[2]];
                    questions[x]["answer_4"] = answer[order[3]];
                }
            }
            else
            {
                $scope.quiz_submitted = true;
                $scope.score = score;
                officer_answers = JSON.parse ( answers );
                for ( var x = 0; x < questions.length; x++ )
                    questions[x]["officer_answer"] = officer_answers[x];
            }
            $scope.questions = questions;
            $scope.answers = JSON.stringify(correct_answers);
            $scope.officer_answers = officer_answers;
            $scope.q_doc = doc;
            $scope.ids = IDs;

            $scope.display_mode_modal = sharedCtrl.getDisplayMode();
            $('#quizModal').modal('show');
        }
    };

    function shuffle( qa )
    {
        var m = qa.length, t, i;
        while(m)
        {
            i = Math.floor(Math.random() * m--);
            t = qa[m]; qa[m] = qa[i]; qa[i] = t;
        };
        return qa;
    };

    /***********************
   * GET ACTIVE DOCUMENTS *
   ***********************/
    $scope.getActiveDocuments = function () {
      $scope.selected_cat = $routeParams.selectedCategory;
      dataService.viewDocuments( id, $scope.selected_cat, 'active' )
        .then(
        function (data) { setDocuments( data ); },
        function (error) { console.log('Error: ' + error); });
    };

    $scope.getArchivedDocuments = function () {
      $scope.selected_cat = $routeParams.selectedCategory;
      dataService.viewDocuments( id, $scope.selected_cat, 'archived' )
        .then(
        function (data) { setDocuments( data ); },
        function (error) { console.log('Error: ' + error); });
    };

    function setDocuments( data ) {
      var pinned_documents = [];
          var unpinned_documents = [];
          if ( data )
            for (var x in data)
            {
                var tmp = new Object();
                tmp.id = data[x].id;
                tmp.name = data[x].name;
                tmp.catid = data[x].catid;

                tmp.upload_name = data[x].upload_name;
                tmp.doc_description = data[x].doc_description;
                tmp.status = data[x].status;
                tmp.isDone = data[x].status == "Done" ? true : false;
                tmp.doneDisable = data[x].status == "Pending" || data[x].status == "Done" ? true : false;
                tmp.quiz = data[x].quiz;
                tmp.questions = data[x].qa;
                tmp.answers = data[x].answers;
                tmp.score = data[x].score;
                tmp.quizz_ready = "No";
                if (data[x].pinned == 1) pinned_documents.push(tmp);
                else unpinned_documents.push(tmp);
            }
          $scope.pinned_documents = pinned_documents;
          $scope.unpinned_documents = unpinned_documents;

          sortDocuments();
    }

    function sortDocuments(){
      $scope.pinned_documents.sort(function(a, b) {
        if (a.status =="Pending" && (b.status =="Reviewed" || b.status =="Done"))
          return -1;

        if (a.status =="Reviewed")
        {
          if (b.status =="Done") return -1;
          if (b.status =="Pending") return 1;
        }

        if(a.status =="Done" && (b.status =="Pending" || b.status =="Reviewed"))
          return 1;
        return 0;
      });
    };

    /***********************
   * Toggle between day and night mode*
   ***********************/
    $scope.changeDisplayMode = function changeDisplayMode() { sharedCtrl.changeDisplayMode(); };
    function getDisplayMode(){ return sharedCtrl.getDisplayMode(); };


    /***********************
     * GET WATCH ORDERS
     To do: move to shared ctrl
     ***********************/
      /* Select Row */
      $scope.select = function(item) {
        item.selected ? item.selected = false : item.selected = true;
        $scope.updateWatchOrderTracking(item.Id, id, item.selected);
      };
      $scope.UnselectAllLocations = function() {
          angular.forEach($scope.watch_orders, function(o) {
            o.selected = false;
            $scope.updateWatchOrderTracking(o.Id, id, o.selected);
          })
      };

      function getWatchOrders(){

        return new Promise(function(resolve, reject) {

          dataService.viewWatchOrders(id)
            .then(
            function (data) {

              //initialize an empty array to store results from the database
              var watch_orders = [];

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
                  tmp.StartDate = data[x].StartDate;
                  tmp.StartTime = data[x].StartTime;
                  tmp.ExpTime = data[x].ExpTime;
                  tmp.Zone = data[x].Zone;
                  tmp.BusinessName = data[x].BusinessName;
                  tmp.OwnerName = data[x].OwnerName;
                  tmp.WORequester = data[x].WORequester;
                  tmp.Phone = data[x].Phone;
                  tmp.WOInstruction = data[x].WOInstruction;
                  tmp.EName = data[x].EName;
                  tmp.EAddress = data[x].EAddress;
                  tmp.EPhone = data[x].EPhone;
                  tmp.CreatedBy = data[x].CreatedBy;
                  tmp.selected = data[x].is_selected;

                  if(validDate(tmp)){
                    watch_orders.push(tmp);
                  }
              }

              resolve(watch_orders);

            },
            function (error) {
              console.log('Error: ' + error);
              reject(error);
            });
        });
      }


    //compare current date with expiration date
    function validDate(order){
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

      //Initialize map
      $scope.initMap = function initMap() {


        $scope.markerCount = 0;

        //updated in version 4.0 to get latitude and longitude specified in Site Settings.
        // var defaultLocation = {lat: 25.6622835, lng: -80.307}; //default location set in Pinecrest,FL


        var defaultLat = localStorageService.get('lat');
        var defaultLong = localStorageService.get('lon');
        var defaultLocation = {lat: 0, lng: 0};
        defaultLocation.lat = defaultLat;
        defaultLocation.lng = defaultLong;

        // alert(defaultLocation.lat);
        // alert(defaultLocation.lng);

        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 13,
          center: defaultLocation
        });

        getWatchOrders().then(
          function (data) {

            //update for use in view
            $scope.watch_orders = data;

            var markerCount = 0;
            data.forEach(function(order){

              var contentString = "<h5><b>" + order.Address + "</b> </h5><hr>";
              contentString += "<p><b>Description:</b> " + order.Desc + "</p>";
              contentString += "<p><b>Created On:</b> " + order.AddDate + "</p>";
              contentString += "<p><b>Start Date:</b> " + order.StartDate + "</p>";
              contentString += "<p><b>Expiration:</b> " + order.ExpDate + "</p>";
              contentString += "<p><b>Start Time:</b> " + order.StartTime + "</p>";
              contentString += "<p><b>Expiration Time:</b> " + order.ExpTime + "</p>";

              var infowindow = new google.maps.InfoWindow({
                content: contentString
              });

              var marker = new google.maps.Marker({
                position: {lat: order.Lat, lng: order.Lng},
                label: String(++markerCount),
                map: map
              });

              marker.addListener('click', function() {
                infowindow.open(map, marker);
              });

              /* **** CODE TO VIEW DETAIL WITHIN MAP... NOT WORKING PROPERLY *****
              marker.addListener('dblclick', function() {
                if (order.Address !== null) {
                  $scope.viewWatchOrder(order.Id, order.Desc, order.Address, order.ExpDate, order.StartDate, order.StartTime, order.ExpTime,
                                        order.Zone, order.BusinessName, order.OwnerName, order.WORequester, order.Phone, order.WOInstruction,
                                        order.EName, order.EAddress, order.EPhone);};});*/
            }
          );

          $scope.$apply(function () {
            $scope.markerCount = markerCount;
          });
          }
        );
      };

      function geocodeAddress(geocoder, resultsMap) {
        var address = document.getElementById('address').value;
        geocoder.geocode({'address': address}, function(results, status) {
          if (status === 'OK') {
            resultsMap.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
              map: resultsMap,
              position: results[0].geometry.location
            });
          } else {
            alert('Geocode was not successful for the following reason: ' + status);
          }
        });
      }

      /***** VIEW WATCH ORDER MODAL *****/
      $scope.viewWatchOrder = function(id, desc, address, expDate, startDate, startTime, expTime, zone, businessName, ownerName, woRequester, phone, woInstruction, eName, eAddress, ePhone, createdBy){

        $scope.updateID = id;
        $scope.updateDesc = desc;
        $scope.updateAddress = address;
        $scope.updateExpDate = expDate;
        $scope.updateStartDate = startDate;
        $scope.updateStartTime = startTime;
        $scope.updateExpTime = expTime;          
        $scope.updateZone = zone;
        $scope.updateBusinessName = businessName;
        $scope.updateOwnerName = ownerName;
        $scope.updateWORequester = woRequester;
        $scope.updatePhone = phone;
        $scope.updateWOInstruction = woInstruction;
        $scope.updateEName = eName;
        $scope.updateEAddress = eAddress;
        $scope.updateEPhone = ePhone;
        $scope.updateCreatedBy = createdBy;

        $scope.display_mode_modal = sharedCtrl.getDisplayMode();
        $('#editModal').modal('show');
      };



    $scope.document_log = function (user_id, document_id, category_id, list_name, status)
    {
      if (status == 'Pending')
        $scope.documentStatusUpdate(user_id, document_id, category_id, list_name, status);
    }

    $scope.documentStatusUpdate = function (user_id, document_id, category_id, list_name, status)
    {
        dataService.documentStatusUpdate(user_id, document_id, category_id, status)
          .then(
            function (data) {
              console.log('Data: Id:' + data.id + ' Status:' + data.status);
              var docs = (list_name == 'pinned') ? $scope.pinned_documents : $scope.unpinned_documents;
              for (var i in docs)
              {
                  if (docs[i].id == data.id)
                  {
                    docs[i].status = data.status;
                    docs[i].doneDisable = data.status == "Pending" || data.status == "Done" ? true : false;
                    docs[i].quizz_ready = "Take Quizz";
                    break; //Stop this loop, we found it!
                  }
              }
              sortDocuments();
              sharedCtrl.getPendingCount( id );
          },
          function (error) { console.log('Error: ' + error);
          });
    };

    $scope.updateWatchOrderTracking = function (wo_id, user_id, is_selected)
    {
        dataService.updateWatchOrderTracking(wo_id, user_id, is_selected)
          .then(
            function (data) {
              console.log('Update Successful');             
          },
          function (error) { console.log('Error: ' + error);
          });
    };

    //alert functions (displays accordingly in views)
    $scope.alert = sharedCtrl.alert;
  }]);
