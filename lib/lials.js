//set up variables for later use
var username,
    userForm = document.getElementById("userform"),
    profile = document.getElementById("profile"),
    search = document.getElementById("search"),
    goals = document.getElementById("goals"),
    writeDiv = document.getElementById("writediv"),
    goalForm = document.getElementById("goalform"),
//set up for when AJAX goes wrong
    errors = function (response) {
        document.getElementById("errors").innerHTML = response;
    },
    signout = function () {
        document.getElementById("userformdiv").style.display = "block";
    },
    renderGoalUpdate = function (response) {
        var i, div, result = JSON.parse(response.target.responseText);
        removeForms();
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    div = document.getElementById("goal" + result.rows[i].ID);
                    div.querySelector(".goaltext").innerHTML = result.rows[i].Goal;
                }
            }
        }
    },
    renderGoalDelete = function (response) {
        var result = JSON.parse(response.target.responseText);
        removeForms();
        if (result.rows.GoalID) {
            document.getElementById("goal" + result.rows.GoalID).remove();
        }
    },
    renderCommentUpdate = function (response) {
        var i, div, result = JSON.parse(response.target.responseText);
        removeForms();
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    div = document.getElementById("comment" + result.rows[i].ID);
                    div.querySelector(".commenttext").innerHTML = result.rows[i].Comment;
                }
            }
        }
    },
    renderCommentDelete = function (response) {
        var result = JSON.parse(response.target.responseText);
        removeForms();
        if (result.rows.commentID) {
            document.getElementById("comment" + result.rows.commentID).remove();
        }
    },
    renderFollowUpdate = function (response) {
        var i, result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    document.querySelector("#follow").src = "images/following.svg";
                }
            }
        }
    },
    renderUnFollowUpdate = function (response) {
        var result = JSON.parse(response.target.responseText);
        if (result.rows) {
            document.querySelector("#follow").src = "images/notfollowing.svg";
        }
    },
    renderLikeUpdate = function (response) {
        var i, div, result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    div = document.getElementById("goal" + result.rows[i].GoalID);
                    div.querySelector(".like").src = "images/liked.svg";
                }
            }
        }
    },
    renderUnLikeUpdate = function (response) {
        var div, result = JSON.parse(response.target.responseText);
        if (result.rows.goalID) {
            div = document.getElementById("goal" + result.rows.goalID);
            div.querySelector(".like").src = "images/notliked.svg";
        }
    },
    //render the img
    renderCompletion = function (completion, img) {
        if (completion == 1) {
            img.src = "images/complete.svg";
            img.alt = "Goal Completed";
        }
        else if (completion == 0) {
            img.src = "images/notcomplete.svg";
            img.alt = "Goal Not Completed";
        }
    },
    //render the private img to display wether it's a private profile or not
    renderPrivate = function (private, img) {
        if (private == 1) {
            img.src = "images/private.svg";
        }
        else if (private == 0) {
            img.src = "images/notprivate.svg";
        }
    },
    //render the profile picture of a user
    renderProfilePicture = function (data, img) {
        if (data.Picture != null) {
            img.src = "api/" + data.Picture;

            if (data.Username == username) {
                var button = document.createElement("button");
                button.type = "button";
                button.id = "deleteprofilepicture";
                button.innerHTML = "Delete Profile Picture";
                profile.appendChild(button);

                button.addEventListener("click", function () {
                    loadXHR({
                        "method": "DELETE",
                        "url": "pictures?username=" + username,
                        "callbacks": {
                            "load": profilePictureUpdate,
                        }
                    });
                });
            }
        }
        else {
            img.src = "images/profilepicture.svg";
            if (document.getElementById("deleteprofilepicture")) {
                document.getElementById("deleteprofilepicture").remove();
            }
        }
    },
    completionUpdate = function (response) {
        var i, div, img, result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    div = document.getElementById("goal" + result.rows[i].ID);
                    img = div.querySelector(".completion");
                    renderCompletion(result.rows[i].Complete, img);
                }
            }
        }
    },
    privateUpdate = function (response) {
        var i, img, result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    img = document.querySelector("#private");
                    renderPrivate(result.rows[i].Private, img);
                }
            }
        }
    },
