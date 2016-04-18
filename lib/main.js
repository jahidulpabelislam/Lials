window.lials = window.lials || {};
window.lials.main = (function () {

    "use strict";

    //set up username variable for later use
    var usersUsername = "",

        //prints out a error message provided
        renderError = function (error) {
            var errorDeleteButton,

                //create the error message
                errorMessage = document.createElement("p");
            errorMessage.innerHTML = error;
            errorMessage.className = "error";
            errors.appendChild(errorMessage);

            //create a button to delete error message
            errorDeleteButton = document.createElement("button");
            errorDeleteButton.innerHTML = "X";
            errorDeleteButton.className = "errorDeleteButton";
            errorMessage.appendChild(errorDeleteButton);

            //add listener for when user clicks the button
            errorDeleteButton.addEventListener("click", function () {
                //remove the error message when clicked
                errorMessage.remove();
            });
        },

        //checks if feedback was provided by API
        checkFeedback = function (feedback, genericMessage) {

            //if there is feedback from PHP give error message using the feedback
            if (feedback) {
                renderError(feedback);
            }
            //else if generic error message is provided give it to user
            else if (genericMessage) {
                renderError(genericMessage)
            }
        },

        //loop through data to see if it exists
        loopThroughData = function (data, toRun, genericMessage) {
            var i;

            //check if data exists
            if (data.rows && data.rows.length > 0) {

                //loop through each row of data in rows
                for (i = 0; i < data.rows.length; i++) {

                    if (data.rows.hasOwnProperty(i)) {

                        //run the function provided as data exists and is valid
                        toRun(data.rows[i]);
                    }
                }
            }
            //otherwise check feedback and show user and return false as data isn't there
            else {
                checkFeedback(data.meta.feedback, genericMessage);
                return false;
            }
        },

        //when user clicks on sign out button
        signOut = function () {

            //make the log in/sign up form display
            userFormDiv.style.display = "block";

            //clear search box
            search.value = "";

            //clear profile
            profile.innerHTML = "";

            //clear goals
            goals.innerHTML = "";
        },

        //remove forms if there's one
        removeForms = function () {

            //check if commentForm is there
            if (document.getElementById("commentForm")) {
                //remove commentForm
                document.getElementById("commentForm").remove();
            }

            //check if editCommentForm is there
            if (document.getElementById("editCommentForm")) {
                //remove editCommentForm
                document.getElementById("editCommentForm").remove();
            }

            //check if editGoalForm is there
            if (document.getElementById("editGoalForm")) {
                //remove editGoalForm
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

        //when a goal update has been submitted
        gotGoalUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderGoalUpdate, "Error Updating your Goal.");
        },

        //remove a goal that's been deleted
        renderGoalDelete = function (result) {

            //check if delete was ok
            if (result.rows && result.rows.GoalID) {

                //finds the goal and removes it
                document.getElementById("goal" + result.rows.GoalID).remove();
            }
            //else check if there if feedback to print
            else {
                checkFeedback(result.meta.feedback, "Error Deleting your Goal.");
            }
        },

        //render comment with update
        renderCommentUpdate = function (comment) {
            var div;

            removeForms();

            //find the comment
            div = document.getElementById("comment" + comment.ID);

            //update comment with new comment
            div.querySelector(".text").innerHTML = comment.Comment;
        },

        //when comment update has been submitted
        gotCommentUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderCommentUpdate, "Error Updating your Comment on Goal.");
        },

        //remove a deleted comment
        renderCommentDelete = function (result) {

            //check if comment delete has been processed
            if (result.rows && result.rows.commentID) {

                //find and remove the comment
                document.getElementById("comment" + result.rows.commentID).remove();
            }
            //else check if there if feedback to print
            else {
                checkFeedback(result.meta.feedback, "Error deleting your comment.");
            }
        },

        //render follow button with update
        renderFollowUpdate = function () {
            //update the follow button
            document.querySelector("#follow").src = "images/following.svg";
        },

        //when user clicks button to follow a user
        gotFollowUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderFollowUpdate, "Error Following a User.");
        },

        //render follow button with update
        renderUnFollowUpdate = function (result) {

            //checks if unfollow has been ok
            if (result.rows && result.rows.user) {
                document.querySelector("#follow").src = "images/notFollowing.svg";
            }
            //checks feedback provided to print error message
            else {
                checkFeedback(result.meta.feedback, "Error Unfollowing a user.");
            }
        },

        //update a goals like button to liked
        renderLikeUpdate = function (like) {

            //find the goal liked
            var div = document.getElementById("goal" + like.GoalID);

            //change the like button to be showing its been liked
            div.querySelector(".like").src = "images/liked.svg";
        },

        //when user likes a goal
        gotLikeUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderLikeUpdate, "Error Liking a Goal.");
        },

        //update a goals liked button to be a like button
        renderUnLikeUpdate = function (result) {
            var div;

            //check if unlike was processed
            if (result.rows && result.rows.goalID) {

                //find the goal unliked
                div = document.getElementById("goal" + result.rows.goalID);

                //update the liked button
                div.querySelector(".like").src = "images/notLiked.svg";
            }
            //else check feedback to print out error message
            else {
                checkFeedback(result.meta.feedback, "Error unliking a goal.");
            }
        },

        //render the completion button
        renderCompletion = function (goal) {

            //find the goal
            var div = document.getElementById("goal" + goal.ID),

                //find the goals completion button
                img = div.querySelector(".completion");

            //checks if goal has been posted as completed
            if (goal.Completion == 1) {
                img.src = "images/complete.svg";
                img.alt = "Goal Completed";
            }
            else {
                img.src = "images/notComplete.svg";
                img.alt = "Goal Not Completed";
            }
        },

        //render the private img to display whether it's a private profile or not
        renderPrivate = function (user) {

            //find the private button
            var img = document.querySelector("#private");

            //checks if the users private attribute is as private
            if (user.Private == 1) {
                img.src = "images/private.svg";
            }
            else {
                img.src = "images/notPrivate.svg";
            }
        },

        //render the profile picture of a user
        renderProfilePicture = function (data) {
            var button,
                buttons = document.getElementById("buttons"),

                //get the users profile picture
                img = document.querySelector("#profilePicture");

            //empty the uploads section as update of profile picture was ok
            uploads.innerHTML = "";

            //checks if the there is a picture or not
            if (data.Picture != null) {

                //make profile picture the new picture uploaded
                img.src = "api/1/" + data.Picture;

                //checks if the profile is the user and there isn't a delete profile picture button
                if (data.Username == usersUsername && !document.getElementById("deleteProfilePicture")) {

                    //create a delete profile picture button
                    button = document.createElement("button");
                    button.id = "deleteProfilePicture";
                    button.innerHTML = "Delete Profile Picture";
                    buttons.appendChild(button);

                    //add a listener for when user clicks on delete picture
                    button.addEventListener("click", function () {
                        window.lials.xhr.load({
                            "method": "DELETE",
                            "url": "pictures?username=" + usersUsername,
                            "load": profilePictureUpdate
                        });
                    });
                }
            }
            //else set the profile picture as a default picture
            else {
                img.src = "images/profilePicture.svg";
            }
        },

        //when user updates one of their goals completion attribute
        completionUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderCompletion, "Error Changing your Goal Completion.");
        },

        //for when user updates their private attribute
        privateUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderPrivate, "Error Changing your Private Attribute.");
        },

        //for when user update their profile picture
        profilePictureUpdate = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderProfilePicture, "Error Changing your Profile Picture.");
        },

        //set up to either follow another user or unfollow a user
        follow = function (user, img) {

            //checks if users not following
            if (img.src.includes("not")) {
                window.lials.xhr.load({
                    "method": "POST",
                    "url": "follows",
                    "query": "username=" + usersUsername + "&userToFollow=" + user,
                    "load": gotFollowUpdate
                });
            }
            else {
                window.lials.xhr.load({
                    "method": "DELETE",
                    "url": "follows?username=" + usersUsername + "&userFollowing=" + user,
                    "load": renderUnFollowUpdate
                });
            }
        },

        //set to either like a goal or unlike a goal
        like = function (goalID, img) {

            //checks if goal is not liked
            if (img.src.includes("not")) {
                window.lials.xhr.load({
                    "method": "POST",
                    "url": "likes",
                    "query": "username=" + usersUsername + "&goalID=" + goalID,
                    "load": gotLikeUpdate
                });
            }
            else {
                window.lials.xhr.load({
                    "method": "DELETE",
                    "url": "likes?username=" + usersUsername + "&goalID=" + goalID,
                    "load": renderUnLikeUpdate
                });
            }
        },

        //create a edit goal form
        editGoal = function (goalData, goalDiv) {
            var editGoalForm, editGoalInputLabel, editGoalInput, editGoalSubmitButton, editGoalFeedback,
                alreadyAEditGoalForm = goalDiv.querySelector("#editGoalForm");

            //check if there's not a edit goal form
            if (!alreadyAEditGoalForm) {
                //remove any forms already shown
                removeForms();

                editGoalForm = document.createElement("div");
                editGoalForm.id = "editGoalForm";
                goalDiv.appendChild(editGoalForm);

                editGoalInputLabel = document.createElement("label");
                editGoalInputLabel.innerHTML = "Enter your updated Goal.";
                editGoalForm.appendChild(editGoalInputLabel);

                editGoalInput = document.createElement("input");
                editGoalInput.placeholder = "I want to go USA!";
                editGoalInput.value = goalData.Goal;
                editGoalForm.appendChild(editGoalInput);

                editGoalSubmitButton = document.createElement("button");
                editGoalSubmitButton.innerHTML = "Update Goal";
                editGoalForm.appendChild(editGoalSubmitButton);

                editGoalFeedback = document.createElement("p");
                editGoalFeedback.id = "editGoalFeedback";
                editGoalForm.appendChild(editGoalFeedback);

                editGoalSubmitButton.addEventListener("click", function () {

                    //checks if input is not empty
                    if (editGoalInput.value.trim() != "") {

                        editGoalInput.style.borderColor = "initial";
                        editGoalFeedback.innerHTML = "";

                        window.lials.xhr.load({
                            "method": "PATCH",
                            "url": "goals/" + goalData.ID + "?goal=" + editGoalInput.value,
                            "load": gotGoalUpdate
                        });

                    }
                    //else input is empty
                    else {
                        editGoalInput.style.borderColor = "red";
                        editGoalFeedback.innerHTML = "Goal is empty.";
                    }
                });
            }
            //else remove the edit goal form
            else {
                alreadyAEditGoalForm.remove();
            }
        },

        //creates a edit comment form
        editComment = function (comment, goalDiv) {
            var editCommentForm, editCommentLabel, editCommentInput, editCommentSubmitButton, editCommentFeedback,
                alreadyAEditCommentForm = goalDiv.querySelector("#editCommentForm");

            //check if there's not a edit comment form
            if (!alreadyAEditCommentForm) {

                //remove any forms already shown
                removeForms();

                editCommentForm = document.createElement("div");
                editCommentForm.id = "editCommentForm";
                goalDiv.appendChild(editCommentForm);

                editCommentLabel = document.createElement("label");
                editCommentLabel.innerHTML = "Enter your updated Comment.";
                editCommentForm.appendChild(editCommentLabel);

                editCommentInput = document.createElement("input");
                editCommentInput.placeholder = "I want to go USA!";
                editCommentInput.value = comment.Comment;
                editCommentForm.appendChild(editCommentInput);

                editCommentSubmitButton = document.createElement("button");
                editCommentSubmitButton.innerHTML = "Update Comment";
                editCommentForm.appendChild(editCommentSubmitButton);

                editCommentFeedback = document.createElement("p");
                editCommentFeedback.id = "editCommentFeedback";
                editCommentForm.appendChild(editCommentFeedback);

                editCommentSubmitButton.addEventListener("click", function () {

                    //checks if input is not empty
                    if (editCommentInput.value.trim() != "") {

                        editCommentInput.style.borderColor = "initial";
                        editCommentFeedback.innerHTML = "";

                        window.lials.xhr.load({
                            "method": "PATCH",
                            "url": "comments/" + comment.ID + "?comment=" + editCommentInput.value,
                            "load": gotCommentUpdate
                        });

                    }
                    //else input is empty
                    else {
                        editCommentInput.style.borderColor = "red";
                        editCommentFeedback.innerHTML = "Comment is empty.";
                    }
                });
            }
            //else remove the edit comment form
            else {
                alreadyAEditCommentForm.remove();
            }
        },

        //add the username button
        addUsernameButton = function (Username, div) {
            var usernameButton = document.createElement("p");
            usernameButton.innerHTML = Username;
            usernameButton.classList.add("username");
            div.appendChild(usernameButton);

            //add a listener for when user clicks on it to then get their profile
            usernameButton.addEventListener("click", function () {
                getProfile(Username);
            });
        },

        //add the upload date text
        addUploadDateText = function (uploadDate, div) {
            var uploadDateText = document.createElement("p");
            uploadDateText.className = "upload";
            uploadDateText.innerHTML = uploadDate;
            div.appendChild(uploadDateText);
        },

        //add the goal/comment text
        addText = function (text, div) {
            var theText = document.createElement("p");
            theText.innerHTML = text;
            theText.className = "text";
            div.appendChild(theText);
        },

        //add the images div for a goal/comment/profile
        addImagesDiv = function (div) {
            var imagesDiv = document.createElement("div");
            imagesDiv.className = "images";
            div.appendChild(imagesDiv);
            return imagesDiv;
        },

        //clear profile div for later
        removeProfileDiv = function () {
            profile.innerHTML = "";
            profile.style.display = "none";
        },

        //render a users profile information
        renderProfile = function (user) {
            var profileImages, profilePicture, privateButton, followButton, profileButtons, signOutButton;

            profilePicture = document.createElement("img");
            profilePicture.id = "profilePicture";
            profilePicture.alt = "Profile Picture of " + user.Username;
            profile.appendChild(profilePicture);

            addUsernameButton(user.Username, profile);

            profileImages = addImagesDiv(profile);

            privateButton = document.createElement("img");
            privateButton.id = "private";
            profileImages.appendChild(privateButton);
            renderPrivate(user);

            //checks if the profile is the user
            if (user.Username == usersUsername) {

                //set up drag and drop
                window.lials.dragNDrop.setup(window);

                //make private button changeable as its the users profile
                privateButton.addEventListener("click", function () {
                    window.lials.xhr.load({
                        "method": "PATCH",
                        "url": "users/" + usersUsername,
                        "load": privateUpdate
                    })
                });

                profileButtons = document.createElement("div");
                profileButtons.id = "buttons";
                profile.appendChild(profileButtons);

                signOutButton = document.createElement("button");
                signOutButton.type = "button";
                signOutButton.innerHTML = "Sign Out";
                profileButtons.appendChild(signOutButton);
                signOutButton.addEventListener("click", signOut);
            }
            //else profile is not user
            else {

                //stop drag and drop
                window.lials.dragNDrop.stop(window);

                followButton = document.createElement("img");
                followButton.id = "follow";
                //check if user is follow user
                if (user.Following == null) {
                    followButton.src = "images/notFollowing.svg";
                    followButton.alt = "Follow User";
                }
                else {
                    followButton.src = "images/following.svg";
                    followButton.alt = "Unfollow User";
                }
                followButton.addEventListener("click", function () {
                    follow(user.Username, followButton);
                });
                profileImages.appendChild(followButton);
            }

            renderProfilePicture(user, profileImages);
        },

        //for when user has attempted to get a users profile
        gotProfile = function (result) {
            loopThroughData(result, renderProfile, "Error getting profile.");
        },

        //renders a comment
        renderComment = function (comment) {
            var goal, aComment, commentImages, editCommentButton, deleteCommentButton;

            removeForms();

            goal = document.getElementById("goal" + comment.GoalID);

            aComment = document.createElement("div");
            aComment.className = "comment";
            aComment.id = "comment" + comment.ID;
            goal.appendChild(aComment);

            addText(comment.Comment, aComment);

            addUsernameButton(comment.Username, aComment);

            commentImages = addImagesDiv(aComment);

            //checks if comment is by user
            if (comment.Username == usersUsername) {

                editCommentButton = document.createElement("img");
                editCommentButton.src = "images/edit.svg";
                editCommentButton.alt = "Edit Comment";
                commentImages.appendChild(editCommentButton);
                editCommentButton.addEventListener("click", function () {
                    editComment(comment, aComment);
                });

                deleteCommentButton = document.createElement("img");
                deleteCommentButton.src = "images/delete.svg";
                deleteCommentButton.alt = "Delete Goal";
                commentImages.appendChild(deleteCommentButton);
                deleteCommentButton.addEventListener("click", function () {
                    window.lials.xhr.load({
                        "method": "DELETE",
                        "url": "comments/" + comment.ID,
                        "load": renderCommentDelete
                    });
                });
            }

            addUploadDateText(comment.Upload, aComment);
        },

        //for when user attempts to add a comment
        addedComment = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderComment, "Error processing your comment.");
        },

        //creates a add comment form
        comment = function (goalData, goalDiv) {
            var commentForm, commentInputLabel, commentInput, submitCommentButton, commentFeedback,

                alreadyAComment = goalDiv.querySelector("#commentForm");

            //if there's a comment form
            if (!alreadyAComment) {

                //remove any forms already shown
                removeForms();

                commentForm = document.createElement("div");
                commentForm.id = "commentForm";
                goalDiv.appendChild(commentForm);

                commentInputLabel = document.createElement("label");
                commentInputLabel.innerHTML = "Enter your Comment.";
                commentForm.appendChild(commentInputLabel);

                commentInput = document.createElement("input");
                commentInput.placeholder = "Same I want to go USA!";
                commentForm.appendChild(commentInput);

                submitCommentButton = document.createElement("button");
                submitCommentButton.innerHTML = "Upload Comment";
                commentForm.appendChild(submitCommentButton);

                commentFeedback = document.createElement("p");
                commentFeedback.id = "commentFeedback";
                commentForm.appendChild(commentFeedback);

                submitCommentButton.addEventListener("click", function () {

                    //checks if input is not empty
                    if (commentInput.value.trim() != "") {

                        commentInput.style.borderColor = "initial";
                        commentFeedback.innerHTML = "";

                        window.lials.xhr.load({
                            "method": "POST",
                            "url": "comments",
                            "query": "goalID=" + goalData.ID + "&comment=" + commentInput.value + "&me=" + usersUsername,
                            "load": addedComment
                        });

                    }
                    //else input is empty
                    else {
                        commentInput.style.borderColor = "red";
                        commentFeedback.innerHTML = "Comment is empty.";
                    }
                });
            }
            else {
                alreadyAComment.remove();
            }
        },

        //for when user has attempted to get comments for a goal
        gotComments = function (result) {
            //send the data, the function to do if data is valid
            loopThroughData(result, renderComment);
        },

        //render a goal
        renderGoal = function (goal) {
            var aGoal, goalDueDate, goalImages, editGoalButton, deleteGoalButton, completionButton, commentButton, likeButton;

            //resets the goal form
            goalForm.goal.value = "";
            goalForm.due.value = "";

            if (document.getElementById("noGoals")) {
                document.getElementById("noGoals").remove();
            }

            aGoal = document.createElement("div");
            aGoal.id = "goal" + goal.ID;
            aGoal.className = "goal";
            goals.insertBefore(aGoal, goals.firstChild);

            addText(goal.Goal, aGoal);

            goalDueDate = document.createElement("p");
            goalDueDate.innerHTML = goal.Due;
            aGoal.appendChild(goalDueDate);

            addUsernameButton(goal.Username, aGoal);

            goalImages = addImagesDiv(aGoal);

            commentButton = document.createElement("img");
            commentButton.src = "images/comment.svg";
            commentButton.alt = "Comment On Goal";
            goalImages.appendChild(commentButton);
            commentButton.addEventListener("click", function () {
                comment(goal, aGoal);
            });

            completionButton = document.createElement("img");
            completionButton.className = "completion";
            goalImages.appendChild(completionButton);
            renderCompletion(goal);

            //checks if the goal is by user
            if (goal.Username == usersUsername) {

                completionButton.addEventListener("click", function () {
                    window.lials.xhr.load({
                        "method": "PATCH",
                        "url": "goals/" + goal.ID + "?completion=true",
                        "load": completionUpdate
                    });
                });

                editGoalButton = document.createElement("img");
                editGoalButton.src = "images/edit.svg";
                editGoalButton.alt = "Edit Goal";
                goalImages.appendChild(editGoalButton);
                editGoalButton.addEventListener("click", function () {
                    editGoal(goal, aGoal);
                });

                deleteGoalButton = document.createElement("img");
                deleteGoalButton.src = "images/delete.svg";
                deleteGoalButton.alt = "Delete Goal";
                goalImages.appendChild(deleteGoalButton);
                deleteGoalButton.addEventListener("click", function () {

                    window.lials.xhr.load({
                        "method": "DELETE",
                        "url": "goals/" + goal.ID,
                        "load": renderGoalDelete
                    });
                });
            }
            //else goal is not by user
            else {

                likeButton = document.createElement("img");
                //check if goal is liked
                if (goal.Liked == 1) {
                    likeButton.src = "images/liked.svg";
                    likeButton.alt = "Unlike Goal";
                }
                else {
                    likeButton.src = "images/notLiked.svg";
                    likeButton.alt = "Like Goal";
                }
                likeButton.className = "like";
                likeButton.addEventListener("click", function () {
                    like(goal.ID, likeButton);
                });
                goalImages.appendChild(likeButton);
            }

            addUploadDateText(goal.Upload, aGoal);

            //get comments for the goal
            window.lials.xhr.load({
                "method": "GET",
                "url": "comments?goalID=" + goal.ID,
                "load": gotComments
            });
        },

        //for when user has attempted to get goal
        gotGoals = function (result) {
            var noGoalsDiv, noGoalsText,

                //send the data, the function to do if data is valid
                dataExists = loopThroughData(result, renderGoal);

            //check if data doesn't exist
            if (dataExists == false) {

                //check theres no feedback
                if (!result.meta.feedback) {

                    //assume theres no error and no goals to show
                    noGoalsDiv = document.createElement("div");
                    noGoalsDiv.id = "noGoals";
                    noGoalsText = document.createElement("p");
                    noGoalsText.innerHTML = "Sorry, no Goals to show!";
                    noGoalsDiv.appendChild(noGoalsText);
                    goals.appendChild(noGoalsDiv);
                }
            }
        },

        //for when user has attempted to add a goal
        goalAdded = function (result) {
            //send the data, the function to do if data is valid and generic error message
            loopThroughData(result, renderGoal, "Error processing your goal.");
        },

        //gets goals
        getGoals = function (query) {

            goals.innerHTML = "";

            uploads.innerHTML = "";

            window.lials.xhr.load({
                "method": "GET",
                "url": "goals?" + query,
                "load": gotGoals
            });
        },

        //get the goals of the user home page (goals of users they are following & theirs)
        getHomeGoals = function () {

            //stop drag and drop
            window.lials.dragNDrop.stop(window);

            removeProfileDiv();

            //reset search
            search.value = "";

            goalFormDiv.style.display = "block";

            getGoals("me=" + usersUsername);
        },

        //when user inputs something into search
        doSearch = function () {

            var searchValue = search.value;

            removeProfileDiv();

            //check if search is not empty or not
            if (searchValue.trim() == "") {
                getHomeGoals();
            }
            else {
                window.lials.dragNDrop.stop(window);
                goalFormDiv.style.display = "none";
                getGoals("me=" + usersUsername + "&search=" + searchValue);
            }
        },

        //when user clicks on a profile
        getProfile = function (Username) {

            search.value = "";

            goalFormDiv.style.display = "none";

            profile.innerHTML = "";
            profile.style.display = "block";

            //gets the goals of the user
            getGoals("user=" + Username + "&me=" + usersUsername);

            //send to XHR a object with the necessary data
            window.lials.xhr.load({
                "method": "GET",
                "url": "users/" + Username + "?me=" + usersUsername,
                "load": gotProfile
            });
        },

        //when user clicks to send goal
        postGoal = function () {

            //makes the feedback section clear
            goalFormFeedback.innerHTML = "";

            //check if both input boxes are empty
            if (goal.value.trim() == "" && due.value.trim() == "") {
                goal.style.borderColor = "red";
                due.style.borderColor = "red";
                goalFormFeedback.innerHTML = "Input fields needs to be filled.";
            }
            //checks if goal is empty
            else if (goal.value.trim() == "") {
                due.style.borderColor = "initial";
                goal.style.borderColor = "red";
                goalFormFeedback.innerHTML = "Goal field needs to be filled.";
            }
            //checks if due date is empty
            else if (due.value.trim() == "") {
                due.style.borderColor = "red";
                goal.style.borderColor = "initial";
                goalFormFeedback.innerHTML = "Due date field needs to be filled.";
            }
            //else inputs are filled
            else {

                goal.style.borderColor = "initial";

                //do a regular expression to check if due data provided is valid
                var validDatePattern = /\b[\d]{4}-[\d]{2}-[\d]{2}\b/im,
                    validDueDate = validDatePattern.test(goalForm.due.value);
                if (validDueDate) {

                    due.style.borderColor = "initial";

                    //send to XHR a object with the necessary data
                    window.lials.xhr.load({
                        "method": "POST",
                        "url": "goals",
                        "query": "goal=" + goal.value + "&due=" + due.value + "&me=" + usersUsername,
                        "load": goalAdded
                    });
                }
                //else due date isn't valid
                else {
                    due.style.borderColor = "red";
                    goalFormFeedback.innerHTML = "Due date is invalid it should be in following format yyyy-mm-dd (2016-04-22).";
                }
            }
        },

        //for when user either logs in or signs up, it sets everything up
        setUpUser = function (user) {

            //sets a variable with users username for later use
            window.lials.main.usersUsername = user.Username;
            usersUsername = user.Username;

            userFormFeedback.innerHTML = "";

            //make the log in/sign up form not visible
            userFormDiv.style.display = "none";

            //get home (get goals of people they're following and theirs)
            getHomeGoals();
        },

        //after user attempted to sign up
        signedUp = function (result) {

            //send the data, the function to do if data is valid, and it will return whether data is valid
            var dataExists = loopThroughData(result, setUpUser);

            //check if data was valid
            if (dataExists == false) {

                //check if feedback was provided
                if (result.meta.feedback) {
                    userFormFeedback.innerHTML = result.meta.feedback;
                }
                //else print generic error message
                else {
                    userFormFeedback.innerHTML = "Error signing up a account for you.";
                }
            }
        },

        //after user has attempted to log in
        loggedIn = function (result) {

            //send the data, the function to do if data is valid, and it will return whether data is valid
            var dataExists = loopThroughData(result, setUpUser);

            //check if data was valid
            if (dataExists == false) {

                //check if feedback was provided
                if (result.meta.feedback) {
                    userFormFeedback.innerHTML = result.meta.feedback;
                }
                //else print generic error message
                else {
                    userFormFeedback.innerHTML = "Error logging you in.";
                }
            }
        },

        //post a new user or log in user
        logIn = function () {

            //checks if inputs both are empty
            if (username.value.trim() == "" && password.value.trim() == "") {
                username.style.borderColor = "red";
                password.style.borderColor = "red";
                userFormFeedback.innerHTML = "Input fields needs to be filled.";
            }
            //else checks if username input is empty
            else if (username.value.trim() == "") {
                password.style.borderColor = "initial";
                username.style.borderColor = "red";
                userFormFeedback.innerHTML = "Username field needs to be filled.";
            }
            //else checks if password input is empty
            else if (password.value.trim() == "") {
                password.style.borderColor = "red";
                username.style.borderColor = "initial";
                userFormFeedback.innerHTML= "Password field needs to be filled.";
            }
            //else inputs are filled
            else {
                username.style.borderColor = "initial";
                password.style.borderColor = "initial";

                //checks if user is trying to sign up
                if (this.id == "signUpButton") {
                    //sends a object with necessary data to XHR
                    window.lials.xhr.load({
                        "method": "PUT",
                        "url": "users/" + username.value + "?password=" + password.value,
                        "load": signedUp
                    });
                }
                //else checks if user is trying to log in
                else if (this.id == "logInButton") {
                    //sends a object with necessary data to XHR
                    window.lials.xhr.load({
                        "method": "GET",
                        "url": "users/" + username.value + "?password=" + password.value,
                        "load": loggedIn
                    });
                }
            }
        },

        //set up site
        setUp = function () {

            signUpButton.addEventListener("click", logIn);

            logInButton.addEventListener("click", logIn);
            
            search.addEventListener("input", doSearch);

            postGoalButton.addEventListener("click", postGoal);

            //add a listener for when user clicks profile image to get their profile
            profileImg.addEventListener("click", function () {
                getProfile(usersUsername);
            });

            //add a listener to the logos to go home (get goals of people they're following and theirs)
            logo.addEventListener("click", getHomeGoals);
            mobileLogo.addEventListener("click", getHomeGoals);
        };

    return {
        "setup": setUp,
        "profilePictureUpdate": profilePictureUpdate,
        "renderError": renderError,
        "usersUsername": usersUsername
    };

}());

window.addEventListener("load", window.lials.main.setup);