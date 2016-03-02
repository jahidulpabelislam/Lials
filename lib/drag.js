var sendPicture = function (picture) {
	form = new FormData();
	form.append("picture", picture);
	form.append("username", username);

	loadxhr({
	    "method": "POST",
	    "url": "api/pictures",
	    "query": form,
	    "image": true,
	    "callbacks": {
	  	    "load": renderProfilePicture,
	        "error": errors
	    }
	});
	document.getElementById("uploads").innerHTML = "";
},
readFile = function (file) {
	if (file.type.includes("image")) {
		var div, button, buttontext, img, fileReader;
		var uploads = document.getElementById("uploads");
		div = document.createElement("div");
		button = document.createElement("button");
		buttontext = document.createTextNode("Upload This Picture");
		button.appendChild(buttontext);
		div.appendChild(button);	
		img = document.createElement("img");
									
		fileReader = new FileReader();
		fileReader.readAsDataURL(file);
		fileReader.onloadend = function(e) {
			img.src = e.target.result;
		}
		div.appendChild(img);
		uploads.appendChild(div);

		button.addEventListener("click", function() { 
			sendPicture(file);
		});
	}
	
},
drag = function () {
	window.addEventListener("dragover", function(e) { 
		e.preventDefault(); 
		document.getElementById("dropzone").style.display = "block";
	});

	window.addEventListener( "drop", function(e) {
		var files, form;
		e.preventDefault();
		document.getElementById("dropzone").style.display = "none";
		
		files = e.dataTransfer.files;
					
		for (i = 0; i < files.length; i++) {
			readFile(files[i]);
	    }
	});
	//to do: when user leaves the area
	/*window.addEventListener( "dragleave", function(e) {
		e.preventDefault();
		document.getElementById("dropzone").style.display = "none";
	});*/
};