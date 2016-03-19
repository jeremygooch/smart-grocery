var testData = {
    api:    'inventory',
    method: 'getLatestScan'
};

sg.makePostCall("api/index.php", testData)
    .success(function(data){
        console.log(data);
    })
    .fail(function(sender, message, details){
        console.log(sender);
        console.log(message);
        console.log(details);
        console.log("Sorry, something went wrong!");
    });