//for when user update their profile picture
    profilePictureUpdate = function (response) {
        var i, img, result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    img = document.querySelector("#profilepicture");
                    renderProfilePicture(result.rows[i], img);
                }
            }
        }
    },
    follow = function (user, img) {
        if (img.src.includes("not")) {
            loadXHR({
                "method": "POST",
                "url": "follows",
                "query": "me=" + username + "&user=" + user,
                "callbacks": {
                    "load": renderFollowUpdate,
                }
            });
        }
        else {
            loadXHR({
                "method": "DELETE",
                "url": "follows?me=" + username + "&user=" + user,
                "callbacks": {
                    "load": renderUnFollowUpdate,
                }
            });
        }
    },
    like = function (goalID, img) {
        if (img.src.includes("not")) {
            loadXHR({
                "method": "POST",
                "url": "likes",
                "query": "me=" + username + "&goalID=" + goalID,
                "callbacks": {
                    "load": renderLikeUpdate,
                }
            });
        }
        else {
            loadXHR({
                "method": "DELETE",
                "url": "likes?me=" + username + "&goalID=" + goalID,
                "callbacks": {
                    "load": renderUnLikeUpdate,
                }
            });
        }
    },
    comment = function (goalData, goalDiv) {
        var form, label, input, button, alreadyacomment = goalDiv.querySelector("#commentform");
        if (!alreadyacomment) {
            removeForms();
            form = document.createElement("form");
            form.id = "commentform";

            label = document.createElement("label");
            label.for = "comment";
            label.innerHTML = "Enter your Comment.";

            input = document.createElement("input");
            input.type = "text";
            input.name = "comment";
            input.placeholder = "Same I want to go USA!";
            input.autofocus = true;

            button = document.createElement("button");
            button.type = "button";
            button.innerHTML = "Upload Goal";

            form.appendChild(label);
            form.appendChild(input);
            form.appendChild(button);

            goalDiv.appendChild(form);

            button.addEventListener("click", function () {
                loadXHR({
                    "method": "POST",
                    "url": "comments",
                    "query": "goalID=" + goalData.ID + "&comment=" + input.value + "&me=" + username,
                    "callbacks": {
                        "load": renderComments,
                    }
                });
            });
        }
        else {
            alreadyacomment.remove();
        }
    },
    completion = function (goalID) {
        loadXHR({
            "method": "PATCH",
            "url": "goals/" + goalID + "?completion=true",
            "callbacks": {
                "load": completionUpdate,
            }
        });
    },
    deleteGoal = function (goalID) {
        loadXHR({
            "method": "DELETE",
            "url": "goals/" + goalID,
            "callbacks": {
                "load": renderGoalDelete,
            }
        });
    },
    editGoal = function (goalData, goalDiv) {
        var alreadyAEditGoalForm = goalDiv.querySelector("#editgoalform");
        if (!alreadyAEditGoalForm) {
            removeForms();
            var form = document.createElement("form");
            form.id = "editgoalform";

            var label = document.createElement("label");
            label.for = "goal";
            label.innerHTML = "Enter your updated Goal.";

            var input = document.createElement("input");
            input.type = "text";
            input.name = "goal";
            input.placeholder = "I want to go USA!";
            input.autofocus = true;
            input.value = goalData.Goal;

            var button = document.createElement("button");
            button.type = "button";
            button.innerHTML = "Update Goal";

            form.appendChild(label);
            form.appendChild(input);
            form.appendChild(button);

            goalDiv.appendChild(form);
            button.addEventListener("click", function () {
                loadXHR({
                    "method": "PATCH",
                    "url": "goals/" + goalData.ID + "?goal=" + input.value,
                    "callbacks": {
                        "load": renderGoalUpdate,
                    }
                });
            });
        }
        else {
            alreadyAEditGoalForm.remove();
        }
    },
    deleteComment = function (commentID) {
        loadXHR({
            "method": "DELETE",
            "url": "comments/" + commentID,
            "callbacks": {
                "load": renderCommentDelete,
            }
        });
    },
    editComment = function (comment, goalDiv) {
        var alreadyAEditCommentForm = goalDiv.querySelector("#editcommentform");
        if (!alreadyAEditCommentForm) {
            removeForms();
            var form = document.createElement("form");
            form.id = "editcommentform";

            var label = document.createElement("label");
            label.for = "goal";
            label.innerHTML = "Enter your updated Comment.";

            var input = document.createElement("input");
            input.type = "text";
            input.name = "goal";
            input.placeholder = "I want to go USA!";
            input.autofocus = true;
            input.value = comment.Comment;

            var button = document.createElement("button");
            button.type = "button";
            button.innerHTML = "Update Comment";

            form.appendChild(label);
            form.appendChild(input);
            form.appendChild(button);

            goalDiv.appendChild(form);
            button.addEventListener("click", function () {
                loadXHR({
                    "method": "PATCH",
                    "url": "comments/" + comment.ID + "?comment=" + input.value,
                    "callbacks": {
                        "load": renderCommentUpdate,
                    }
                });
            });
        }
        else {
            alreadyAEditCommentForm.remove();
        }
    },
    private = function () {
        loadXHR({
            "method": "PATCH",
            "url": "users/" + username,
            "callbacks": {
                "load": privateUpdate,
            }
        });
    },
    addFollowButton = function (data, goalImgDiv) {
        var img = document.createElement("img");
        if (data.Username1 == null) {
            img.src = "images/notfollowing.svg";
            img.alt = "Follow User";
        }
        else {
            img.src = "images/following.svg";
            img.alt = "Unfollow User";
        }
        img.addEventListener("click", function () {
            follow(data.Username, img);
        });
        img.id = "follow";
        goalImgDiv.appendChild(img);
    },
    addLikeButton = function (data, goalImgDiv) {
        var img = document.createElement("img");
        if (data.GoalID == null) {
            img.src = "images/notliked.svg";
            img.alt = "Like Goal";
        }
        else {
            img.src = "images/liked.svg";
            img.alt = "Unlike Goal";
        }
        img.addEventListener("click", function () {
            like(data.ID, img);
        });
        img.className = "like";
        goalImgDiv.appendChild(img);
    },
    addCommentButton = function (data, goalImgDiv) {
        var img = document.createElement("img");
        img.src = "images/comment.svg";
        img.alt = "Comment On Goal";
        goalImgDiv.appendChild(img);
        img.addEventListener("click", function () {
            comment(data, goalImgDiv.parentNode);
        });
    },
    addCompletionButton = function (data, goalImgDiv) {
        var img = document.createElement("img");
        renderCompletion(data.Complete, img);
        img.className = "completion";
        goalImgDiv.appendChild(img);
        if (data.Username == username) {
            img.addEventListener("click", function () {
                completion(data.ID);
            });
        }
    },
    addDeleteGoalButton = function (data, goalImgDiv) {
        var img = document.createElement("img");
        img.src = "images/delete.svg";
        img.alt = "Delete Goal";
        goalImgDiv.appendChild(img);
        img.addEventListener("click", function () {
            deleteGoal(data.ID);
        });
    },
    addEditGoalButton = function (data, goalImgDiv) {
        var img = document.createElement("img");
        img.src = "images/edit.svg";
        img.alt = "Edit Goal";
        goalImgDiv.appendChild(img);
        img.addEventListener("click", function () {
            editGoal(data, goalImgDiv.parentNode);
        });
    },
    addDeleteCommentButton = function (data, commentImgDiv) {
        var img = document.createElement("img");
        img.src = "images/delete.svg";
        img.alt = "Delete Goal";
        commentImgDiv.appendChild(img);
        img.addEventListener("click", function () {
            deleteComment(data.ID);
        });
    },
    addEditCommentButton = function (data, commentImgDiv) {
        var img = document.createElement("img");
        img.src = "images/edit.svg";
        img.alt = "Edit Goal";
        commentImgDiv.appendChild(img);
        img.addEventListener("click", function () {
            editComment(data, commentImgDiv.parentNode);
        });
    },
    addUsernameButton = function (data, goalDiv) {
        var p = document.createElement("p");
        p.innerHTML = data.Username;
        p.classList.add("username");
        goalDiv.appendChild(p);
        p.addEventListener("click", function () {
            getProfile(data.Username);
        });
    },
    addPrivateButton = function (data, profileImgDiv) {
        var img = document.createElement("img");
        renderPrivate(data.Private, img);
        img.id = "private";
        profileImgDiv.appendChild(img);
        if (data.Username == username) {
            img.addEventListener("click", private);
        }
    },
    addUploadText = function (data, goalDiv) {
        var p = document.createElement("p");
        p.className = "upload";
        p.innerHTML = data;
        goalDiv.appendChild(p);
    },
