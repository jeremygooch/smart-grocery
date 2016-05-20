describe('Smart Grocery Unit Tests', function () {
    beforeEach(angular.mock.module('app'));
    var $controller;

    beforeEach(angular.mock.inject(function(_$controller_){
        $controller = _$controller_;
    }));

    describe('AppController, Sum test', function () {
        it('1 + 1 should equal 2', function () {
            var $scope = {};
            var controller = $controller('AppController', { $scope: $scope });
            $scope.x = 1;
            $scope.y = 2;
            $scope.sum();
            expect($scope.z).toBe(3);
        });
    });

});
