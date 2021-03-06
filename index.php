<!DOCTYPE html>
<!-- CSP support mode (required for Windows Universal apps): https://docs.angularjs.org/api/ng/directive/ngCsp -->
<!-- ------------------------------------ -->
<!-- 0. Login -->
<!-- 1. Main Screen -->
<!-- 2. Inventory -->
<!-- 3. Receipts -->
<!-- 4. Review Receipt -->
<!-- 5. Recipes -->
<!-- 6. Recipe -->
<!-- 7. Add Item To Inventory -->
<!-- ??. General Detail -->
<!-- ------------------------------------ -->
<html lang="en" ng-app="app" ng-csp>
<head>
    <meta charset="utf-8" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />

    <!-- JS dependencies (order matters!) -->
    <script src="libs/lodash.js"></script>
    <script src="libs/angular/angular.js"></script>
    <script src="libs/onsenui.js"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet'"type="text/css">


    <!-- CSS dependencies -->
    <link rel="stylesheet" href="css/onsenui.css" />
    <link rel="stylesheet" href="css/onsen-css-components-blue-basic-theme.css" />
    <link rel="stylesheet" href="css/theme.css" />
    <link rel="stylesheet" href="css/smart-grocery.css" />
    <link rel="stylesheet" href="css/typicons/typicons.min.css" >

    <!-- CSP support mode (required for Windows Universal apps) -->
    <link rel="stylesheet" href="libs/angular/angular-csp.css" />


    <!-- --------------- App init --------------- -->
    <script src="app/app.js"></script>
    <script src="app/controllers.js"></script>

    <title>:: Smart Grocery ::</title>
</head>

