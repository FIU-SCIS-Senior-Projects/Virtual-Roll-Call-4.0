const MAP_API_KEY = 'AIzaSyAuubk4Obni7qiK7Umj7CdvUUxO23688cM';

//SERVICE for shared controller
sharedModule.factory('sharedService', function ($http, $q) {
  return {
    getSiteNames: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-site-names.php', {})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    getPendingCount: function (id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-pendingdocs.php', { 'user_id': id })
          .then(
          function (response) { resolve(response.data); },
          function (error) {
            reject(error);
          });
      });
    },
    getCategories: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-categories.php', {})
          .then(
          function (response) { resolve(response.data); },
          function (error) { reject(error); });
      });
    },

    getAuthorizedCategories: function (id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-authorized-categories.php', {'user_id': id})
          .then(
          function (response) { resolve(response.data); },
          function (error) { reject(error); });
      });
    },

    getDocuments: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-documents.php', {'type': 'all', 'user_id': 0, 'cat' : ''})
          .then(
          function (response) { resolve(response.data); },
          function (error) { reject(error); });
      });
    },
    getMessages: function( id ) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-messages.php', { 'id': id })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    getOfficers: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-officers.php', {})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },

    getShifts: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-shifts.php', {})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },

    getlogs: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-logs.php', {})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
   updateDocument: function(id,categorie,name,pinned){
     return $q(function (resolve, reject){
         	$http.post('../app/php/update-document.php',{'id':id,'categories':categorie,'name':name,'pinned':pinned})
      	 .then(
      		function (response){ resolve(response.data);},
      		function (error) {reject(error);})
      	});
    },

   changePassword: function(id, curr_pass, new_pass) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/change-password.php', { 'id': id, 'current': curr_pass, 'new': new_pass })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    }
  }
});

//SERVICE for login controller
loginModule.factory('dataService', function ($http, $q) {
  return {
    login: function (username, password) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/login.php', { 'username': username, 'password': password })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    getTally: function( username ){
        return $q(function (resolve) {
          $http.post('../app/php/get-tally.php', {'username': username})
              .then( function(response) { resolve(response.data); });
        });
    },
    updateFailedLog: function( found, id, count ) {
      return $q(function (resolve, reject)
      {
        $http.post('../app/php/updateFailedLog.php', { 'lock_found' : found, 'lock_id': id, 'lock_count': count})
          .then(
                  function (response) { resolve(response.data); },
                  function (error) { reject(error); }
            );
      });
    },
    lockUser: function (userid){
        return $q(function (resolve) {
          $http.post('../app/php/lockUser.php', {'userid': userid})
            .then( function(response) { resolve(response.data); });
        });
    },
    resetLock: function(userid){
        return $q(function(resolve) {
          $http.post('../app/php/resetLock.php', {'userid': userid})
            .then( function(response) {
              resolve(response.data); });
        });
    }
  }
});


