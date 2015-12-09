busApplication.controller("BusController", ['$scope', '$http', function($scope, $http) {
    $scope.przystanek = "";
    $scope.przystanki = [];
    $scope.wszystkiePrzystanki = [];
    $scope.odjazdy = [];
    $scope.najblizszeOdjazdy = [];
    
    $scope.pobierzPrzystanki = function() {
        $http({
            url: Routing.generate('bus-stops'),
            method: 'GET'
        }).success(function(data) {
            $scope.przystanki = data;
            $scope.wszystkiePrzystanki = data;
        })
    }
    
    $scope.szukajPrzystanku = function() {
        if ($scope.przystanek) {
            $scope.przystanki = [];
            for (var i = 0; i < $scope.wszystkiePrzystanki.length; i++) {
                if ($scope.wszystkiePrzystanki[i].name.indexOf($scope.przystanek) > -1) {
                    $scope.przystanki.push($scope.wszystkiePrzystanki[i]);
                }
            }
        } else {
            $scope.przystanki = $scope.wszystkiePrzystanki;
        }
    }
    
    $scope.pobierzOdjazdy = function(id) {
    	$http({
            url: Routing.generate('bus-stops-get', {
                'id' : id
            }),
            method: 'GET'
        }).success(function(data) {
            $scope.odjazdy = data;
            $scope.wyswietlOdjazdy();
        })
    }
    
    $scope.wyswietlOdjazdy = function()
    {
        $scope.nazwaPrzystanku = $scope.odjazdy.name;
        $scope.najblizszeOdjazdy = $scope.odjazdy.departures;
    }
}]);