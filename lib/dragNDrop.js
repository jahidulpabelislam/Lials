window.lials = window.lials || {};
window.lials.dragNDrop = (function () {

    "use strict";

    //get the dropzone
    var dropZone = document.getElementById("dropZone"),
        //send a picture to API with the username of user
        sendPicture = function (picture) {
            var form = new FormData();
            //add the picture
            form.append("picture", picture);
            //add the username of user
            form.append("username", window.lials.main.usersUsername);

            //sends a object with necessary data to XHR
            window.lials.xhr.load({
                "method": "POST",
                "url": "pictures",
                "query": form,
                "data": "file",
                "load": window.lials.main.profilePictureUpdate
            });
        },
        //set im failed upload div to display error
        failedUpload = function (div, img) {
            div.className = "failedUpload";
            img.src = "images/notComplete.svg";
        },
        //read item dropped
        readItem = function (item) {

            //creates variable for later
            var div, p, button, img, fileReader, directoryReader, i,

                //gets element where image should go
                uploads = document.getElementById("uploads");

            //creates the element for dropped file
            div = document.createElement("div");
            img = document.createElement("img");
            p = document.createElement("p");
            div.appendChild(p);
            div.appendChild(img);
            uploads.appendChild(div);

            //checks if item is file
            if (item.isFile) {
                //shows the file name
                p.innerHTML = item.name;

                //gets file
                item.file(function (file) {

                    //checks if file is a image
                    if (file.type.includes("image/")) {
                        div.className = "aUpload";
                        //create button to set up upload
                        button = document.createElement("button");
                        button.innerHTML = "Upload This Picture";
                        div.appendChild(button);

                        //gets image
                        fileReader = new FileReader();
                        fileReader.readAsDataURL(file);
                        fileReader.onload = function (e) {
                            img.src = e.target.result;
                        };
                        fileReader.onerror = function () {
                            failedUpload(div, img);
                            p.innerHTML = "Error getting image - " + p;
                        };

                        //set up listener for when user wants to upload a picture
                        button.addEventListener("click", function () {
                            sendPicture(file);
                        });
                    }
                    //else it isn't a image so show its failed
                    else {
                        failedUpload(div, img);
                        p.innerHTML += " isn't a image";
                    }
                });
            }
            //checks if its a directory
            else if (item.isDirectory) {
                //Get folder content
                directoryReader = item.createReader();
                directoryReader.readEntries(function (entries) {
                    //loop through each directory item
                    for (i = 0; i < entries.length; i++) {
                        readItem(entries[i]);
                    }
                });
            }
            //else drop of item has failed therefore show its failed
            else {
                failedUpload(div, img);
                p.innerHTML = "Error processing upload - " + item.name;
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
        //when drop of item (file/directory) has occurred
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
                //send a item (file/directory) to be read
                readItem(items[i].webkitGetAsEntry());
            }
        },
        //stop drag and drop to work
        stopDragNDrop = function (target) {
            target.removeEventListener("dragover", dragOver);
            target.removeEventListener("drop", drop);
        },
        //this allows drag and drop to work, sets up all listeners needed
        setUp = function (target) {
            //sets up listener for when a drag occurs
            target.addEventListener("dragover", dragOver);

            //sets up listener for when a drop happens
            target.addEventListener("drop", drop);

            //when user leaves the area, make drop zone invisible
            dropZone.addEventListener("dragleave", function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropZone.style.display = "none";
            });
        };

    return {
        "setup": setUp,
        "stop": stopDragNDrop
    };

}());