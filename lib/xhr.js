loadXHR = function (ob) {
    var i, xhr = new XMLHttpRequest();

    xhr.open(ob.method, "api/" + ob.url, true);

    xhr.setRequestHeader("Accept", "application/json");

    if (ob.query && ob.data != "image") {
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    }

    for (i in ob.callbacks) {
        if (ob.callbacks.hasOwnProperty(i)) {
            xhr.addEventListener(i, ob.callbacks[i]);
        }
    }
    xhr.addEventListener("error", errors);

    xhr.send(ob.query);
};