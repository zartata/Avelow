//      Done, wihtout bugs
//
//      01/06/2015
//
//      Alexis

(function(){

    var app = angular.module('avelowMain', ['avelow', 'ngRoute', 'ngCookies', 'awFlash']);

    // Config
    app.config(['$routeProvider', function($routeProvider) {
        $routeProvider
        .when('/', {
            templateUrl: '..'
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

     // redirection à la page d'acceuil si l'on est pas connecté.
     /* app.run(['$location', 'awfLogin', function($location, awfLogin){
        // On redirige vers l'index si la personne est pas connectée
        if (!awfLogin.isConnected()){
            $location.path('/');
        }
    }]);
    */
})();
