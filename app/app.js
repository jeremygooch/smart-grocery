var sg = angular.module('app', ['onsen']);

sg.factory('$data', function() {
    var data = {};

    data.items = [
        {
            title: 'Inventory',
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



// ////////////////////////////////////
// Filters
// ////////////////////////////////////
sg.filter('formatMonth', function() {
    return function(item) {
        switch(item) {
            case "1":
                return "January";
                break;
            case "2":
                return "February";
                break;
            case "3":
                return "March";
                break;
            case "4":
                return "April";
                break;
            case "5":
                return "May";
                break;
            case "6":
                return "June";
                break;
            case "7":
                return "July";
                break;
            case "8":
                return "August";
                break;
            case "9":
                return "September";
                break;
            case "10":
                return "October";
                break;
            case "11":
                return "November";
                break;
            case "12":
                return "December";
                break;
            default:
                return item;
                break;
        }
    };
});
