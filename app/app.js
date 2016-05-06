var sg = angular.module('app', ['onsen']);

sg.factory('api', function($http) {
    return {
        query: function(type, path, data) {
            return $http({
                method: type,
                url: path,
                data: data,
                async: false,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
        }
    };
});

sg.factory('$data', function() {
    var data = {};

    data.items = [
        {
            title: 'Inventory',
        },
        {
            title: 'Recipes',
        },
        {
            title: 'Receipts',
        },
        {
            title: 'Grocery List',
        }
    ];

    return data;
});



// ////////////////////////////////////
// Filters
// ////////////////////////////////////
sg.filter('monthToString', function() {
    return function(item) {
        // Convert item to numeric to account for changes the slider might apply
        item = item * 1;
        switch(item) {
            case 1:
                return "January";
                break;
            case 2:
                return "February";
                break;
            case 3:
                return "March";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "May";
                break;
            case 6:
                return "June";
                break;
            case 7:
                return "July";
                break;
            case 8:
                return "August";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "October";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "December";
                break;
            default:
                return item;
                break;
        }
    };
});
