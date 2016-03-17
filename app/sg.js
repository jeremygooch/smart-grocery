window.sg = window.sg || {};
sg.makePostCall = function (url, data) { // here the data and url are not hardcoded anymore
    var json_data = JSON.stringify(data);

    return $.ajax({
        type: "POST",
        url: url,
        data: json_data,
        dataType: "json",
        contentType: "application/json;charset=utf-8"
    });
    // EXAMPLE   


}

