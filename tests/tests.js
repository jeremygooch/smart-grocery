describe('Smart Grocery Unit Tests', function () {
    beforeEach(angular.mock.module('app'));
    var $controller, $httpBackend;

    beforeEach(angular.mock.inject(function(_$controller_, _$httpBackend_){
        $controller = _$controller_;
        $httpBackend = _$httpBackend_;
    }));

    describe('AppController, getAllReceipts tests', function () {
        var res = {
            data: {
                code: 200,
                data: {
                    'new': [],
                    old: [],
                    units: []
                },
                message: 'success'
            },
            status: 200
        };
        it('Values should be assigned appropriately when the server returns a 200', inject(function($http) {
            
            var $scope = {};
            var controller = $controller('AppController', { $scope: $scope });
            
            $scope.getAllReceipts(false);

            $httpBackend
                .when('POST', 'api/index.php', { api: 'receipts', method: 'getAllReceipts'})
                .respond(200, { foo: 'bar' });

            $httpBackend.flush();
            
            expect($scope.newReceipts).toEqual(res.data['new']);
            expect($scope.oldReceipts).toEqual(res.data.old);
            expect($scope.units).toEqual(res.data.units);

        }));
    });
});
