sg.controller('InventoryController', function($scope, $data) {
    $scope.item = $data.selectedItem;

    // Setup the fake list of items in the inventory
    $scope.inventory = {};

    $scope.contentLoaded = false;
    var data = {
        api    : 'inventory',
        method : 'getInventoryItems'
    };
    var getInventory = $scope.apiRequest('post', 'api/index.php', data);
    getInventory.success(function(res) {
        if (res.code == 200) {
            $scope.inventory = res.data;
            $scope.curList = $scope.inventory.meat;
            $scope.switchCatetory = function(cat) {
                $scope.curList = $scope.inventory[cat];
            };
        } else {
            console.dir(res);
        }
    });
    getInventory.error(function(data, status, headers, config){
        console.dir(data);
    });
    getInventory.finally(function() {
        $scope.contentLoaded = true;
    });
});


sg.controller('ReceiptsController', function($scope, $data, $timeout, $http) {
    $scope.item = $data.selectedItem;
    // Set the loading animation
    $scope.contentLoaded = false;

    
    // Get the old and new receipts
    $scope.getAllReceipts(function() {
        $scope.contentLoaded = true;
    });
    
    $scope.reviewReceipt = function(id) {
        $data.reviewReceiptId = id;
        $scope.navi.pushPage('reviewReceipt.html', {title : 'Review Receipt' });
    };




    // /////////////////
    // Refresh
    // /////////////////
    $scope.items = [];
    $scope.load = function($done) {
      $timeout(function() {
        $http.jsonp('http://numbersapi.com/random/year?callback=JSON_CALLBACK')
          .success(function(data) {
            $scope.items.unshift({
              desc: data,
              rand: Math.random()
            });
          })
          .error(function() {
            $scope.items.unshift({
              desc: 'No data',
              rand: Math.random()
            });
          })
          .finally(function() {
            $done();
          });
      }, 1000);
    };
    $scope.reset = function() {
      $scope.items.length = 0;
    }
});

sg.controller('ReviewReceiptController', function($scope, $data, $timeout) {
    // Set the loading animation
    $scope.contentLoaded = false;
    // Get the old and new receipts
    $scope.getAllReceipts(function() {
        // Hide the loading animation
        $scope.contentLoaded = true;
        if ($data.reviewReceiptId)
            for (var i=0; i<$scope.newReceipts.length; i++) {
                if ($scope.newReceipts[i].id &&
                    ($scope.newReceipts[i].receipt_data && $scope.newReceipts[i].receipt_data.length > 0)) {
                    $data.reviewReceipt = $scope.newReceipts[i];
                }
            }

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


        function removeItemFromView() {
            $data.reviewReceipt.receipt_data.splice(r,1);
            if ($data.reviewReceipt.receipt_data[r + 1]) {
                // Update the current receipt
                $scope.receipt = $data.reviewReceipt.receipt_data[r];
            } else if ($data.reviewReceipt.receipt_data[r - 1]) {
                $scope.receipt = $data.reviewReceipt.receipt_data[r - 1];
            } else {
                $scope.receipt = false;
                // Send api call to delete the receipt itself
                $scope.archiveReceipt($data.reviewReceipt.id);
            }
        }

        $scope.saveItem = function(receipt) {
            $scope.processingItem = true;
            var data = {
                api               : 'receipts',
                method            : 'saveItem',
                id                : receipt.id,
                inventory_item_id : receipt.inventory_item_id,
                expires           : receipt.exp.day + '-' + receipt.exp.month + '-' + receipt.exp.year,
                quantity          : receipt.quantity,
                units             : receipt.units,
                category          : receipt.category
            };
            var saveItem = $scope.apiRequest('post', 'api/index.php', data);
            saveItem.success(function(res) {
                if (res.code == 200) {
                    $scope.receipt.resetItem = false;
                    $scope.receipt.saved = true;
                    $timeout(function() {
                        $scope.processingItem = false;
                        removeItemFromView();
                        $scope.receipt.resetItem = true;
                    }, 740);
                } else {
                    console.dir(res);
                }
            });
            saveItem.error(function(data, status, headers, config){
                console.dir(data);
            });
            saveItem.finally(function() {
                $scope.processingItem = false;
            });
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
                        $scope.receipt.deleted = false;
                        removeItemFromView();
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
    });

    // Setup the various units
    $scope.units = {
        standard: ['bag', 'bottle', 'box', 'bunch', 'can', 'container', 'cups', 'liter',
                   'oz', 'package', 'quart', ''],
        butter: ['sticks', 'small tub', 'medium tub', 'large tub']
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

    $scope.getAllReceipts = function(cb) {
        var data = { api: 'receipts', method: 'getAllReceipts' };
        var newReceipts = $scope.apiRequest('post', 'api/index.php', data);
        newReceipts.success(function (res) {
            if (res.code == 200) {
                $scope.newReceipts = res.data['new']; // new is reserved in JS
                $scope.oldReceipts = res.data.old;
                if (typeof cb === 'function') { cb(); }
            } else {
                console.dir(res);
            }
        });
        newReceipts.error(function(data, status, headers, config){
            console.dir(data);
        });
    }

    $scope.archiveReceipt = function(id) {
        var data = {
            api               : 'receipts',
            method            : 'archiveReceipt',
            id                : id
        };
        var archiveRcpt = $scope.apiRequest('post', 'api/index.php', data);
        archiveRcpt.success(function(res) {
            if (res.code != 200) {
                console.dir(res);
            }
        });
        archiveRcpt.error(function(data, status, headers, config){
            console.dir(data);
        });
        archiveRcpt.finally(function() {
            //
        });
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
