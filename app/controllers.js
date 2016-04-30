sg.controller('InventoryController', function($scope, $data, api) {
    $scope.item = $data.selectedItem;

    // Setup the fake list of items in the inventory
    $scope.inventory = {};
    $scope.selectedItems = [];

    $scope.contentLoaded = false;
    var data = {
        api    : 'inventory',
        method : 'getInventoryItems'
    };

    $scope.getInventory = function() {
        var getInventory = api.query('post', 'api/index.php', data);
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
    };
    $scope.getInventory();

    $scope.showActions = function(e) {
        if (e.target.checked) {
            // Add the item to the selected bucket
            $scope.selectedItems.push($scope.curList[e.target.getAttribute('item-reference')]);
        } else {
            // Remove the item from the selected bucket
            var index = $scope.selectedItems.indexOf($scope.curList[e.target.getAttribute('item-reference')]);
            if (index > -1) {
                $scope.selectedItems.splice(index, 1);
            }
        }
    };

    $scope.addNewItem = function() {
        console.log('Going to add a tasty treat to your stock');
    };
    $scope.deleteItems = function(items) {
        var data = { api: 'inventory', method: 'deleteItems', items: items };
        var deleteItem = api.query('POST', 'api/index.php', data);
        deleteItem.success(function(res) {
            if (res.code == 200) {
                // Drop the item from the view
                $scope.curList = _.difference($scope.curList, $scope.selectedItems);
                // Drop it from the main list
                Object.keys($scope.inventory).forEach(function(cat) {
                    $scope.inventory[cat] = _.difference($scope.inventory[cat], $scope.selectedItems);
                });
                // Reset the selection
                $scope.selectedItems = [];
            }
        });
    };

    function updateItem (id, field, value) {
        var fields = {};
        fields[field] = value;
        var data = { api: 'inventory', method: 'updateItem', id: id, data: fields};
        var updateItem = api.query('POST', 'api/index.php', data);
        updateItem.success(function(res) {
            if (res.code == 200) {
                // update the view
                var index = _.indexOf($scope.curList, _.find($scope.curList, {inventory_id: id}));
                $scope.curList[index][field] = value;
            }
        });
    }

    $scope.adjustItmValue = function(type, title, itm) {
        var units = itm.units ? ' ' + itm.units : "";
        var curValue = itm[type];
        var messageHTML = '<input class="range" type="range" id="adjustItmValue" min="1" max="10" value="' + curValue + '">';
        var valDisplay = '<span id="valDisplay" class="m-l-5 fade50">(' + itm.quantity + units + ')</span>';
        setTimeout(function() {
            ons.notification.confirm({
                messageHTML: messageHTML + valDisplay,
                buttonLabels: ["Cancel", "OK"],
                cancelable: true,
                animation: 'fade',
                title: title,
                callback: function(index) {
                    var q = document.getElementById('adjustItmValue').value;
                    if (q != curValue && index) {
                        updateItem(itm.inventory_id, type, q);
                    }
                }
            });
            document.getElementById('adjustItmValue').addEventListener('change', function(e) {
                var newVal = e.target.value;
                document.getElementById('valDisplay').innerHTML = '(' + newVal + units + ')';
            });
        }, 100);
    };
});


sg.controller('ReceiptsController', function($scope, $data, $timeout, $http, api) {
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

sg.controller('ReviewReceiptController', function($scope, $data, $timeout, api) {
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
        $scope.receipt.freezer = ($scope.receipt.freezer * 1) != 0 ? true : false;
        $scope.receipt.reserved = ($scope.receipt.reserved * 1) != 0 ? true : false;


        function removeItemFromView() {
            $data.reviewReceipt.receipt_data.splice(r,1);
            if ($data.reviewReceipt.receipt_data[r + 1]) {
                // Update the current receipt
                $scope.receipt = $data.reviewReceipt.receipt_data[r];
            } else if ($data.reviewReceipt.receipt_data[r - 1]) {
                $scope.receipt = $data.reviewReceipt.receipt_data[r - 1];
            } else {
                if ($data.reviewReceipt.receipt_data.length == 0) { // No more items left
                    $scope.receipt = false;
                    // Send api call to delete the receipt itself
                    $scope.archiveReceipt($data.reviewReceipt.id);
                } else {
                    // This is the last item
                    $scope.receipt = $data.reviewReceipt.receipt_data[0];
                }
            }
            // Need to convert freezer to an integer for onsen's switches to work properly
            if ($scope.receipt) {
                $scope.receipt.freezer = $data.reviewReceipt.receipt_data[r].freezer * 1;
            }
        }

        $scope.saveItem = function(receipt) {
            $scope.processingItem = true;
            var data = {
                api               : 'receipts',
                method            : 'saveItem',
                id                : receipt.id,
                inventory_id      : receipt.inventory_id,
                inventory_item_id : receipt.inventory_item_id,
                freezer           : receipt.freezer ? receipt.freezer : '0',
                expires           : receipt.exp.day + '-' + receipt.exp.month + '-' + receipt.exp.year,
                quantity          : receipt.quantity,
                units             : receipt.units,
                category          : receipt.category
            };
            var saveItem = api.query('post', 'api/index.php', data);
            saveItem.success(function(res) {
                if (res.code == 200) {
                    $scope.receipt.resetItem = false;
                    $scope.receipt.saved = true;
                    $timeout(function() {
                        $scope.processingItem = false;
                        removeItemFromView();
                        if ($scope.receipt) {
                            $scope.receipt.resetItem = true;
                        }
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
            var deleteItem = api.query('post', 'api/index.php', data);
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
            $scope.$apply(function() {
                $scope.receipt.freezer = !$scope.receipt.freezer;
            });
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
                $scope.receipt.freezer = ($scope.receipt.freezer * 1) != 0 ? true : false;
                $scope.receipt.reserved = ($scope.receipt.reserved * 1) != 0 ? true : false;
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




sg.controller('DetailController', function($scope, $data, api) {
    $scope.item = $data.selectedItem;
});


sg.controller('MainScreenController', function($scope, $data, $interval, api) {
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
        var newReceipts = api.query('post', 'api/index.php', data);
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
    // $interval(function() { getNewReceipts(); }, 1000);
});

// Main controller wrapping entire app
sg.controller('AppController', function($scope, $data, $http, api) {
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
        var newReceipts = api.query('post', 'api/index.php', data);
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
            api    : 'receipts',
            method : 'archiveReceipt',
            id     : id
        };
        var archiveRcpt = api.query('post', 'api/index.php', data);
        archiveRcpt.success(function(res) {
            if (res.code != 200) {
                console.dir(res);
            }
        });
        archiveRcpt.error(function(data, status, headers, config){
            console.dir(data);
        });
        archiveRcpt.finally(function() {});
    };

    var currentTime = new Date();
    $scope.currentYear = currentTime.getFullYear();

    $scope.col1 = 'style="width: 25%;"';

});