//SERVICE for admin controller
adminModule.factory('dataService', function ($http, $q) {
  return {

    addUser: function (fname, lname, email, password, role, shift) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/add-user.php', { 'fName': fname, 'lName': lname, 'email': email, 'password': password, 'role': role , 'shift' : shift})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    getUser: function (username) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-user.php', { 'username': username })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    updateUser: function (id, fname, lname, username, role, shift) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/edit-user.php', { 'id': id, 'fName': fname, 'lName': lname, 'username': username, 'role': role, 'shift' : shift})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    removeUser: function (id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/remove-user.php', { 'id': id })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },



    addShift: function (shift_name, from_time, to_time, status) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/add-shift.php', { 'sName': shift_name, 'fTime': from_time, 'tTime': to_time, 'sStatus': status})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },

    updateShift: function (id, shift_name, from_time, to_time, status) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/edit-shift.php', { 'id': id, 'sName': shift_name, 'fTime': from_time, 'tTime': to_time, 'sStatus': status })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },

    removeShift: function (id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/remove-shift.php', { 'id': id })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },

    addCategory: function (new_cat, shiftCodes) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/add-category.php', { 'category': new_cat , 'shifts' : shiftCodes})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    removeCategory: function (id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/remove-category.php', { 'category_id': id })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    updateCategory: function (cid, cname, shifts) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/update-category.php', { 'id': cid, 'name': cname, 'shifts': shifts})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    updateAppName: function (name) {
      return $q(function (resolve, reject) {

        $http.post('../app/php/update-app-name.php', { 'name': name })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    deleteArchive: function(from,to) {
      return $q(function (resolve, reject) {

        $http.post('../app/php/delete-archive.php', { 'from': from,'to':to })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    updateDeptName: function (name) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/update-dept-name.php', { 'name': name })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    }
  }
});


//SERVICE for supervisor controller
supervisorModule.factory('dataService', function ($http, $q) {
  return {
    addWatchOrder: function (desc, address, lat, long, expDate) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/add-watch-order.php', { 'desc': desc, 'address': address, 'lat': lat, 'long': long, 'expDate': expDate })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    viewWatchOrders: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-watch-orders.php')
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    removeWatchOrders: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/remove-watch-orders.php')
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    removeUploadedDocument: function (document_id) {
      return $q(function (resolve, reject) {
          $http.post('../app/php/remove-document.php', { 'id': document_id })
            .then (
              function(response) { alert(JSON.stringify(resonse.data)); resolve(response.data); },
              function(error) { reject(error); }
            );
      });
    },
    updateWatchOrder: function (id, desc, address, lat, lng, expDate) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/edit-watch-order.php', { 'id': id, 'desc': desc, 'address': address, 'lat': lat, 'lng': lng, 'expDate': expDate})
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    removeWatchOrder: function (id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/remove-watch-order.php', { 'id': id })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    geoCodeAddress: function (address) {
      return $q(function (resolve, reject) {

        var params = {
          address: address,
        //  components: "administrative_area:Florida",  //only look for addresses in Florida
          key: MAP_API_KEY
        };

        //encode query URL
        var esc = encodeURIComponent;
        var query = Object.keys(params)
        .map(k => esc(k) + '=' + esc(params[k]))
        .join('&');
        $http.get('https://maps.googleapis.com/maps/api/geocode/json?' + query)
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    },
    updateMessage: function (id, officerId, title, message, description) {
      return $q(function (resolve, reject) {
          $http.post('../app/php/update-message.php', { 'id': id, 'officerId': officerId, 'title': title, 'message': message, 'description': description })
            .then (
              function(respoonse) { resolve(response.data); },
              function(error) { reject(error); }
            );
      });
    },
    removeMessage: function (message_id) {
      return $q(function (resolve, reject) {
          $http.post('../app/php/delete-message.php', { 'id': message_id })
            .then (
              function(respoonse) { resolve(response.data); },
              function(error) { reject(error); }
            );
      });
    },
    resetPassword: function (id, reset_pass) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/reset-password.php', { 'id': id, 'reset_pass': reset_pass })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    }
  }
});

//SERVICE for officer controller
officerModule.factory('dataService', function ($http, $q) {
return {
    viewDocuments: function (user_id, doc_category, $type) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-documents.php', {'type': $type, 'user_id': user_id, 'cat': doc_category })
          .then(
                function (response) { resolve(response.data); },
                function (error) { reject(error); });
          });
    },
    downloadDocument: function (upload_name) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/view-document.php', { 'upload_name': upload_name })
          .then(
          function (response) {
            resolve(response.statusText);
          },
          function (error) {
            reject(error);
          });
      });
    },
    documentSaveLog: function (user_id, document_id) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/documentSaveLog.php', { 'user_id': user_id, 'document_id': document_id })
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            console.log(error);
            reject(error);
          });
      });
    },
    documentStatusUpdate: function (user_id, document_id, category_id, new_status) {
      return $q(function (resolve, reject) {
        $http.post('../app/php/documentStatusUpdate.php', { 'user_id': user_id, 'document_id': document_id, 'category_id': category_id, 'new_status': new_status })
          .then(
          function (response) {
            console.log(response.data);
            resolve(response.data);
          },
          function (error) {
            console.log(error);
            reject(error);
          });
      });
    },
    viewWatchOrders: function () {
      return $q(function (resolve, reject) {
        $http.post('../app/php/get-watch-orders.php')
          .then(
          function (response) {
            resolve(response.data);
          },
          function (error) {
            reject(error);
          });
      });
    }
  }
});
