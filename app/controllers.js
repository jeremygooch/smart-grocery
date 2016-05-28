sg.controller('InventoryController', function($scope, $filter, $data, $http, api) {
    $scope.item = $data.selectedItem;

    $scope.inventory = {};
    $scope.selectedItems = [];

    $scope.contentLoaded = false;
    var data = {
        api    : 'inventory',
        method : 'getInventoryItems'
    };

    $scope.getInventory = function(e) {
        $http.post('api/index.php', data).then(
            function(res) {
                if (res.data.code == 200) {
                    $scope.inventory = res.data.data;
                    $scope.curList = $scope.inventory.meat || [];
                    $scope.switchCatetory = function(cat) {
                        $scope.selectedItems.length = 0; // Uncheck the checkboxes
                        $scope.curList = $scope.inventory[cat] || [];
                    };
                }
            },
            function(error) {
                console.dir(error);
            }).finally(function() { $scope.contentLoaded = true; });
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
        $scope.navi.pushPage('addItem.html');
    };
    $scope.deleteItems = function(items) {
        var data = { api: 'inventory', method: 'deleteItems', items: items };
        $http.post('api/index.php', data).then(
            function(res) {
                if (res.data.code == 200) {
                    // Drop the item from the view
                    $scope.curList = _.difference($scope.curList, $scope.selectedItems);
                    // Drop it from the main list
                    Object.keys($scope.inventory).forEach(function(cat) {
                        $scope.inventory[cat] = _.difference($scope.inventory[cat], $scope.selectedItems);
                    });
                    // Reset the selection
                    $scope.selectedItems = [];
                }
            },
            function(error) {
                console.dir(error);
            });
    };

    $scope.qtyPending = []; // Need this to store which row may need to show a loading icon for updating a value

    function updateItem (id, field, value, cb) {
        var fields = {};
        fields[field] = value;
        var data = { api: 'inventory', method: 'updateItem', id: id, data: fields};
        var updateItem = api.query('POST', 'api/index.php', data);
        updateItem.success(function(res) {
            if (res.code == 200) {
                // update the view
                var index = _.indexOf($scope.curList, _.find($scope.curList, {inventory_id: id}));
                if (field != 'expires') {
                    $scope.curList[index][field] = value;
                } else {
                    $scope.curList[index]['daysLeft'] = res.data.daysLeft;
                    $scope.curList[index]['exp'] = res.data.exp;
                    $scope.curList[index]['expFlag'] = res.data.flag;
                }
            }
        });
        updateItem.finally(function() {
            if (typeof cb == "function") { cb(); }
        });
    }

    $scope.adjustQtyValue = function(itm) {
        var units = itm.units ? ' ' + itm.units : "";
        var curValue = itm['quantity'];
        var messageHTML = '<input class="range" type="range" id="adjustQtyValue" min="1" max="10" value="' + curValue + '">';
        var qtyDisplay = '<span id="qtyDisplay" class="m-l-5 fade50">(' + itm.quantity + units + ')</span>';
        setTimeout(function() {
            ons.notification.confirm({
                messageHTML: messageHTML + qtyDisplay,
                buttonLabels: ["Cancel", "OK"],
                cancelable: true,
                animation: 'fade',
                title: itm.description,
                callback: function(index) {
                    var q = document.getElementById('adjustQtyValue').value;
                    if (q != curValue && index === 1) {
                        $scope.qtyPending[itm.inventory_id] = true; // Icon
                        updateItem(itm.inventory_id, 'quantity', q, function() {
                            $scope.qtyPending[itm.inventory_id] = false;
                        });
                    }
                }
            });
            document.getElementById('adjustQtyValue').addEventListener('change', function(e) {
                var newVal = e.target.value;
                document.getElementById('qtyDisplay').innerHTML = '(' + newVal + units + ')';
            });
        }, 100);
    };


    $scope.adjustExpValue = function(itm) {
        var content = document.getElementById('hiddenExpContent'); // Grab the popup contents
        // Cannot use angular within the dom to update the values.
        // Update the elements before inserting them into the DOM
        content.querySelector('.expDate').innerHTML = '<span class="mo">' + $filter('monthToString')(itm.exp.month)+
            '</span> <span class="da">' + itm.exp.day + '</span>, <span class="yr">' + itm.exp.year + '</span>';
        content.querySelector('.slider').id = 'slider_' + itm.inventory_id;

        content.querySelector('.expDay').id = 'expDay_' + itm.inventory_id;
        content.querySelector('.expMonth').id = 'expMonth_' + itm.inventory_id;
        content.querySelector('.expYear').id = 'expYear_' + itm.inventory_id;
        
        // Update the freezer
        content.querySelector('.updateFreezer').id = 'updateFreezer_' + itm.inventory_id;
        if (itm.freezer == "1") {
            content.querySelector('.updateFreezer').setAttribute('checked','');
            content.querySelector('.slider').classList.add('closed');
        } else {
            content.querySelector('.updateFreezer').removeAttribute('checked','');
            content.querySelector('.slider').classList.remove('closed');
        }


        setTimeout(function() {
            ons.notification.confirm({
                messageHTML: content.innerHTML,
                buttonLabels: ["Cancel", "OK"],
                cancelable: true,
                animation: 'fade',
                title: itm.description,
                callback: function(index) {
                    if (index) {
                        // See if we need to update the expiration date
                        function updateExp() {
                            if (!freezer.checked) {
                                // See if the exp date changed
                                var expDay = document.querySelector('.alert-dialog-content #expDay_' + itm.inventory_id);
                                var expMonth = document.querySelector('.alert-dialog-content #expMonth_' + itm.inventory_id);
                                var expYear = document.querySelector('.alert-dialog-content #expYear_' + itm.inventory_id);
                                
                                if ((expDay.value != itm.exp.day) ||
                                    (expMonth.value != itm.exp.month) ||
                                    (expYear.value != itm.exp.year)) {
                                    updateItem(itm.inventory_id, 'expires', expYear.value+'-'+expMonth.value+'-'+expDay.value,
                                               function() {
                                                   //
                                               });
                                }
                            }
                        }
                        
                        // See if the freezer value changed
                        var freezer = document.querySelector('.alert-dialog-content #updateFreezer_' + itm.inventory_id);
                        if ((freezer.checked && itm.freezer == '0') || (!freezer.checked && itm.freezer == '1')) {
                            var f = freezer.checked ? "1" : "0";
                            updateItem(itm.inventory_id, 'freezer', f, function() {
                                // $scope.qtyPending[itm.inventory_id] = false;
                                updateExp();
                            });
                        } else { updateExp(); }
                    }
                }
            });

            // Updating slider values after they're rendered in the dom
            document.querySelector('.alert-dialog-content #expDay_' + itm.inventory_id).value = itm.exp.day;
            document.querySelector('.alert-dialog-content #expMonth_' + itm.inventory_id).value = itm.exp.month;
            document.querySelector('.alert-dialog-content #expYear_' + itm.inventory_id).value = itm.exp.year;

            document.querySelector('body').addEventListener('click', function(event) {
                var target = event.target;
                switch (target.id) {
                case 'updateFreezer_' + itm.inventory_id:
                    if (target.checked) {
                        document.querySelector('.alert-dialog-content #slider_' + itm.inventory_id).classList.add('closed');
                    } else {
                        document.querySelector('.alert-dialog-content #slider_' + itm.inventory_id).classList.remove('closed');
                    }
                    break;
                case 'expMonth_' + itm.inventory_id:
                    document.querySelector('.alert-dialog-content .mo').innerHTML = $filter('monthToString')(target.value);
                    break;
                case 'expDay_' + itm.inventory_id:
                    document.querySelector('.alert-dialog-content .da').innerHTML = target.value;
                    break;
                case 'expYear_' + itm.inventory_id:
                    document.querySelector('.alert-dialog-content .yr').innerHTML = target.value;
                    break;
                default:
                    break;
                };
                
            });
            
        }, 100);
    };
});

