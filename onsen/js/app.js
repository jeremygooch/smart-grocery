(function(){
    'use strict';
    var module = angular.module('app', ['onsen']);

    module.controller('AppController', function($scope, $data) {
        // Main controller wrapping entire app
        
        $scope.doSomething = function() {
            setTimeout(function() {
                ons.notification.alert({ message: 'tapped' });
            }, 100);
        };
    });

    module.controller('InventoryController', function($scope, $data) {
        $scope.item = $data.selectedItem;
        $scope.inventory = {
            meat: [
                {
                    label: 'Crispy Chicken Strips',
                    reserved: false,
                    shelfLife: '6/26',
                    daysLeft: '45',
                    quantity: '5lbs'
                },
                {
                    label: 'Ribs',
                    reserved: true,
                    shelfLife: 'frozen',
                    quantity: '2lbs'
                },
                {
                    label: 'Ground Turkey',
                    reserved: false,
                    shelfLife: 'frozen',
                    quantity: '2lbs'
                },
                {
                    label: 'Ground Beef',
                    reserved: false,
                    shelfLife: 'frozen',
                    quantity: '1lbs'
                },
                {
                    label: 'Fish - River Cod',
                    reserved: true,
                    shelfLife: '4/15',
                    daysLeft: '5',
                    quantity: '4lbs'
                }
            ],
            produce: [

            ]
        };
    });


    

    module.controller('DetailController', function($scope, $data) {
        $scope.item = $data.selectedItem;
    });

    module.controller('MasterController', function($scope, $data) {
        $scope.items = $data.items;

        $scope.showDetail = function(index) {
            var selectedItem = $data.items[index], page = 'detail.html';
            $data.selectedItem = selectedItem;
            if (index == 0) { page='inventory.html'; }
            $scope.navi.pushPage(page, {title : selectedItem.title});
        };
    });

    module.factory('$data', function() {
        var data = {};

        data.items = [
            {
                title: 'Inventory',
                notify: '2',
                controller: '',
                icon: 'inventory-thum',
                desc: 'View and update any of the items in your currrent inventory.'
            },
            {
                title: 'Recipes',
                icon: 'recipes',
                desc: 'View and update up any of your saved recipes.'
            },
            {
                title: 'Receipts',
                icon: 'receipts',
                notify: '1',
                desc: 'Review any uploaded receipts, and view any previous receipts.'
            },
            {
                title: 'Grocery List',
                icon: 'grocery',
                desc: 'Build a grocery list based off any new or old receipes.'
            }
        ];

        return data;
    });
})();

