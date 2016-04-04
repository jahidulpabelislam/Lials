//set up variables for later use
var username,
    //grab element for later use
    userForm = document.getElementById("userForm"),
    userFormDiv = document.getElementById("userFormDiv"),
    profile = document.getElementById("profile"),
    search = document.getElementById("search"),
    goals = document.getElementById("goals"),
    goalFormDiv = document.getElementById("goalFormDiv"),
    goalForm = document.getElementById("goalForm"),
    //set up for when AJAX goes wrong
    renderError = function (result) {
        var error = document.getElementById("errors"),
            errorMessage = document.createElement("p");
        errorMessage.innerHTML = result;

        var button = document.createElement("button");
        button.type = "button";
        button.className = "deleteError";
        button.innerHTML = "X";
        errorMessage.appendChild(button);

        button.addEventListener("click", function () {
            errorMessage.remove();
        });

        error.appendChild(errorMessage);
    },
    checkFeedback = function (feedback, genericMessage) {
        //if there is feedback from PHP give error message using the feedback
        if (feedback) {
            renderError(feedback);
            //else if generic error message is provided give it to user
        } else if (genericMessage !== undefined) {
            renderError(genericMessage)
        }
    },
    //loop through data to see if it exists
    loopThroughData = function (data, toRun, genericMessage) {
        var i;
        if (data.rows && data.rows.length > 0) {
            for (i = 0; i < data.rows.length; i++) {
                if (data.rows.hasOwnProperty(i)) {
                    //run the function provided as data exists and is valid
                    toRun(data.rows[i]);
                }
            }
        } else {
            checkFeedback(data.meta.feedback, genericMessage);
            return false;
        }
    },
    //when user clicks on sign out button
    signOut = function () {
        userFormDiv.style.display = "block";
    },
    //remove forms if there's one
    removeForms = function () {
        if (document.getElementById("commentForm")) {
            document.getElementById("commentForm").remove();
        }
        if (document.getElementById("editCommentForm")) {
            document.getElementById("editCommentForm").remove();
        }
        if (document.getElementById("editGoalForm")) {
            document.getElementById("editGoalForm").remove();
        }
    },
    //render the goal with updated goal
    renderGoalUpdate = function (goal) {
        var div;
        removeForms();
        //finds the goal
        div = document.getElementById("goal" + goal.ID);
        //finds the text of a goal and updates it
        div.querySelector(".text").innerHTML = goal.Goal;
    },
    gotGoalUpdate = function (result) {
        loopThroughData(result, renderGoalUpdate, "Error Updating your Goal.");
    },
    //remove a goal that's been deleted
    renderGoalDelete = function (result) {
        if (result.rows && result.rows.GoalID) {
            //finds the goal and removes it
            document.getElementById("goal" + result.rows.GoalID).remove();
        } else {
            checkFeedback(result.meta.feedback, "Error Deleting your Goal.");
        }
    },
    //render comment with update
    renderCommentUpdate = function (comment) {
        var div;
        removeForms();
        div = document.getElementById("comment" + comment.ID);
        div.querySelector(".text").innerHTML = comment.Comment;
    },
    gotCommentUpdate = function (result) {
        loopThroughData(result, renderCommentUpdate, "Error Updating your Comment on Goal.");
    },
    //remove a deleted comment
    renderCommentDelete = function (result) {
        if (result.rows && result.rows.commentID) {
            document.getElementById("comment" + result.rows.commentID).remove();
        } else {
            checkFeedback(result.meta.feedback, "Error deleting your comment.");
        }
    },
    renderFollowUpdate = function () {
        document.querySelector("#follow").src = "images/following.svg";
    },
    gotFollowUpdate = function (result) {
        loopThroughData(result, renderFollowUpdate, "Error Following a User.");
    },
    renderUnFollowUpdate = function (result) {
        if (result.rows) {
            document.querySelector("#follow").src = "images/notFollowing.svg";
        } else {
            checkFeedback(result.meta.feedback, "Error Unfollowing a user.");
        }
    },
    renderLikeUpdate = function (like) {
        var div = document.getElementById("goal" + like.GoalID);
        div.querySelector(".like").src = "images/liked.svg";
    },
    gotLikeUpdate = function (result) {
        loopThroughData(result, renderLikeUpdate, "Error Liking a Goal.");
    },
    renderUnLikeUpdate = function (result) {
        var div;
        if (result.rows && result.rows.goalID) {
            div = document.getElementById("goal" + result.rows.goalID);
            div.querySelector(".like").src = "images/notLiked.svg";
        } else {
            checkFeedback(result.meta.feedback, "Error unliking a goal.");
        }
    },
    //render the completion image
    renderCompletion = function (goal) {
        var div = document.getElementById("goal" + goal.ID);
        var img = div.querySelector(".completion");
        if (goal.Completion == 1) {
            img.src = "images/complete.svg";
            img.alt = "Goal Completed";
        } else if (goal.Completion == 0) {
            img.src = "images/notComplete.svg";
            img.alt = "Goal Not Completed";
        }
    },
    //render the private img to display whether it's a private profile or not
    renderPrivate = function (user) {
        var img = document.querySelector("#private");
        if (user.Private == 1) {
            img.src = "images/private.svg";
        } else if (user.Private == 0) {
            img.src = "images/notPrivate.svg";
        }
    },
    //render the profile picture of a user
    renderProfilePicture = function (data) {
        var button, buttons,
            img = document.querySelector("#profilePicture");
        document.getElementById("uploads").innerHTML = "";
        if (document.getElementById("deleteProfilePicture")) {
            document.getElementById("deleteProfilePicture").remove();
        }
        if (data.Picture != null) {
            img.src = "api/" + data.Picture;

            if (data.Username == username) {
                button = document.createElement("button");
                button.type = "button";
                button.id = "deleteProfilePicture";
                button.innerHTML = "Delete Profile Picture";
                buttons = document.getElementById("buttons");
                buttons.appendChild(button);
                profile.appendChild(buttons);

                button.addEventListener("click", function () {
                    loadXHR({
                        "method": "DELETE",
                        "url": "pictures?username=" + username,
                        "load": profilePictureUpdate
                    });
                });
            }
        } else {
            img.src = "images/profilePicture.svg";
        }
    },
    completionUpdate = function (result) {
        loopThroughData(result, renderCompletion, "Error Changing your Goal Completion.");
    },
    privateUpdate = function (result) {
        loopThroughData(result, renderPrivate, "Error Changing your Private Attribute.");
    },
    //for when user update their profile picture
    profilePictureUpdate = function (result) {
        loopThroughData(result, renderProfilePicture, "Error Changing your Profile Picture.");
    },
    follow = function (user, img) {
        if (img.src.includes("not")) {
            loadXHR({
                "method": "POST",
                "url": "follows",
                "query": "me=" + username + "&user=" + user,
                "load": gotFollowUpdate
            });
        } else {
            loadXHR({
                "method": "DELETE",
                "url": "follows?me=" + username + "&user=" + user,
                "load": renderUnFollowUpdate
            });
        }
    },
    like = function (goalID, img) {
        if (img.src.includes("not")) {
            loadXHR({
                "method": "POST",
                "url": "likes",
                "query": "me=" + username + "&goalID=" + goalID,
                "load": gotLikeUpdate
            });
        } else {
            loadXHR({
                "method": "DELETE",
                "url": "likes?me=" + username + "&goalID=" + goalID,
                "load": renderUnLikeUpdate
            });
        }
    },
    comment = function (goalData, goalDiv) {
        var form, label, input, button,
            alreadyAComment = goalDiv.querySelector("#commentForm");
        if (!alreadyAComment) {
            removeForms();
            form = document.createElement("div");
            form.id = "commentForm";

            label = document.createElement("label");
            label.innerHTML = "Enter your Comment.";
            form.appendChild(label);

            input = document.createElement("input");
            input.placeholder = "Same I want to go USA!";
            form.appendChild(input);

            button = document.createElement("button");

            button.innerHTML = "Upload Comment";
            form.appendChild(button);

            goalDiv.appendChild(form);

            button.addEventListener("click", function () {
                loadXHR({
                    "method": "POST",
                    "url": "comments",
                    "query": "goalID=" + goalData.ID + "&comment=" + input.value + "&me=" + username,
                    "load": addedComment
                });
            });
        } else {
            alreadyAComment.remove();
        }
    },
    editGoal = function (goalData, goalDiv) {
        var alreadyAEditGoalForm = goalDiv.querySelector("#editGoalForm");
        if (!alreadyAEditGoalForm) {
            removeForms();
            var form = document.createElement("div");
            form.id = "editGoalForm";

            var label = document.createElement("label");
            label.innerHTML = "Enter your updated Goal.";

            var input = document.createElement("input");
            input.placeholder = "I want to go USA!";
            input.value = goalData.Goal;

            var button = document.createElement("button");
            button.innerHTML = "Update Goal";

            form.appendChild(label);
            form.appendChild(input);
            form.appendChild(button);

            goalDiv.appendChild(form);
            button.addEventListener("click", function () {
                loadXHR({
                    "method": "PATCH",
                    "url": "goals/" + goalData.ID + "?goal=" + input.value,
                    "load": gotGoalUpdate
                });
            });
        } else {
            alreadyAEditGoalForm.remove();
        }
    },
    editComment = function (comment, goalDiv) {
        var alreadyAEditCommentForm = goalDiv.querySelector("#editCommentForm");
        if (!alreadyAEditCommentForm) {
            removeForms();
            var form = document.createElement("div");
            form.id = "editCommentForm";

            var label = document.createElement("label");
            label.innerHTML = "Enter your updated Comment.";

            var input = document.createElement("input");
            input.placeholder = "I want to go USA!";
            input.value = comment.Comment;

            var button = document.createElement("button");
            button.innerHTML = "Update Comment";

            form.appendChild(label);
            form.appendChild(input);
            form.appendChild(button);

            goalDiv.appendChild(form);
            button.addEventListener("click", function () {
                loadXHR({
                    "method": "PATCH",
                    "url": "comments/" + comment.ID + "?comment=" + input.value,
                    "load": gotCommentUpdate
                });
            });
        } else {
            alreadyAEditCommentForm.remove();
        }
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
    //add the upload date text
    addUploadText = function (data, goalDiv) {
        var p = document.createElement("p");
        p.className = "upload";
        p.innerHTML = data;
        goalDiv.appendChild(p);
    },
    //clear profile div for later
    removeProfileDiv = function () {
        profile.innerHTML = "";
        profile.style.display = "none";
    },
    renderProfile = function (user) {
        var p, div, img, img2, img3, div2, button;
        profile.innerHTML = "";

        img = document.createElement("img");
        img.id = "profilePicture";
        img.alt = "Profile Picture of " + user.Username;
        profile.appendChild(img);

        p = document.createElement("p");
        p.innerHTML = user.Username;
        p.className = "username";
        profile.appendChild(p);

        div = document.createElement("div");
        div.className = "images";

        profile.appendChild(div);

        img2 = document.createElement("img");
        img2.id = "private";
        div.appendChild(img2);
        renderPrivate(user);

        if (user.Username == username) {
            dragNDrop();

            img2.addEventListener("click", function () {
                loadXHR({
                    "method": "PATCH",
                    "url": "users/" + username,
                    "load": privateUpdate
                })
            });

            div2 = document.createElement("div");
            div2.id = "buttons";

            button = document.createElement("button");
            button.type = "button";
            button.innerHTML = "Sign Out";
            div2.appendChild(button);

            button.addEventListener("click", signOut);
            profile.appendChild(div2);
        } else {
            img3 = document.createElement("img");
            img3.id = "follow";
            if (user.Username1 == null) {
                img3.src = "images/notFollowing.svg";
                img3.alt = "Follow User";
            } else {
                img3.src = "images/following.svg";
                img3.alt = "Unfollow User";
            }
            img3.addEventListener("click", function () {
                follow(user.Username, img3);
            });
            div.appendChild(img3);
        }
        renderProfilePicture(user, img);
    },
    gotProfile = function (result) {
        loopThroughData(result, renderProfile, "Error getting profile.");
    },
    renderComment = function (comment) {
        var div, div2, p1, div3, img, img2;
        removeForms();
        div = document.getElementById("goal" + comment.GoalID);

        div2 = document.createElement("div");
        div2.className = "comment";
        div2.id = "comment" + comment.ID;

        p1 = document.createElement("p");
        p1.innerHTML = comment.Comment;
        p1.className = "text";
        div2.appendChild(p1);

        addUsernameButton(comment, div2);

        div3 = document.createElement("div");
        div3.className = "images";

        if (comment.Username == username) {
            img = document.createElement("img");
            img.src = "images/edit.svg";
            img.alt = "Edit Goal";
            div3.appendChild(img);
            img.addEventListener("click", function () {
                editComment(comment, div2);
            });

            img2 = document.createElement("img");
            img2.src = "images/delete.svg";
            img2.alt = "Delete Goal";
            div3.appendChild(img2);
            img2.addEventListener("click", function () {
                loadXHR({
                    "method": "DELETE",
                    "url": "comments/" + comment.ID,
                    "load": renderCommentDelete
                });
            });
        }
        div2.appendChild(div3);

        addUploadText(comment.Upload, div2);

        div.appendChild(div2);
    },
    addedComment = function (result) {
        loopThroughData(result, renderComment, "Error processing your comment.");
    },
    gotComments = function (result) {
        loopThroughData(result, renderComment);
    },
    renderGoal = function (goal) {
        var div, p, p2, div2, img, img2, img3, img4, img5;
        //resets the goal form
        goalForm.goal.value = "";
        goalForm.due.value = "";
        if (document.getElementById("noGoals")) {
            document.getElementById("noGoals").remove();
        }
        div = document.createElement("div");
        div.id = "goal" + goal.ID;
        div.className = "goal";

        goals.insertBefore(div, goals.firstChild);

        p = document.createElement("p");
        p.innerHTML = goal.Goal;
        p.className = "text";
        div.appendChild(p);

        p2 = document.createElement("p");
        p2.innerHTML = goal.Due;
        div.appendChild(p2);

        addUsernameButton(goal, div);

        div2 = document.createElement("div");
        div2.className = "images";

        div.appendChild(div2);

        img4 = document.createElement("img");
        img4.src = "images/comment.svg";
        img4.alt = "Comment On Goal";
        div2.appendChild(img4);
        img4.addEventListener("click", function () {
            comment(goal, div);
        });

        img3 = document.createElement("img");
        img3.className = "completion";
        div2.appendChild(img3);
        renderCompletion(goal);

        if (goal.Username != username) {
            img5 = document.createElement("img");
            if (goal.GoalID == null) {
                img5.src = "images/notLiked.svg";
                img5.alt = "Like Goal";
            } else {
                img5.src = "images/liked.svg";
                img5.alt = "Unlike Goal";
            }
            img5.addEventListener("click", function () {
                like(goal.ID, img5);
            });
            img5.className = "like";
            div2.appendChild(img5);
        } else {
            img3.addEventListener("click", function () {
                loadXHR({
                    "method": "PATCH",
                    "url": "goals/" + goal.ID + "?completion=true",
                    "load": completionUpdate
                });
            });

            img = document.createElement("img");
            img.src = "images/edit.svg";
            img.alt = "Edit Goal";
            div2.appendChild(img);
            img.addEventListener("click", function () {
                editGoal(goal, div);
            });

            img2 = document.createElement("img");
            img2.src = "images/delete.svg";
            img2.alt = "Delete Goal";
            div2.appendChild(img2);
            img2.addEventListener("click", function () {
                loadXHR({
                    "method": "DELETE",
                    "url": "goals/" + goal.ID,
                    "load": renderGoalDelete
                });
            });
        }

        addUploadText(goal.Upload, div);

        //get comments for the goal
        loadXHR({
            "method": "GET",
            "url": "comments?goalID=" + goal.ID,
            "load": gotComments
        });
    },
    gotGoals = function (result) {
        var noGoalsDiv, noGoalsText,
            dataExists = loopThroughData(result, renderGoal);
        if (dataExists == false) {
            if (!result.meta.feedback) {
                noGoalsDiv = document.createElement("div");
                noGoalsDiv.id = "noGoals";
                noGoalsText = document.createElement("p");
                noGoalsText.innerHTML = "Sorry, no Goals to show!";
                noGoalsDiv.appendChild(noGoalsText);
                goals.appendChild(noGoalsDiv);
            }
        }
    },
    goalAdded = function (result) {
        loopThroughData(result, renderGoal, "Error processing your goal.");
    },
    getGoals = function (query) {
        goals.innerHTML = "";
        document.getElementById("uploads").innerHTML = "";
        loadXHR({
            "method": "GET",
            "url": "goals?" + query,
            "load": gotGoals
        });
    },
    getHomeGoals = function () {
        window.removeEventListener("dragover", dragOver);
        window.removeEventListener("drop", drop);
        search.value = "";
        goalFormDiv.style.display = "block";
        removeProfileDiv();
        getGoals("me=" + username);
    },
    //when user inputs something into search
    doSearch = function () {
        var searchValue = search.value;
        removeProfileDiv();
        if (searchValue.trim() == "") {
            getHomeGoals();
        } else {
            window.removeEventListener("dragover", dragOver);
            window.removeEventListener("drop", drop);
            goalFormDiv.style.display = "none";
            getGoals("me=" + username + "&search=" + searchValue);
        }
    },
    //when user clicks on a profile
    getProfile = function (Username) {
        search.value = "";
        goalFormDiv.style.display = "none";
        profile.style.display = "block";
        //gets the goals of user
        getGoals("user=" + Username + "&me=" + username);
        //send to XHR a object with the necessary data
        loadXHR({
            "method": "GET",
            "url": "users/" + Username + "?me=" + username,
            "load": gotProfile
        });
    },
    //when user clicks to send goal
    postGoal = function () {
        document.getElementById("goalFormFeedback").innerHTML = "";
        //check if input boxes are not empty
        if (goalForm.goal.value.trim() != "" && goalForm.due.value.trim() != "") {
            //do a regular expression to check if due data provided is valid
            var validDatePattern = /\b[\d]{4}-[\d]{2}-[\d]{2}\b/im,
                validDueDate = validDatePattern.test(goalForm.due.value);
            if (validDueDate) {
                //send to XHR a object with the necessary data
                loadXHR({
                    "method": "POST",
                    "url": "goals/",
                    "query": "goal=" + goalForm.goal.value + "&due=" + goalForm.due.value + "&me=" + username,
                    "load": goalAdded
                });
            } else {
                document.getElementById("goalFormFeedback").innerHTML = "Due date is invalid it should be in following format yyyy-mm-dd (2016-04-22).";
            }
        } else {
            document.getElementById("goalFormFeedback").innerHTML = "Input Fields need to be filled.";
        }
    },
    //for when user either logs in for signs up
    setUpUser = function (user) {
        var i, logos;
        //sets a local variable with users username for later use
        username = user.Username;
        userFormDiv.style.display = "none";
        search.addEventListener("input", doSearch);
        document.getElementById("postGoal").addEventListener("click", postGoal);
        document.getElementById("profileImg").addEventListener("click", function () {
            getProfile(username);
        });
        logos = document.querySelectorAll(".logo");
        for (i = 0; i < logos.length; i++) {
            logos[i].addEventListener("click", function () {
                getHomeGoals();
            });
        }
        getHomeGoals();
    },
    signedUp = function (result) {
        var dataExists = loopThroughData(result, setUpUser);
        if (dataExists == false) {
            if (result.meta.feedback) {
                document.getElementById("userFormFeedback").innerHTML = result.meta.feedback;
            } else {
                document.getElementById("userFormFeedback").innerHTML = "Error signing up a account for you.";
            }
        }
    },
    loggedIn = function (result) {
        var dataExists = loopThroughData(result, setUpUser);
        if (dataExists == false) {
            if (result.meta.feedback) {
                document.getElementById("userFormFeedback").innerHTML = result.meta.feedback;
            } else {
                document.getElementById("userFormFeedback").innerHTML = "Error logging you in.";
            }
        }
    },
    //post a new user
    signUp = function () {
        if (userForm.username.value.trim() != "" && userForm.password.value.trim() != "") {
            loadXHR({
                "method": "POST",
                "url": "users/",
                "query": "username=" + userForm.username.value + "&password=" + userForm.password.value,
                "load": signedUp
            });
        } else {
            document.getElementById("userFormFeedback").innerHTML = "Input Fields need to be filled.";
        }
    },
    //sign in/get a user
    logIn = function () {
        if (userForm.username.value.trim() != "" && userForm.password.value.trim() != "") {
            loadXHR({
                "method": "GET",
                "url": "users/" + userForm.username.value + "?password=" + userForm.password.value,
                "load": loggedIn
            });
        } else {
            document.getElementById("userFormFeedback").innerHTML = "Input Fields need to be fiiled.";
        }
    };

//set up login and sign up buttons
document.getElementById("signUpButton").addEventListener("click", signUp);
document.getElementById("logInButton").addEventListener("click", logIn);