sg.controller('recipesController', function($scope, $recipe, api) {
    $scope.contentLoaded = false;
    // Load the recipes initially
    $scope.resultPage = 1;
    $scope.loadingRecipes = false;
    $scope.getRecipes = function() {
        $scope.loadingRecipes = true;        
        var data = { api: 'recipes', method: 'getRecipesByCurrentInventory', page: $scope.resultPage++ };
        var newRecipes = api.query('post', 'api/index.php', data);
        newRecipes.success(function (res) {
            if (res.code == 200) {
                var recipes = JSON.parse(res.data);
                if (!$scope.recipes) {
                    $scope.recipes = recipes.recipes;
                } else {
                    // Add the new items to the existing array
                    $scope.recipes = _.union($scope.recipes, recipes.recipes);
                }
            }
        });
        newRecipes.error(function(data, status, headers, config){
            console.dir(data);
        });
        newRecipes.finally(function() {
            $scope.loadingRecipes = false;
            $scope.contentLoaded = true;
        });
    };
    $scope.getRecipes();

    $scope.getRecipeById = function(id) {
        var data = { api: 'recipes', method: 'getRecipesById', id: id };
        var newRecipes = api.query('post', 'api/index.php', data);
        newRecipes.success(function (res) {
            if (res.code == 200) {
                $recipe.setRecipe(JSON.parse(res.data));
                $scope.navi.pushPage('recipe.html');
            }
        });
        newRecipes.error(function(data, status, headers, config){
            console.dir(data);
        });
        newRecipes.finally(function() {
            $scope.loadingRecipes = false;
            $scope.contentLoaded = true;
        });
    };
});

