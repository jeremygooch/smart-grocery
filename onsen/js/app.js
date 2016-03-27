var sg = angular.module('app', ['onsen']);

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