<body ng-controller="AppController">

    <!-- ----------------------------------------- -->
    <!-- 0. LOGIN -->
    <!-- ----------------------------------------- -->
    <ons-navigator var="navi" page="login.html"></ons-navigator>
    <ons-template id="login.html">
        <ons-page ng-controller="loginController">
            <ons-toolbar>
                <div class="center">Please Sign In</div>
            </ons-toolbar>

            <div style="text-align: center; margin-top: 30px;">
                Username: <input type="text" class="text-input--underbar" ng-model="username" value="">
            </div>

            <div style="text-align: center; margin-top: 30px;">
                Password: <input type="password" class="text-input--underbar" ng-model="password" value="" />
            </div>  

            <div style="text-align: center;margin-top: 30px;">
                <ons-button ng-click="checkLogin()" class="button button--outline"
                            ng-disabled="!username || !password">
                    <ons-icon icon="fa-sign-in" size="15px" fixed-width="false"></ons-icon>
                    Sign In 
                </ons-button>
            </div>
        </ons-page>
    </ons-template>



    
    <!-- ----------------------------------------- -->
    <!-- 1. MAIN SCREEN -->
    <!-- ----------------------------------------- -->
    <ons-template id="main.html">
        <ons-page>
            <ons-toolbar><div class="center">Smart Grocery</div></ons-toolbar>
            <ons-list ng-controller="MainScreenController" class="main-screen">
                <ons-list-item modifier="chevron" class="item" ng-class="{'inventory' : item.title == 'Inventory', 'recipes' : item.title == 'Recipes', 'receipts' : item.title == 'Receipts', 'grocery-list' : item.title == 'Grocery List'}" ng-repeat="item in items" ng-click="loadView($index)">
                    <ons-row>
                        <ons-col>
                            <header class="p-l-25">
                                <span class="item-title">{{item.title}}</span>
                                <span class="item-label">
                                    <span class="notification" data-ng-show="item.notify">{{item.notify}}</span>
                                </span>
                            </header>
                        </ons-col>
                    </ons-row>
                </ons-list-item>
            </ons-list>
        </ons-page>
    </ons-template>

    
    <!-- ----------------------------------------- -->
    <!-- 2. INVENTORY -->
    <!-- ----------------------------------------- -->
    <ons-template id="inventory.html">
        <ons-page ng-controller="InventoryController">
            <div class="loading" ng-class="{ hide : contentLoaded }"><ons-icon icon="fa-spinner" size="80px" spin="true"></div>
            <ons-toolbar>
                <div class="left"><ons-back-button></ons-back-button></div>
                <div class="center">Inventory</div>
            </ons-toolbar>
            <div class="button-bar" style="margin: 25px auto 7px auto; padding: 0 10px;">
                <div class="button-bar__item" id="invMeat" ng-click="switchCatetory('meat')">
                    <input type="radio" name="navi-segment-a" ng-checked="resetCategory">
                    <div class="button-bar__button">Meat</div>
                </div>

                <div class="button-bar__item" id="invProduce" ng-click="switchCatetory('produce')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Produce</div>
                </div>

                <div class="button-bar__item" id="invDairy" ng-click="switchCatetory('dairy')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Dairy</div>
                </div>

                <div class="button-bar__item" id="invPantry" ng-click="switchCatetory('instant')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Instant</div>
                </div>

                <div class="button-bar__item" id="invOther" ng-click="switchCatetory('dry')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Dry</div>
                </div>

                <div class="button-bar__item" id="invOther" ng-click="switchCatetory('bread')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Bread</div>
                </div>

                <div class="button-bar__item" id="invOther" ng-click="switchCatetory('can')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Can</div>
                </div>

                <div class="button-bar__item" id="invOther" ng-click="switchCatetory('other')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Other</div>
                </div>
            </div>
            <ons-list style="margin-top: 10px">
                <div ng-show="curList.length == 0" class="fade50 text-center m-t-10 m-b-10">
                    <em>There are currently no items in this category.</em>
                </div>
                <ul class="list" ng-show="curList.length > 0">
                    <li class="list__item list__item--tappable" ng-repeat="item in curList"
                        id="meat_{{ $index }}">
                        <label class="checkbox checkbox--list-item">
                            <input type="checkbox" item-reference="{{ $index }}"
                                   ng-click="showActions($event);">
                            <div class="checkbox__checkmark checkbox--noborder checkbox--list-item__checkmark"></div>
                            <span class="spCol col3" ng-class="{ 'danger': item.expFlag == 'danger' && item.freezer != '1', 'warning': item.expFlag == 'warning' && item.freezer != '1' }">
                                {{ item.description }}
                            </span>
                            <span class="spCol col2" ng-click="adjustExpValue(item)">
                                <button class="button--quiet"
                                        ng-class="{ 'danger': item.expFlag == 'danger' && item.freezer != '1', 'warning': item.expFlag == 'warning' && item.freezer != '1' }">
                                    {{ item.freezer == '1' ? 'Frozen' : item.daysLeft }}
                                </button>
                            </span>
                            <span class="spCol col2" ng-click="adjustQtyValue(item)">
                                <button class="button--quiet">
                                    <span ng-show="!qtyPending[item.inventory_id]">
                                        {{ item.quantity }} {{ item.units ? item.units : '' }}
                                    </span>
                                    <ons-icon icon="fa-spinner fa-spin" size="15px" fixed-width="false"
                                              ng-show="qtyPending[item.inventory_id]"></ons-icon>
                                </button>
                            </span>
                        </label>
                    </li>
                </ul>

                <div id="hiddenExpContent" class="hide">
                    <ons-row>
                        <ons-col>
                            <label class="checkbox checkbox--list-item">
                                <div class="pull-left m-r-10">Freezing</div>
                                <input type="checkbox" class="updateFreezer">
                                <div class="checkbox__checkmark checkbox--list-item__checkmark"></div>
                            </label>
                        </ons-col>
                    </ons-row>
                    <ons-row>
                        <ons-col>
                            <div class="slider">
                                <label for="itemExpMo">Expires: </label>
                                <div class="expDate"></div>
                                <div class="clearfix"></div>
                                <label for="expMonth" class="text-right">
                                    <em>Month</em>
                                </label>
                                <input type="range" class="range expMonth" min="1" max="12" value="">
                                <div class="clearfix"></div>
                                <label for="expDay" class="text-right">
                                    <em>Day</em>
                                </label>
                                <input type="range" class="range expDay" min="1" max="31" value="">
                                <div class="clearfix"></div>
                                <label for="expYear" class="text-right">
                                    <em>Year</em>
                                </label>
                                <input type="range" class="range expYear" min="{{ currentYear }}" max="{{ currentYear +5 }}" value="">
                                <div class="clearfix"></div>
                            </div>
                        </ons-col>
                    </ons-row>
                </div>
                
            </ons-list>
            <button class="button button-small floating-action-button"
                    ng-click="addNewItem()"><ons-icon size="35px" icon="ion-plus"></ons-icon></button>
            <button class="button button-small button--outline pull-left m-t-15 m-l-15"
                    ng-click="deleteItems(selectedItems)"
                    ng-class="{ hide: selectedItems.length <= 0 }">Delete Selected ({{ selectedItems.length }})</button>
        </ons-page>
    </ons-template>


    <!-- ----------------------------------------- -->
    <!-- 3. RECEIPTS -->
    <!-- ----------------------------------------- -->
    <ons-template id="receipts.html">
        <ons-page ng-controller="ReceiptsController">
            <div class="loading" ng-class="{ hide : contentLoaded }"><ons-icon icon="fa-spinner" size="80px" spin="true"></div>
            <ons-toolbar>
                <div class="left"><ons-back-button></ons-back-button></div>
                <div class="center">Receipts</div>
            </ons-toolbar>



            <!-- Refresh -->
            <ons-pull-hook ng-action="load($done)" var="loader">
                <span ng-switch="loader.getCurrentState()">
                    <span ng-switch-when="initial"><ons-icon size="35px" icon="ion-arrow-down-a"></ons-icon> Pull down to refresh</span>
                    <span ng-switch-when="preaction"><ons-icon size="35px" icon="ion-arrow-up-a"></ons-icon> Release to refresh</span>
                    <span ng-switch-when="action"><ons-icon size="35px" spin="true" icon="ion-load-d"></ons-icon> Loading data...</span>
                </span>
            </ons-pull-hook>



            <div ng-show="newReceipts.length == 0 && oldReceipts.length == 0" class="fade50 text-center m-t-10 m-b-10">
                <em>No receipts have been scanned yet.</em>
            </div>
            <ons-list modifier="inset" class="m-t-10" ng-show="newReceipts.length > 0 || oldReceipts.length > 0">
                <ul class="list">
                    <li class="list__header" ng-show="newReceipts.length > 0">
                        New Receipts
                    </li>
                    <li class="list__item" ng-show="newReceipts.length > 0"
                        ng-repeat="receipt in newReceipts">
                        <span class="spCol col2">{{ receipt.location }}</span>
                        <span class="spCol col2">{{ receipt.receipt_data.length > 0 ? receipt.receipt_data.length : 'No' }} Items</span>
                        <span class="pull-right">
                            <button class="button button--quiet warning m-t-5 m-b-5"
                                    ng-click="deleteReceipt(receipt.id)">Delete</button>
                            <button class="button button--quiet primary m-t-5 m-b-5"
                                    ng-click="reviewReceipt(receipt.id);">Review</button>
                        </span>
                    </li>
                    <li class="list__header" ng-show="oldReceipts.length > 0">
                        Old Receipts
                    </li>
                    <li class="list__item" ng-show="oldReceipts.length > 0"
                        ng-repeat="receipt in oldReceipts">
                        <span class="spCol col2">{{ receipt.location }}</span>
                        <span class="spCol col4">{{ receipt.scan_date }}</span>
                        <span class="pull-right">
                            <ons-icon icon="fa-search" size="15px" fixed-width="false"
                                      ng-click="doSomething()"
                                      style="color:rgba(255,255,255,.54);">
                            </ons-icon>
                            <ons-icon icon="fa-list-alt" size="15px" fixed-width="false"
                                      class="m-l-10" ng-click="doSomething()"
                                      style="color:rgba(255,255,255,.54);">
                            </ons-icon>
                        </span>
                    </li>
                </ul>
            </ons-list>
            <p class="text-center" ng-show="receipts.oldReceipts.length<=0 && receipts.newReceipts.length<=0">
                No receipts have been uploaded yet
            </p>
            <br>
        </ons-page>
    </ons-template>


    <!-- ----------------------------------------- -->
    <!-- 4. REVIEW RECEIPT -->
    <!-- ----------------------------------------- -->
    <ons-template id="reviewReceipt.html">
        <ons-page ng-controller="ReviewReceiptController">
            <div class="loading" ng-class="{ hide : contentLoaded }"><ons-icon icon="fa-spinner" size="80px" spin="true"></div>
            <ons-toolbar>
                <div class="left"><ons-back-button ng-click="getAllReceipts()"></ons-back-button></div>
                <div class="center">Review</div>
            </ons-toolbar> 
            <ons-row ng-class="{ hide : !receipt }" class="m-t-10">
                <ons-col>
                    <ons-icon icon="fa-chevron-left" size="35px"
                              class="reviewProgress m-l-10" ng-click="changeReceipt('back')"
                              fixed-width="false"
                              style="float: left; padding-top: 10px; color: #889460; z-index: 1; font-size: 35px; opacity: {{ prevReceipts ? '1' : '.25'}}"></ons-icon>
                </ons-col>
                <ons-col>
                    <header class="text-center">
                        <span class="item-title">{{ reviewReceiptTitle }}</span>
                    </header>
                    <p class="item-desc text-center p-r-0">{{ receipt.description }}</p>
                </ons-col>

                <ons-col>
                    <ons-icon icon="fa-chevron-right" size="35px"
                              class="reviewProgress m-r-10" ng-click="changeReceipt('next')"
                              fixed-width="false"
                              style="float: right; padding-top: 10px; color: #889460; z-index: 1; font-size: 35px; opacity: {{ nextReceipts ? '1' : '.25'}}"></ons-icon>
                </ons-col>

            </ons-row>
            <ons-list class="m-t-10 no-receipts" ng-if="!receipt">
                All Receipts have been reviewed. What would you like to do now?
                <p><ons-button onclick="navi.pushPage('inventory.html')">Go To Recipes</ons-button></p>
                <p><ons-button onclick="navi.pushPage('inventory.html'')">Go To Inventory</ons-button></p>
                <p><ons-button onclick="navi.pushPage('')">Go Home</ons-button></p>
            </ons-list>
            <ons-list class="m-t-10" ng-class="{ deleted : receipt.deleted, saved : receipt.saved, fadeIn : receipt.resetItem, hide : !receipt }">
                <ons-list-item class="item">
                    <ons-row>
                        <ons-col>
                            <label>Freezing</label>
                            <ons-switch id="freezer" ng-checked="receipt.freezer"></ons-switch>
                        </ons-col>
                    </ons-row>
                    <ons-row>
                        <ons-col>
                            <div class="slider" ng-class="{'closed' : receipt.freezer}">
                                <label for="itemExpMo" ng-class="{'fade50' : receipt.freezer}">Expires: </label>
                                <div class="expDate" ng-class="{'fade50' : receipt.freezer}">
                                    {{ receipt.exp.month | monthToString }} {{ receipt.exp.day }},
                                    {{ receipt.exp.year }}</div>
                                <div class="clearfix"></div>
                                <label for="expMonth" class="text-right" ng-class="{'fade50' : receipt.freezer}">
                                    <em>Month</em>
                                </label>
                                <input id="expMonth" type="range" class="range" min="1" max="12"
                                       ng-model="receipt.exp.month" ng-disabled="receipt.freezer"
                                       value="{{ receipt.exp.month }}">
                                <div class="clearfix"></div>
                                <label for="expDay" class="text-right" ng-class="{'fade50' : receipt.freezer}">
                                    <em>Day</em>
                                </label>
                                <input id="expDay" type="range" class="range" min="1" max="31"
                                       ng-model="receipt.exp.day" ng-disabled="receipt.freezer"
                                       value="{{ receipt.exp.day }}">
                                <div class="clearfix"></div>
                                <label for="expYear" class="text-right" ng-class="{'fade50' : receipt.freezer}">
                                    <em>Year</em>
                                </label>
                                <input id="expYear" type="range" class="range" min="{{ currentYear }}"
                                       max="{{ currentYear +5 }}" ng-model="receipt.exp.year"
                                       ng-disabled="receipt.freezer" value="{{ receipt.exp.year }}">
                                <div class="clearfix"></div>
                            </div>
                        </ons-col>
                    </ons-row>
                </ons-list-item>
                <ons-list-item class="item">
                    <ons-row>
                        <ons-col>
                            <label for="itemQuantity">Quantity ({{ receipt.quantity }}): </label>
                            <input type="range" class="range" min="1" max="10"
                                   ng-model="receipt.quantity"
                                   value="{{ receipt.quantity }}">
                        </ons-col>
                    </ons-row>
                </ons-list-item>                
                <ons-list-item class="item">
                    <ons-row>
                        <ons-col>
                            <label for="itemUnits">Units: </label>
                            <div class="select-style">
                                <select>
                                    <option ng-repeat="unit in units"
                                            ng-selected="receipt.units == unit">{{ unit }}</option>
                                </select>
                            </div>
                        </ons-col>
                    </ons-row>
                </ons-list-item>                
                <ons-list-item class="item">
                    <ons-row>
                        <ons-col>
                            <button class="button pull-right button--outline" ng-disabled="processingItem" ng-click="saveItem(receipt)">
                                <ons-icon icon="fa-floppy-o" size="15px" ng-if="!processingItem"
                                          fixed-width="false"></ons-icon>
                                <ons-icon icon="fa-spinner fa-spin" size="15px" ng-if="processingItem" fixed-width="false"></ons-icon>
                                <span class="m-l-5">Save</span>
                            </button>
                            <button class="button button--quiet warning pull-left" ng-click="deleteItem(receipt.id)">
                                <ons-icon icon="fa-trash-o" size="15px" fixed-width="false" style=""></ons-icon>
                                <span class="m-r-5">Delete!</span>
                            </button>
                        </ons-col>
                    </ons-row>
                </ons-list-item>
            </ons-list>
        </ons-page>
    </ons-template>

    <!-- ----------------------------------------- -->
    <!-- 5. RECIPES -->
    <!-- ----------------------------------------- -->
    <ons-template id="recipes.html">
        <ons-page ng-controller="recipesController">
            <div class="loading" ng-class="{ hide : contentLoaded }"><ons-icon icon="fa-spinner" size="80px" spin="true"></ons-icon></div>
            <ons-toolbar>
                <div class="left"><ons-back-button></ons-back-button></div>
                <div class="center">Recipes</div>
            </ons-toolbar>


            <div class="button-bar" style="margin: 7px auto; padding: 0 10px;">
                <div class="button-bar__item" id="recipeTrending"
                     ng-click="switchRecipes('all')">
                    <input type="radio" name="navi-segment-a" checked>
                    <div class="button-bar__button">Trending Recipes</div>
                </div>

                <div class="button-bar__item" id="recipeLibrary"
                     ng-click="switchRecipes('saved')">
                    <input type="radio" name="navi-segment-a">
                    <div class="button-bar__button">Your Recipes</div>
                </div>
            </div>

            <ons-list class="timeline" modifier="inset">
                <div class="wrap">
                    <div class="box" ng-repeat="recipe in recipes" modifier="tappable" ng-click="getRecipeById(recipe.recipe_id)">
                        <div class="boxInner">
                            <img src="{{ recipe.image_url }}" />
                            <div class="titleBox">{{ recipe.title }}</div>
                        </div>
                    </div>
                </div>
                <button class="button--quiet pull-center" ng-click="getRecipes()">
                    <span ng-show="!loadingRecipes">Load More</span>
                    <ons-icon icon="fa-spinner fa-spin" size="15px" fixed-width="false" ng-show="loadingRecipes"></ons-icon>
                </button>
            </ons-list>
        </ons-page>
    </ons-template>



    <!-- ----------------------------------------- -->
    <!-- 6. RECIPE -->
    <!-- ----------------------------------------- -->
    <ons-template id="recipe.html">
        <ons-page ng-controller="recipeController">
            <div class="loading" ng-class="{ hide : contentLoaded }"><ons-icon icon="fa-spinner" size="80px" spin="true"></ons-icon></div>
            <ons-toolbar>
                <div class="left"><ons-back-button></ons-back-button></div>
                <div class="center">{{ recipe.title }}</div>
            </ons-toolbar>
            <ons-list modifier="inset" class="m-t-10" ng-show="!showFrame">
                <ul class="list">
                    <li class="list__header">
                       Ingredients
                    </li>
                    <li class="list__item" ng-repeat="ing in recipe.ingredients">{{ ing }}</li>
                </ul>
            </ons-list>
            <button class="button--quiet pull-center" ng-click="showFrame = true;" ng-show="!showFrame">View Complete Recipe</button>
            <span ng-show="showFrame">
                <iframe ng-src="{{ recipe_url }}" class="recipe_frame"></iframe>
            </span>
        </ons-page>
    </ons-template>



    <!-- ----------------------------------------- -->
    <!-- 7. ADD ITEM TO INVENTORY -->
    <!-- ----------------------------------------- -->
    <ons-template id="addItem.html">
        <ons-page ng-controller="AddItemController">
            <ons-toolbar>
                <div class="left"><ons-back-button ng-click="getInventory();"></ons-back-button></div>
                <div class="center">Add Food</div>
                <div class="right m-t-20 m-r-10 primary" ng-class="{ hide : !itemSaveSuccess, fadeInOut : itemSaveSuccess }">SAVED!</div>
            </ons-toolbar>
            <div class="loading" ng-class="{ hide : contentLoaded }"><ons-icon icon="fa-spinner" size="80px" spin="true"></ons-icon></div>
            <p class="text-center p-l-25 p-r-25">What type of food would you like to add to the inventory?</p>
            <ons-list modifier="inset" class="settings-list p-10" ng-class="{ hideCategory : hideCategory }">
                <ons-list-item ng-repeat="category in categories" ng-click="addCategory(category)"
                               class="addItemCategory" modifier="tappable">
                    <div class="{{ category }}"></div>
                    {{ category | categoryTitle }}
                </ons-list-item>
            </ons-list>
            <p class="text-center m-t-0 m-b-0">{{ categoryTitle }}</p>
            <ons-list modifier="inset" class="m-t-10" ng-class="{ fadeIn : hideCategory }">
                <ul class="list" ng-repeat="(alpha, item) in itemList">
                    <li class="list__header">{{ alpha }}</li>
                    <li class="list__item" ng-repeat="row in item">
                        <div ng-class="{ 'primary strong' : adjustItemValues === alpha + $index , fade50 : adjustItemValues !== undefined && adjustItemValues !== alpha + $index }"
                              ng-click="showItemValues(true, alpha, $index)">{{ row.description }}</div>
                        <div ng-class="{ hide : adjustItemValues !== alpha + $index }" class="addItemAdjust">

                            <!-- Edit Fields Start -->
                            <ons-list class="m-t-10 m-b-25">
                                <ons-list-item class="item">
                                    <ons-row>
                                        <ons-col>
                                            <label>Freezing<span class="hide">{{ row.freezer = row.freezer == '1' ? true : false; }}</span></label>
                                            <ons-switch id="freezer_{{ alpha + $index }}" ng-model="row.freezer"></ons-switch>
                                        </ons-col>
                                    </ons-row>
                                    <ons-row>
                                        <ons-col>
                                            <div class="slider" ng-class="{'closed' : row.freezer == '1'}">
                                                <label for="itemExpMo" ng-class="{'fade50' : row.freezer == '1'}">Expires: </label>
                                                <div class="expDate" ng-class="{'fade50' : row.freezer == '1'}">
                                                    {{ row.exp.month | monthToString }} {{ row.exp.day }},
                                                    {{ row.exp.year }}</div>
                                                <div class="clearfix"></div>
                                                <label for="expMonth" class="text-right" ng-class="{'fade50' : row.freezer == '1'}">
                                                    <em>Month</em>
                                                </label>
                                                <input id="expMonth" type="range" class="range" min="1" max="12"
                                                       ng-model="row.exp.month" ng-disabled="receipt.freezer == '1'"
                                                       value="{{ row.exp.month }}">
                                                <div class="clearfix"></div>
                                                <label for="expDay" class="text-right" ng-class="{'fade50' : row.freezer == '1'}">
                                                    <em>Day</em>
                                                </label>
                                                <input id="expDay" type="range" class="range" min="1" max="31"
                                                       ng-model="row.exp.day" ng-disabled="row.freezer == '1'"
                                                       value="{{ row.exp.day }}">
                                                <div class="clearfix"></div>
                                                <label for="expYear" class="text-right" ng-class="{'fade50' : row.freezer == '1'}">
                                                    <em>Year</em>
                                                </label>
                                                <input id="expYear" type="range" class="range" min="{{ currentYear }}"
                                                       max="{{ currentYear +5 }}" ng-model="row.exp.year"
                                                       ng-disabled="row.freezer == '1'" value="{{ row.exp.year }}">
                                                <div class="clearfix"></div>
                                            </div>
                                        </ons-col>
                                    </ons-row>
                                </ons-list-item>
                                <ons-list-item class="item">
                                    <ons-row>
                                        <ons-col>
                                            <label for="itemQuantity">Quantity ({{ row.quantity || 1 }}): </label>
                                            <input type="range" class="range" min="1" max="10" ng-model="row.quantity">
                                        </ons-col>
                                    </ons-row>
                                </ons-list-item>
                                <ons-list-item class="item">
                                    <ons-row>
                                        <ons-col>
                                            <label for="itemUnits">Units: </label>
                                            <div class="select-style">
                                                <select>
                                                    <option ng-repeat="unit in units"
                                                            ng-selected="row.units == unit">{{ unit }}</option>
                                                </select>
                                            </div>
                                        </ons-col>
                                    </ons-row>
                                </ons-list-item>
                                <ons-list-item class="item">
                                    <ons-row>
                                        <ons-col>
                                            <button class="button pull-right button--outline" ng-disabled="processingItem" ng-click="addItemToInventory(row, 'freezer_' + alpha + $index)">
                                                <ons-icon icon="fa-floppy-o" size="15px" ng-if="!processingItem" fixed-width="false"></ons-icon>
                                                <ons-icon icon="fa-spinner fa-spin" size="15px" ng-if="processingItem" fixed-width="false"></ons-icon>
                                                <span class="m-l-5">Save</span>
                                            </button>
                                            <button class="button button--quiet warning pull-left"
                                                    ng-click="showItemValues(false);">
                                                <ons-icon icon="fa-ban" size="15px" fixed-width="false" style=""></ons-icon>
                                                <span class="m-r-5">Cancel</span>
                                            </button>
                                        </ons-col>
                                    </ons-row>
                                </ons-list-item>
                            </ons-list>
                            <!-- Edit Fields Stop -->
                            
                        </div>
                    </li>
                </ul>
            </ons-list>
        </ons-page>
    </ons-template>


    <!-- ----------------------------------------- -->
    <!-- ??. GENERAL DETAIL -->
    <!-- ----------------------------------------- -->
    <ons-template id="detail.html">
        <ons-page ng-controller="DetailController">
            <ons-toolbar>
                <div class="left"><ons-back-button></ons-back-button></div>
                <div class="center">Details</div>
            </ons-toolbar>

            <ons-list modifier="inset" style="margin-top: 10px">
                <ons-list-item class="item">
                    <ons-row>
                        <ons-col width="80px">
                            <div class="item-thum"></div>
                        </ons-col>
                        <ons-col>
                            <header>
                                <span class="item-title">{{item.title}}</span>
                                <span class="item-label">{{item.label}}</span>
                            </header>
                            <p class="item-desc">{{item.desc}}</p>
                        </ons-col>
                    </ons-row>
                </ons-list-item>

                <ons-list-item modifier="chevron" ng-click="doSomething()">
                    <ons-icon icon="ion-chatboxes" fixed-width="true" style="color: #ccc"></ons-icon>
                    Add a note
                </ons-list-item>
            </ons-list>

            <ons-list style="margin-top: 10px">
                <ons-list-item class="item" ng-repeat="i in [1,2,3]">
                    <header>
                        <span class="item-title">Lorem ipsum dolor sit amet</span>
                    </header>
                    <p class="item-desc">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. <span class="typcn typcn-arrow-left"></span>
                    </p>
                </ons-list-item>
            </ons-list>

            <br>

        </ons-page>
    </ons-template>



<script type="text/javascript">
    <?php
         $loggedIn = 'var loggedIn = ';
         if (isset($_COOKIE['loggedIn'])) {
           if ($_COOKIE['loggedIn'] == true) {
             $loggedIn .= 'true';
           } else {
             $loggedIn .= 'false';
           }
         } else { $loggedIn .= 'false'; }
         
         echo $loggedIn;
         ?>
</script>    
</body>
</html>
