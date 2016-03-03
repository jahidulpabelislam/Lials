loadxhr = function(ob) {
	var i, xhr = new XMLHttpRequest();

	xhr.open(ob.method, ob.url, true);

	xhr.setRequestHeader("Accept", "application/json");

	if (ob.query && !ob.image) {
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	}

	for (i in ob.callbacks) {
		if (ob.callbacks.hasOwnProperty(i)) {
			xhr.addEventListener(i, ob.callbacks[i]);
		}
	}

	xhr.send(ob.query);
};