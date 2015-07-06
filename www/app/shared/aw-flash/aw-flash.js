//      Done, wihtout bugs
//
//      01/06/2015
//
//      Alexis

(function(){

    var app = angular.module('awFlash', []);

    app.factory('awfFlash', function(){

        var awF = {

            nextId : 1,
            messages: [],

            add: function(typeP, msgP){
                awF.messages.push({id: awF.nextId, type: typeP, message: msgP});
                awF.nextId++;
            },

            remove: function(idP){
                awF.messages = _.without(awF.messages, _.findWhere(awF.messages, {id: idP}));
                // New object : need to reuse getMessages
            },

            getMessages: function(){
                return awF.messages;
            }
        };

        return awF;

    });

    app.directive('awdFlash', function(){
        return {
            restrict: 'A',
            templateUrl:'/app/shared/aw-flash/aw-flash.html',
            controller: ['$scope', 'awfFlash', function($scope, awfFlash){
                $scope.messages = awfFlash.getMessages();
                $scope.remove = function(id){
                    awfFlash.remove(id);
                    $scope.messages = awfFlash.getMessages();
                };
            }]
        };
    });
})();
