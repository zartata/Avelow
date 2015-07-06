//      In progress
//
//      04/06/2015
//
//      Alexis

(function(){

    var app = angular.module('awAdmin', ['awFlash', 'avelow', 'ngRoute', 'ngCookies']);

    // Config
    app.config(['$routeProvider', function($routeProvider) {
        $routeProvider
        .when('/', {
            templateUrl: '/app/shared/aw-admin/aw-admin-login.html',
            controller: 'awcLogin'
        })
        .when('/board', {
            templateUrl: '/app/shared/aw-admin/aw-admin-board.html',
            controller: 'awcBoard'
        })
        .when('/add-doc', {
            templateUrl: '/app/components/doc/addDoc.html',
            controller: 'docCtrl'
        })
        .otherwise({
            redirectTo: '/'
        });
    }]);


    app.config(['$locationProvider', function($locationProvider) {
            // Penser au <base href="/">
            $locationProvider.html5Mode(true);
     }]);

     app.config(['$cookiesProvider', function($cookiesProvider){
         var d = new Date();
         d.setFullYear(d.getFullYear() + 1);
         $cookiesProvider.defaults.expires = d;
     }]);


    app.run(['$location', 'awfLogin', function($location, awfLogin){

        // On redirige vers l'index si la personne est pas connectée
        if (!awfLogin.isConnected()){
            $location.path('/');
        }
    }]);

    app.controller('awcLogin', ['$scope', 'awfLogin', '$cookies', 'awfFlash', function($scope, awLogin, $cookies, awfFlash){

        $scope.password = '';
        $scope.showHelp = true;

        $scope.login = function(){
            awLogin.login('admin', $scope.password, '/board')
        };

        $scope.dontShow = function(){
            $cookies.put('dontShow', true);
            $scope.showHelp = false;
        }

        this.init = function(){
            var cookie = $cookies.get('dontShow');

            if (cookie){
                $scope.showHelp = false;
            }else{
                awfFlash.add('help', 'Vous devez configurer votre mot de passe en utilisant la commande console : php generatePassword.php');
            }
        };

        this.init();

    }]);

    app.controller('awcBoard', ['awfEntity', '$scope', '$route', 'awfLogin', function(awfEntity, $scope, $route, awfLogin){

        $scope.tables = [];
        $scope.entities = [];
        $scope.infos = {};
        $scope.awfLogin = awfLogin;

        $scope.awTable = {};

        $scope.selected = {table: {}, entity: {}}

        this.init = function(){

            awfEntity.getListEntity('AwTable')
                .then(function(data){
                    $scope.tables = data;

                    $scope.awTable = _.findWhere(data, {name: 'awtable'});
                });

        };

        $scope.isSelectedTable = function(){
            return !_.isEmpty($scope.selected.table);
        };

        $scope.isSelectedEntity = function(){
            return !_.isEmpty($scope.selected.entity);
        };

        $scope.existEntities = function(){
            return !_.isEmpty($scope.entities);
        };

        $scope.updateOnTable = function(table){

            $scope.entities = [];
            awfEntity.getListEntity(table.entity)
                .then(function(data){
                    $scope.entities = data;
                });

            $scope.infos = table;
            $scope.entity = {};
            $scope.selected.table = table;
            $scope.selected.entity = {};

        };

        $scope.updateOnEntity = function(entity){

            $scope.selected.entity = entity;

        };

        this.init();

    }]);



})();