//remove forms if theres one
    removeForms = function () {
        if (document.getElementById("commentform")) {
            document.getElementById("commentform").remove();
        }
        if (document.getElementById("editcommentform")) {
            document.getElementById("editcommentform").remove();
        }
        if (document.getElementById("editgoalform")) {
            document.getElementById("editgoalform").remove();
        }
    },
//clear profile div for later
    removeProfileDiv = function () {
        profile.innerHTML = "";
        profile.style.display = "none";
    },
    renderProfile = function (response) {
        var p, div, img, img2, img;
        profile.innerHTML = "";
        var result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    p = document.createElement("p");
                    p.innerHTML = result.rows[i].Username;
                    p.className = "username";
                    profile.appendChild(p);

                    div = document.createElement("div");
                    div.className = "images";

                    img = document.createElement("img");
                    img.id = "profilepicture";
                    img.alt = "Profile";
                    renderProfilePicture(result.rows[i], img);
                    div.appendChild(img);

                    if (result.rows[i].Username == username) {
                        drag();

                        var button = document.createElement("button");
                        button.type = "button";
                        button.id = "signout";
                        button.innerHTML = "Sign Out";
                        profile.appendChild(button);

                        button.addEventListener("click", signout);
                    }
                    else {
                        addFollowButton(result.rows[i], div);
                    }

                    addPrivateButton(result.rows[i], div);

                    profile.appendChild(div);
                }
            }
        }
    },
    renderComments = function (response) {
        var i, div, div2, p1, div3;
        var result = JSON.parse(response.target.responseText);
        removeForms();
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    div = document.getElementById("goal" + result.rows[i].GoalID);

                    div2 = document.createElement("div");
                    div2.className = "comment";
                    div2.id = "comment" + result.rows[i].ID;

                    p1 = document.createElement("p");
                    p1.innerHTML = result.rows[i].Comment;
                    p1.className = "commenttext";
                    div2.appendChild(p1);

                    addUsernameButton(result.rows[i], div2);

                    div3 = document.createElement("div");
                    div3.className = "images";

                    if (result.rows[i].Username == username) {
                        addEditCommentButton(result.rows[i], div3);

                        addDeleteCommentButton(result.rows[i], div3);
                    }
                    div2.appendChild(div3);

                    addUploadText(result.rows[i].Upload, div2);

                    div.appendChild(div2);
                }
            }
        }
    },
    renderGoals = function (response) {
        var i, div, p, p2, div2;
        var result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    if (document.getElementById("nogoals")) {
                        document.getElementById("nogoals").remove();
                    }
                    div = document.createElement("div");
                    div.id = "goal" + result.rows[i].ID;
                    div.className = "goal";

                    p = document.createElement("p");
                    p.innerHTML = result.rows[i].Goal;
                    p.className = "goaltext";
                    div.appendChild(p);

                    p2 = document.createElement("p");
                    p2.innerHTML = result.rows[i].Due;
                    div.appendChild(p2);

                    addUsernameButton(result.rows[i], div);

                    div2 = document.createElement("div");
                    div2.className = "images";

                    addCommentButton(result.rows[i], div2);

                    addCompletionButton(result.rows[i], div2);

                    if (result.rows[i].Username != username) {
                        addLikeButton(result.rows[i], div2);
                    }
                    else {
                        addEditGoalButton(result.rows[i], div2);

                        addDeleteGoalButton(result.rows[i], div2);
                    }

                    div.appendChild(div2);

                    addUploadText(result.rows[i].Upload, div);

                    loadXHR({
                        "method": "GET",
                        "url": "comments?goalID=" + result.rows[i].ID,
                        "callbacks": {
                            "load": renderComments,
                        }
                    });

                    goals.insertBefore(div, goals.firstChild);
                }
            }
        }
        else {
            p = document.createElement("p");
            p.id = "nogoals";
            p.innerHTML = "Sorry, no Goals to show!";
            goals.appendChild(p);
        }
    },
    getGoals = function (query) {
        loadXHR({
            "method": "GET",
            "url": "goals?" + query,
            "callbacks": {
                "load": renderGoals,
            }
        });
    },
    getHomeGoals = function () {
        search.value = "";
        goals.innerHTML = "";
        writeDiv.style.display = "block";
        removeProfileDiv();
        getGoals("me=" + username);
    },
    doSearch = function () {
        var searchValue = search.value;
        removeProfileDiv();
        if (searchValue.trim() == "") {
            getHomeGoals();
            writeDiv.style.display = "block";
        }
        else {
            writeDiv.style.display = "none";
            goals.innerHTML = "";
            getGoals("me=" + username + "&search=" + searchValue);
        }
    },
    getProfile = function (Username) {
        search.value = "";
        profile.style.display = "block";
        writeDiv.style.display = "none";
        goals.innerHTML = "";
        getGoals("user=" + Username + "&me=" + username);
        loadXHR({
            "method": "GET",
            "url": "users/" + Username + "?me=" + username,
            "callbacks": {
                "load": renderProfile,
            }
        });
    },
    postGoal = function () {
        loadXHR({
            "method": "POST",
            "url": "goals/",
            "query": "goal=" + goalForm.goal.value + "&due=" + goalForm.due.value + "&me=" + username,
            "callbacks": {
                "load": renderGoals,
            }
        });
        goalForm.due.value = "";
        goalForm.goal.value = "";
    },
    //for when user either logs in for signs up
    loggedIn = function (response) {
        var i, logos,
            result = JSON.parse(response.target.responseText);
        if (result.rows && result.rows.length > 0) {
            for (i = 0; i < result.rows.length; i++) {
                if (result.rows.hasOwnProperty(i)) {
                    username = result.rows[i].Username;
                    document.getElementById("userformdiv").style.display = "none";
                    search.addEventListener("input", doSearch);
                    document.getElementById("postgoal").addEventListener("click", postGoal);
                    document.getElementById("profileimg").addEventListener("click", function () {
                        getProfile(username);
                    });
                    logos = document.querySelectorAll(".logo");
                    for (i = 0; i < logos.length; i++) {
                        logos[i].addEventListener("click", function () {
                            getHomeGoals();
                        });
                    }
                    getHomeGoals();
                }
            }
        }
        else {
            document.getElementById("feedback").innerHTML = result.meta.feedback;
        }
    },
    //post a new user
    signUp = function () {
        loadXHR({
            "method": "POST",
            "url": "users/",
            "query": "username=" + userForm.username.value + "&password=" + userForm.password.value,
            "callbacks": {
                "load": loggedIn,
            }
        });
    },
//sign in/get a user
    logIn = function () {
        loadXHR({
            "method": "GET",
            "url": "users/" + userForm.username.value + "?password=" + userForm.password.value,
            "callbacks": {
                "load": loggedIn,
            }
        });
    };

//set up login and sign up buttons
document.getElementById("signupbutton").addEventListener("click", signUp);
document.getElementById("loginbutton").addEventListener("click", logIn);