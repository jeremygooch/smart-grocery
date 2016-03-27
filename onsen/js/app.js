(function(){
    'use strict';
    var sg = angular.module('app', ['onsen']);

    // Main controller wrapping entire app
    sg.controller('AppController', function($scope, $data) {
        // Detect scroll height for sizing the topbar
        window.onscroll = function() { myFunction() };
        function myFunction() {
            console.log('whatre you doin!');
            if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
                console.log('whatre you doin!');
            }
        }

            
        
        $scope.doSomething = function() {
            setTimeout(function() {
                ons.notification.alert({ message: 'tapped' });
            }, 100);
        };
    });

    sg.controller('InventoryController', function($scope, $data) {
        $scope.item = $data.selectedItem;

        // Setup the fake list of items in the inventory
        $scope.inventory = {
            meat: [
                {label: 'Fish - River Cod',reserved: true, shelfLife: '4/15',daysLeft: '5',quantity: '4lbs' },
                {label: 'Beef - Ground', reserved: false, shelfLife: 'frozen', quantity: '1lbs' },
                {label:'Crispy Chicken Strips',reserved:false,shelfLife:'6/26',daysLeft:'45',quantity: '5lbs'},
                {label: 'Ribs', reserved: true, shelfLife: 'frozen', quantity: '2lbs' },
                {label: 'Turkey - Ground', reserved: false, shelfLife: 'frozen', quantity: '2lbs' }
            ],
            produce: [
                {label: 'Bellpeppers - Red',reserved: false, shelfLife: '4/30', daysLeft: '20', quantity: '2'},
                {label: 'Lettuce', reserved: true, shelfLife: '4/15', daysLeft: '5', quantity: '4cup' },
                {label: 'Onion', reserved: true, shelfLife: '4/22', daysLeft: '12', quantity: '1' },
                {label: 'Tomato', reserved: false, shelfLife: '4/20', daysLeft: '10', quantity: '2' }
            ],
            dairy: [
                {label: 'Milk',reserved: false,shelfLife: '4/22', daysLeft: '12', quantity: '.5gal' },
                {label: 'Eggs', reserved: true, shelfLife: '5/01', daysLeft: '21', quantity: '4cup' },
                {label: 'Whip Cream', reserved: true, shelfLife: '4/22', daysLeft: '12', quantity: '1' },
                {label: 'Cheese - Feta', reserved: false, shelfLife: '4/20', daysLeft: '10', quantity: '2' },
                {label: 'Sour Cream', reserved: false, shelfLife: '4/15', daysLeft: '5', quantity: '.5cup' },
                {label: 'Yogurt - Vanilla',reserved:false,shelfLife: '4/18', daysLeft: '8', quantity: '.5cup' }
            ],
            pantry: [
                {label: 'Bread',reserved: false,shelfLife: '4/15', daysLeft: '5', quantity: '0.5loaf' },
                {label: 'Raisins', reserved: true, shelfLife: '4/15', daysLeft: '5', quantity: '4cup' },
                {label: 'Flour', reserved: true, shelfLife: '4/22', daysLeft: '12', quantity: '16oz' },
                {label: 'Yeast', reserved: false, shelfLife: '4/20', daysLeft: '10', quantity: '6oz' },
                {label: 'Corn Starch', reserved: false, shelfLife: '8/25', daysLeft: '108', quantity: '12oz'},
                {label: 'Torillas', reserved: false, shelfLife: '5/20', daysLeft: '40', quantity: '6' },
                {label: 'Marinade', reserved: false, shelfLife: '7/28', daysLeft: '98', quantity: '12oz'},
                {label: 'Cake Mix - Yellow',reserved:false, shelfLife: '4/20',daysLeft:'10',quantity: '1box' },
                {label: 'Pitas', reserved: true, shelfLife: '4/25', daysLeft: '15', quantity: '12' },
                {label: 'Chips - Tortilla', reserved: false, shelfLife: '4/18',daysLeft: '8',quantity:'.5pkg' }
            ],
            other: [
                {label: 'Salsa - Red',reserved: false,shelfLife: '5/30', daysLeft: '50', quantity: '2.5cup' }
            ]
        };
        $scope.curList = $scope.inventory.meat;
        $scope.switchCatetory = function(cat) {
            $scope.curList = $scope.inventory[cat];
        };
    });


    

    sg.controller('DetailController', function($scope, $data) {
        $scope.item = $data.selectedItem;
    });

    sg.controller('MasterController', function($scope, $data) {
        $scope.items = $data.items;

        $scope.showDetail = function(index) {
            var selectedItem = $data.items[index], page = 'detail.html';
            $data.selectedItem = selectedItem;
            if (index == 0) { page='inventory.html'; }
            $scope.navi.pushPage(page, {title : selectedItem.title});
        };
    });

    sg.factory('$data', function() {
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