sg.controller('recipeController', function($scope, $sce, $recipe, api) {
    $scope.contentLoaded = false;
    var recipe = $recipe.getRecipe();
    $scope.recipe = recipe.recipe;
    $scope.contentLoaded = true;

    $scope.frameLoaded = false;

    $scope.recipe_url = $sce.trustAsResourceUrl($scope.recipe.source_url);
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

    $scope.deleteReceipt = function(id) {
        var data = {
            api    : 'receipts',
            method : 'deleteReceipt',
            id     : id
        };
        var delReceipt = api.query('post', 'api/index.php', data);
        delReceipt.success(function(res) {
            if (res.code == 200) {
                $scope.getAllReceipts();
            } else {
                console.dir(res);
            }
        });
        delReceipt.error(function(data, status, headers, config){
            console.dir(data);
        });
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

sg.controller('AddItemController', function($scope, $data, $http, api) {
    var data = {
        api    : 'inventory',
        method : 'getAllInventoryItems'
    };
    $http.post('api/index.php', data).then(
        function(res) {
            console.log(res);
            if (res.data.code == 200) {
                $scope.allItems = res.data.data;
                $scope.categories = [];
                Object.keys(res.data.data).forEach(function(category) {
                    $scope.categories.push(category);
                });
                console.log($scope.categories);
            }
        },
        function(error) {
            console.dir(error);
        }).finally(function() { $scope.contentLoaded = true; });

    $scope.test = function() {
        console.log('caught!');
    };
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
        case 1:
            page = 'recipes.html';
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
sg.controller('AppController', function ($scope, $data, $http, api) {
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
        $http.post('api/index.php', data)
            .then(
            function(res) {
                if (res.status == 200 && res.data.code == 200) {
                    $scope.newReceipts = res.data.data['new']; // new is reserved in JS
                    $scope.oldReceipts = res.data.data.old;
                    $scope.units = res.data.data.units;
                    if (typeof cb === 'function') { cb(); }
                } else {
                    console.dir(res);
                }
            },
            function(error) {
                console.dir(res);
            }
        ).finally(
            function() {
                
            }
        );
    }

    $scope.archiveReceipt = function(id) {
        var data = {
            api    : 'receipts',
            method : 'archiveReceipt',
            id     : id
        };
        $http.post('api/index.php', data).then(
            function(res) {
                if (res.data.code != 200) {
                    console.dir(res);
                }
            },
            function(error) {
                console.dir(error);
            });
    };

    var currentTime = new Date();
    $scope.currentYear = currentTime.getFullYear();

    $scope.col1 = 'style="width: 25%;"';

});
