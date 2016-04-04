
var dropZone = document.getElementById("dropZone"),
    //send a picture to API with the username of user
    sendPicture = function (picture) {
        var form = new FormData();
        form.append("picture", picture);
        form.append("username", username);

        //sends a object with nessary data to XHR
        loadXHR({
            "method": "POST",
            "url": "pictures",
            "query": form,
            "data": "file",
            "load": profilePictureUpdate
        });
    },
    //read item dropped
    readItem = function (item) {

        //creates variable for later
        var div, p, button, img, fileReader, directoryReader, i,

        //gets element where image should go
        uploads = document.getElementById("uploads");

        //checks if item is file
        if (item.isFile) {

            //creates the element for dropped file
            div = document.createElement("div");
            img = document.createElement("img");
            p = document.createElement("p");
            p.innerHTML = item.name;
            div.appendChild(p);
            div.appendChild(img);
            uploads.appendChild(div);

            //get file
            item.file(function (file) {

                //checks if file is a image
                if (file.type.includes("image/") && !file.type.includes("/svg")) {
                    div.className = "aUpload";
                    //create button to set up upload
                    button = document.createElement("button");
                    button.innerHTML = "Upload This Picture";
                    div.appendChild(button);

                    //gets image
                    fileReader = new FileReader();
                    fileReader.readAsDataURL(file);
                    fileReader.onloadend = function (e) {
                        img.src = e.target.result;
                    };

                    button.addEventListener("click", function () {
                        sendPicture(file);
                    });
                }
                else {
                    div.className = "failedUpload";
                    img.src = "images/notComplete.svg";
                }
            });
        } else if (item.isDirectory) {
            //Get folder content
            directoryReader = item.createReader();
            directoryReader.readEntries(function (entries) {
                //loop through each directory file
                for (i = 0; i < entries.length; i++) {
                    readItem(entries[i]);
                }
            });
        }
    },
    //when a dragover starts
    dragOver = function (e) {
        //stop default events
        e.preventDefault();
        e.stopPropagation();
        //make dropzone visible
        dropZone.style.display = "block";
    },
    drop = function (e) {
        var items, i;
        //stop default events
        e.preventDefault();
        e.stopPropagation();
        //make dropzone invisible
        dropZone.style.display = "none";

        //gets the items (files/directories) dropped
        items = e.dataTransfer.items;

        //loop through each item (file/directory) dropped
        for (i = 0; i < items.length; i++) {
            //send a item (file/directory)
            readItem(items[i].webkitGetAsEntry());
        }
    },
    //this allows drag and drop to work, sets up all listeners needed
    dragNDrop = function () {
        //sets up listener for when a drag occurs
        window.addEventListener("dragover", dragOver);

        //sets up listener for when a drop happens
        window.addEventListener("drop", drop);

        //when user leaves the area, make drop zone invisible
        dropZone.addEventListener("dragleave", function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.display = "none";
        });
    };