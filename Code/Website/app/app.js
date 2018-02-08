var sharedModule = angular.module('shared', ['admin', 'supervisor', 'officer', 'login']);
var loginModule = angular.module('login', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap']);
var adminModule = angular.module('admin', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap']);
var supervisorModule = angular.module('supervisor', ['ngRoute', 'LocalStorageModule', 'shared', 'flow', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.exporter']);
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
