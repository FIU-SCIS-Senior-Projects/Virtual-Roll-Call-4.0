var sharedModule = angular.module('shared', ['admin', 'supervisor', 'officer', 'login', 'ngIdle', 'ui.bootstrap']);
var loginModule = angular.module('login', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap','ngIdle']);
var adminModule = angular.module('admin', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap']);
var supervisorModule = angular.module('supervisor', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.exporter', 'angularjs-dropdown-multiselect']);
var officerModule = angular.module('officer', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap']);

adminModule.config(function($routeProvider){
	$routeProvider
	.when('/categories', {
		templateUrl: 'partials/category-management.html',
		controller: 'adminCtrl'
	})
	.when('/settings', {
		templateUrl: 'partials/site-settings.html',
		controller: 'adminCtrl'
	})
	.when('/user-management', {
		templateUrl: 'partials/user-management.html',
		controller: 'adminCtrl'
	})
	.when('/password', {
		templateUrl: 'partials/change-password.html',
		controller: 'sharedCtrl'
	})
	.when('/reporting', {
		templateUrl: 'partials/reporting.html',
		controller: 'adminCtrl'
	})
	.when('/archive',{
		templateUrl: 'partials/archive.html',
		controller: 'adminCtrl'
	})
	.when('/temp',{
		templateUrl: 'partials/user-Temp.html',
		controller: 'adminCtrl'
	})
	.when('/shift-management',{
		templateUrl: 'partials/shift-management.html',
		controller: 'adminCtrl'
	})
	.otherwise({
		redirectTo: '/user-management'
	});
});

supervisorModule.config(function($routeProvider){
	$routeProvider
	.when('/upload', {
		templateUrl: 'partials/manage-documents.html',
		controller: 'supervisorCtrl'
	})
	.when('/manage-watch-orders', {
		templateUrl: 'partials/manage-watch-orders.html',
		controller: 'supervisorCtrl'
	})
	.when('/archived-watch-orders', {
	templateUrl: 'partials/archived-watch-orders.html',
	controller: 'supervisorCtrl'
	})
	.when('/manage-freetext', {
		templateUrl: 'partials/manage-freetext.php'
	})
	.when('/reset', {
		templateUrl: 'partials/reset-password.html',
		controller: 'supervisorCtrl'
	})
	.when('/password', {
		templateUrl: 'partials/change-password.html',
		controller: 'supervisorCtrl'
	})
	.when('/log',{
         templateUrl: 'partials/show-logs.html'
        })
	.otherwise({
		redirectTo: '/upload'
	});
});

officerModule.config(function($routeProvider){
	$routeProvider
	.when('/categories', {
		templateUrl: 'partials/view-categories.html',//,
		controller: 'officerCtrl'
    })
	.when('/documents/:selectedCategory', {
		templateUrl: 'partials/view-documents.html'
	})
	.when('/documents/:selectedCategory/archived', {
		templateUrl: 'partials/view-documents-archived.html'
	})
	.when('/watch-orders', {
		templateUrl: 'partials/watch-orders.html'
	})
	.otherwise({
		redirectTo: '/categories'
	});
});


sharedModule.config(['KeepaliveProvider', 'IdleProvider', function(KeepaliveProvider, IdleProvider) {
  //default timeout is 1 hour (3600 seconds). This value is updated to the minutes entered in Site Settings
  //from function timeoutInit() in shared controller which is called from all views
  IdleProvider.idle(3600);

  //defualt countdonw is 1 minute before login user out. 
  IdleProvider.timeout(60);
  KeepaliveProvider.interval(10);
}]);

sharedModule.run(['Idle', function(Idle) {
  Idle.watch();
}]);

