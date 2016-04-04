//function for sending XHR requests
loadXHR = function (ob) {
    //start a XHR
    var xhr = new XMLHttpRequest();

    //open a XHR
    xhr.open(ob.method, "api/" + ob.url, true);

    //set request header for XHR
    xhr.setRequestHeader("Accept", "application/json");

    //checks if there is query to send to payload and checks its not sending a file
    if (ob.query && ob.data != "file") {
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    }

    //add listener for it
    xhr.addEventListener("load", function () {
        ob.load(JSON.parse(this.responseText));
    });

    //add listener for when XHR has a error
    xhr.addEventListener("error", function () {
        renderError(JSON.parse(this.responseText));
    });

    //send payload if any
    xhr.send(ob.query);
};