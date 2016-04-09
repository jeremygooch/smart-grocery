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


sg.controller('ReceiptsController', function($scope, $data) {
    $scope.item = $data.selectedItem;

    // Get the old and new receipts
    var data = { api: 'receipts', method: 'getAllReceipts' }
    var newReceipts = $scope.apiRequest('post', 'api/index.php', data);
    newReceipts.success(function (res) {
        if (res.code == 200) {
            $scope.newReceipts = res.data['new']; // new is reserved
            $scope.oldReceipts = res.data.old;
        } else {
            console.dir(res);
        }
    });
    newReceipts.error(function(data, status, headers, config){
        console.dir(data);
    });

    
    $scope.reviewReceipt = function(id) {
        for (var i=0; i<$scope.newReceipts.length; i++) {
            if ($scope.newReceipts[i].id) {
                $data.reviewReceipt = $scope.newReceipts[i];
                $scope.navi.pushPage('reviewReceipt.html', {title : 'Review Receipt' });
            }
        }
    };
});

sg.controller('ReviewReceiptController', function($scope, $data, $timeout) {
    var r = 0; tot = $data.reviewReceipt.receipt_data.length;

    // Update the pagination for the UI
    $scope.prevReceipts = false;
    $scope.nextReceipts = tot > 1 ? true : false;
    
    // Assign the current receipt
    $scope.receipt = $data.reviewReceipt.receipt_data[r];

    $scope.reviewReceiptTitle = $data.reviewReceipt.location;

    // Update the switches
    $scope.receipt.freezer = $scope.receipt.freezer != '0' ? true : false;
    $scope.receipt.reserved = $scope.receipt.reserved != '0' ? true : false;

    // Setup the various units
    $scope.units = {
        standard: ['bag', 'bottle', 'box', 'bunch', 'can', 'container', 'cups', 'liter',
                   'oz', 'package', 'quart'],
        butter: ['sticks', 'small tub', 'medium tub', 'large tub']
    };

    $scope.saveItem = function(id) {
        $scope.processingItem = true;
        console.log('here?');
    };

    $scope.deleteItem = function(id) {
        var data = { api: 'receipts', method: 'deleteItem', item_id: id };
        var deleteItem = $scope.apiRequest('post', 'api/index.php', data);
        deleteItem.success(function (res) {
            if (res.code == 200) {
                // Play out deleted animation
                $scope.receipt.resetItem = false;
                $scope.receipt.deleted = true;
                $timeout(function() {
                    $data.reviewReceipt.receipt_data.splice(r,1);
                    $scope.receipt.deleted = false;
                    if ($data.reviewReceipt.receipt_data[r + 1]) {
                        $scope.changeReceipt('next');
                    } else if ($data.reviewReceipt.receipt_data[r - 1]) {
                        console.log('FINISHED!!!!');
                    }
                    $scope.receipt.resetItem = true;
                }, 640);
                
                
            } else {
                console.dir(res);
            }
        });
        deleteItem.error(function(data, status, headers, config){
            console.dir(data);
        });
    };

    

    document.getElementById('freezer').addEventListener('change', function(event) {
        $scope.$apply(function() { $scope.receipt.freezer = !$scope.receipt.freezer; });
    });

    // Change receipt
    $scope.changeReceipt = function(direction) {
        /* This function progresses through the receipts in the $data object
         * 
         * @direction[str]: 'next'  or 'back'
         */
        
        if (direction == 'next') {
            r = r + 1 <= (tot-1) ? r+1 : r; // Make sure we haven't max-ed out
        } else {
            r = r - 1 >= 0 ? r-1 : r; // Make sure we haven't min-ed out
        }

        function checkPagination() {
            // Cleanup for afterwards
            $scope.prevReceipts = r == 0 ? false : true
            $scope.nextReceipts = r == (tot - 1) ? false : true;
        }
        
        if ($data.reviewReceipt.receipt_data[r]) {
            $scope.receipt = $data.reviewReceipt.receipt_data[r];
            // Update the switches
            $scope.receipt.freezer = $scope.receipt.freezer != '0' ? true : false;
            $scope.receipt.reserved = $scope.receipt.reserved != '0' ? true : false;
            checkPagination();
        } else {
            // We reached the end of the receipts
            checkPagination();
        }
    };

    $scope.saveItem = function(id) {
        // Do some logic
        

        // Advance to next item
        // $scope.changeReceipt('next');
    };

    
    // ////////////////////////////////////////////
    // Recipes not yet implemented
    // ////////////////////////////////////////////
    // document.getElementById('reserved').addEventListener('change', function(event) {
    //     $scope.$apply(function() { $scope.receipt.items[0].reserved = !$scope.receipt.items[0].reserved; });
    // });
});




sg.controller('DetailController', function($scope, $data) {
    $scope.item = $data.selectedItem;
});

sg.controller('MasterController', function($scope, $data, $interval) {
    // Initial Page setup
    $scope.items = $data.items;
    $scope.loadView = function(index) {
        var page;
        switch (index) {
        case 0:
            page = 'inventory.html';
            break;
        case 2:
            page = 'receipts.html';
            break;
        default:
            page = 'detail.html';
            break;
        };

        // Navigation
        var selectedItem = $data.items[index];
        $data.selectedItem = selectedItem;
        $scope.navi.pushPage(page, {title : selectedItem.title});
    };



    
    // Start listening for notifications
    function getNewReceipts() {
        var data = { api: 'receipts', method: 'getNewReceipts' }
        var newReceipts = $scope.apiRequest('post', 'api/index.php', data);
        newReceipts.success(function (res) {
            if (res.code == 200) {
                if (res.data.new_receipt_count > 0) {
                    $data.items[2].notify = res.data.new_receipt_count;
                } else {
                    delete $data.items[2].notify;
                }
            } else {
                console.dir(res);
            }
        });
        newReceipts.error(function(data, status, headers, config){
            console.dir(data);
        });
    }
    getNewReceipts();
    $interval(function() { getNewReceipts(); }, 1000);




});

// Main controller wrapping entire app
sg.controller('AppController', function($scope, $data, $http) {
    // Detect scroll height for sizing the topbar
    $scope.showMore = function() {
        console.log('show more triggered');  
    };
    
    $scope.doSomething = function() {
        setTimeout(function() {
            ons.notification.alert({ message: 'tapped' });
        }, 100);
    };

    var currentTime = new Date();
    $scope.currentYear = currentTime.getFullYear();

    $scope.apiRequest = function(type, api, data, async) {
        /*
         * This fuction makes a basic request to the champHR API
         * based off of the provided parameter
         *
         * @type    = The type of request being made (i.e. POST, GET)
         * @api     = The api to be requested
         * @data    = The data payload
         * @async   = Should the request be sent asyncronysoulsly? [default: false]
         *
         * @RETURN = The results of the request as an object
         */

        // var $http = angular.element(document.body).injector().get('$http');
        var request = $http({
            method: type,
            url: api,
            data: data,
            async: (async) ? true : false,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });
        /* Check whether the HTTP Request is successful or not. */
        return request;
    };

    $scope.col1 = 'style="width: 25%;"';

});
