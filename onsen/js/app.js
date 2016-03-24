(function(){
  'use strict';
  var module = angular.module('app', ['onsen']);

  module.controller('AppController', function($scope, $data) {
    $scope.doSomething = function() {
      setTimeout(function() {
        ons.notification.alert({ message: 'tapped' });
      }, 100);
    };
  });

  module.controller('DetailController', function($scope, $data) {
    $scope.item = $data.selectedItem;
  });

  module.controller('MasterController', function($scope, $data) {
    $scope.items = $data.items;

    $scope.showDetail = function(index) {
      var selectedItem = $data.items[index];
      $data.selectedItem = selectedItem;
      $scope.navi.pushPage('detail.html', {title : selectedItem.title});
    };
  });

  module.factory('$data', function() {
      var data = {};

      data.items = [
          {
              title: 'Inventory',
              notify: '2',
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

