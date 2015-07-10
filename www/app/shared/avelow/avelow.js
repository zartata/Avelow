(function(){

    var app = angular.module('avelow', ['awFlash', 'ngCookies']);

    app.factory('awfUrl', ['awfLogin', function(LoginFactory){

        var UrlF = {

            getUrl: function(calledUrl, params){

                var param = "";

                if (!_.isUndefined(params) && !_.isEmpty(params)){
                    // Si il y a des paramètres
                    for (var key in params){
                        if (params.hasOwnProperty(key)){
                            param = param+"&"+key+"="+params[key];
                        }
                    }
                }

                var tmp = Math.floor(Date.now() / 1000);
                var urlToReturn = calledUrl+'?tmp='+tmp;

                if (LoginFactory.isConnected()){
                    // On récupère l'url en fonction de l'user
                    urlToReturn = urlToReturn+'&user='+LoginFactory.currentUser.pseudo;
                    var hash = CryptoJS.enc.Hex.stringify(CryptoJS.HmacSHA256(LoginFactory.currentUser.pseudo, LoginFactory.currentUser.password));
                    var sign = CryptoJS.enc.Hex.stringify(CryptoJS.HmacSHA256(urlToReturn, hash));
                    urlToReturn = urlToReturn + '&signature='+sign;
                }

                return '/api'+urlToReturn + param;
            },
        };

        return UrlF;
    }]);

    app.factory('awfRess', ['$q', '$http', 'awfFlash', function($q, $http, awFlash){

        var ressourcesFactory = {

            awRess: function(request){
                var deferred = $q.defer();

                $http(request)
                .success(function(data, status){
                    if (data.status == 200 || data.status == 201 || data.status == 204){
                        // On revoit les données si la requete a fonctionnee.
                        deferred.resolve(data.data);
                    }
                    else{
                        // Gestion des messages flash
                        awFlash.add('error', data.error.message);
                        deferred.reject(data.error);
                    }
                }).error(function(){
                    var msg = 'Problème lors de l\'execution de la requete.';
                    awFlash.add('error', msg);
                    deferred.reject(msg);
                });

                //On renvoie la promesse
                return deferred.promise;
            }

        };

        return ressourcesFactory;

    }]);

    app.factory('awfLogin', ['awfRess', 'awfFlash', 'awfFlash', '$cookies', '$location', function(awRess, awUrl, awFlash, $cookies, $location){

        var logF = {

            currentUser: {},

            getUser: function(){
                return logF.currentUser;
            },

            // Return true if connected, false if not
            login: function(pseudo, pw, urlToRedirect, remember){
                if (logF.isConnected()){
                    awFlash.add('error', 'Un utilisateur est déjà connecté.');
                    return false;
                }else{

                    var req = {
                        method: 'get',
                        url: logF.getConnectionUrl(pseudo, pw)
                    };

                    awRess.awRess(req).then(
                        function(data){
                            logF.currentUser = data;

                            if (!_.isUndefined(remember) && remember === true){
                                $cookies.putObject('user_infos', {pseudo: pseudo, password: pw});
                            }

                            if (!_.isUndefined(urlToRedirect)){
                                $location.path(urlToRedirect);
                            }

                        });
                }
            },

            loginWithCookie: function(){
                if (!logF.isConnected()){

                    var userInfos = $cookies.get('user_infos');

                    if (!_.isUndefined(userInfos.pseudo) && !_.isUndefined(userInfos.password)){
                        logF.login(userInfos.pseudo, userInfos.password);
                    }
                }
            },

            logout: function(){
                logF.currentUser = {};
                $location.path('/');
                $cookies.remove('user_infos');
            },

            isConnected: function(){
                return !_.isEmpty(logF.currentUser);
            },

            getConnectionUrl: function(pseudo, pw){

                var tmp = Math.floor(Date.now() / 1000);
                var urlToReturn = '/connection?tmp='+tmp;

                // On récupère l'url en fonction de l'user
                urlToReturn = urlToReturn+'&user='+pseudo;
                var hash = CryptoJS.enc.Hex.stringify(CryptoJS.HmacSHA256(pseudo, CryptoJS.enc.Hex.stringify(CryptoJS.SHA256(pw))));
                var sign = CryptoJS.enc.Hex.stringify(CryptoJS.HmacSHA256(urlToReturn, hash));
                return '/api'+urlToReturn + '&signature='+sign;
            }

        };

        return logF;
    }]);

    app.factory('awfEntity', ['awfRess', 'awfUrl', function(awRess, awUrl){

        var EntityF = {

            ///////////////
            // Voir pour rajouter un systeme de stockage pour pas recharger 50 fois la même entité
            ///////////////

            getEntity: function(entityName, id, params){

                // On recup l'url
                var urlToCall = '/'+entityName+'/'+id;
                var finalUrl = awUrl.getUrl(urlToCall, params);

                var request = {
                    method: 'get',
                    url: finalUrl
                };

                // On renvoie la promesse
                return awRess.awRess(request);
            },

            getListEntity: function(entityName, params){

                // On récup l'url
                var urlToCall = '/'+entityName;
                var finalUrl = awUrl.getUrl(urlToCall, params);

                var request = {
                    method: 'get',
                    url: finalUrl
                };

                // On renvoie la promesse
                return awRess.awRess(request);
            },

            addEntity: function(entityName, dataEntity){
                // On récup l'url
                var urlToCall = '/'+entityName;
                var finalUrl = awUrl.getUrl(urlToCall, null);

                var request = {
                    method: "post",
                    url: finalUrl,
                    data: dataEntity
                };

                // On renvoie la promesse
                return awRess.awRess(request);
            },

            // Met à jour un user
            updateEntity: function(entityName, id, dataEntity){
                // On récup l'url
                var urlToCall = '/'+entityName+'/'+id;
                var finalUrl = awUrl.getUrl(urlToCall, null);

                var request = {
                    method: "post",
                    url: finalUrl,
                    data: dataEntity
                };

                // On renvoie la promesse
                return awRess.awRess(request);
            },

            deleteEntity: function(entityName, id){
                // On récup l'url
                var urlToCall = '/'+entityName+'/'+id;
                var finalUrl = awUrl.getUrl(urlToCall, null);

                var request = {
                    method: "delete",
                    url: finalUrl,
                };

                // On renvoie la promesse
                return awRess.awRess(request);
            }

        };

        return EntityF;

    }]);

    app.directive('awdLogout', function(){
        return {
            restrict: 'E',
            template:'<a href ng-click="logout()">Déconnexion</a>',
            controller: ['$scope', 'awfLogin', function($scope, awfLogin){
                $scope.logout = awfLogin.logout;
            }]
        };
    });

    // https://uncorkedstudios.com/blog/multipartformdata-file-upload-with-angularjs
    app.directive('awdFileModel', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function(scope, element, attrs) {
                var model = $parse(attrs.fileModel);
                var modelSetter = model.assign;

                element.bind('change', function(){
                    scope.$apply(function(){
                        modelSetter(scope, element[0].files[0]);
                    });
                });
            }
        };
    }]);

    // https://uncorkedstudios.com/blog/multipartformdata-file-upload-with-angularjs
    app.service('awfFileUpload', ['$http', 'awfUrl', '$q', 'awfFlash', function ($http, awfUrl, $q, awfFlash) {
        this.uploadFile = function(file){
            var deferred = $q.defer();

            var fd = new FormData();
            fd.append('file', file);
            $http.post(awfUrl.getUrl('/file'), fd, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            })
            .success(function(data, status){
                if (data.status == 200 || data.status == 201 || data.status == 204){
                    // On revoit les données si la requete a fonctionnee.
                    deferred.resolve(data.data);
                }
                else{
                    // Gestion des messages flash
                    awfFlash.add('error', data.error.message);
                    deferred.reject(data.error);
                }
            })
            .error(function(data){
                var msg = 'Problème lors de l\'execution de la requete.';
                awfFlash.add('error', msg);
                deferred.reject(msg);
            });

            return deferred.promise;
        };
    }]);
})();
