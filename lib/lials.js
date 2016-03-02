var username,
signUp = function () {
	var signupForm = document.getElementById("userform");
	loadxhr({
        "method": "POST",
        "url": "api/users/",
        "query": "username=" + signupForm.username.value + "&password=" + signupForm.password.value,
        "callbacks": {
            "load": loggedIn,
            "error": errors
        }
    });
},
logIn = function () {
	var loginForm = document.getElementById("userform");
	loadxhr({
	    "method": "GET",
	    "url": "api/users/" + loginForm.username.value + "?password=" + loginForm.password.value,
	    "callbacks": {
	        "load": loggedIn,
	        "error": errors
	    }
	});
},
loggedIn = function (response) {
	var user = JSON.parse(response.target.responseText);
	if (user.rows) {
	     for (i = 0; i < user.rows.length; i ++) {
	        if (user.rows.hasOwnProperty(i)) {
		       	username = user.rows[i].Username;
		       	document.getElementById("formdiv").style.display = "none";
		       	document.getElementById("search").addEventListener("input", search);
				document.getElementById("postgoal").addEventListener("click", postGoal);
				document.getElementById("profileimg").addEventListener("click", function () {
					getProfile(username);
				});
				logos = document.querySelectorAll(".logo")
				for (var i = 0; i < logos.length; i++) {
				    logos[i].addEventListener("click", function () {
						getHomeGoals();
					});
				}
				getHomeGoals();
			}
	    }            	
    } 
    else {
	    document.getElementById("feedback").innerHTML = user.meta.feedback;
    }
},
errors = function (response) {
	document.getElementById("errors").innerHTML = response;
},
renderGoals = function (response) {
	var div, p, p2, div2, p3;
	var goals = document.getElementById("goals");
	var result = JSON.parse(response.target.responseText);
	resetGoalForm();
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
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

	   			p3 = document.createElement("p");
	        	p3.className = "upload";
	        	p3.innerHTML = result.rows[i].Upload;
	        	div.appendChild(p3);

	   			loadxhr({
			        "method": "GET",
			        "url": "api/comments?goalID=" + result.rows[i].ID,
			        "callbacks": {
			            "load": renderComments,
			            "error": errors
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
renderComments = function (response) {
	var i, div, div2, p1, p1text, p2, p2text, div3, img1, img2;
	var result = JSON.parse(response.target.responseText);
	removeForms();
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div = document.getElementById("goal" + result.rows[i].GoalID);
	        	div2 = document.createElement("div");
	        	div2.classList.add("comment");
	        	div2.setAttribute("id", "comment" + result.rows[i].ID);

	        	p1 = document.createElement("p");
	        	p1text = document.createTextNode(result.rows[i].Comment);
	        	p1.appendChild(p1text);
	        	p1.classList.add("commenttext");
	        	div2.appendChild(p1);
	        	
	        	addUsernameButton(result.rows[i], div2);

	        	div3 = document.createElement("div");
	        	div3.classList.add("images");

	   			if (result.rows[i].Username == username) {
		   			addEditCommentButton(result.rows[i], div3);

		   			addDeleteCommentButton(result.rows[i], div3);
	   			}
	   			div2.appendChild(div3);

	   			p3 = document.createElement("p");
	        	p3.classList.add("upload");
	        	p3text = document.createTextNode(result.rows[i].Upload);
	        	p3.appendChild(p3text);
	        	div2.appendChild(p3);

	        	div.appendChild(div2);
	        }
	    }            	
    }
},
renderProfile = function (response) {
	var p, div, img, img2, img;
	var profile = document.getElementById("profile");
	profile.innerHTML = "";
	var result = JSON.parse(response.target.responseText);
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	p = document.createElement("p");
				p.innerHTML = result.rows[i].Username;
				p.className = "username";
				profile.appendChild(p);

				div = document.createElement("div");
	        	div.className = "images";

				img = document.createElement("img");
				img.id = "profilepicture";
				img.setAttribute("alt", "Profile");
				if (result.rows[i].Picture != null) {
					img.src = "api/pictures/" + result.rows[i].Picture;
				}
				else {
					img.src= "images/profilepicture.svg";
				}

				div.appendChild(img);

				if (result.rows[i].Username == username) {
					drag();				
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
getHomeGoals = function () {
	document.getElementById("goals").innerHTML = "";
	document.getElementById("writediv").style.display = "block";
	removeProfileDiv();
	loadxhr({
		"method": "GET",
		"url": "api/goals?me="+username,
		"callbacks": {
			"load": renderGoals,
			"error": errors
		}
	});
},
search = function () {
	var search = document.getElementById("search").value;
	removeProfileDiv();
	if (search.trim() == "") {
		getHomeGoals();
		document.getElementById("writediv").style.display = "block";
	}
	else {
		document.getElementById("writediv").style.display = "none";
		document.getElementById("goals").innerHTML = "";
		loadxhr({
	        "method": "GET",
	        "url": "api/goals?me="+username+"&search=" + search,
	        "callbacks": {
	            "load": renderGoals,
	            "error": errors
	        }
	    });
	}
},
getProfile = function (Username) {
	document.getElementById("search").value = "";
	document.getElementById("profile").style.display = "block";
	document.getElementById("writediv").style.display = "none";
	document.getElementById("goals").innerHTML = "";
	loadxhr({
	    "method": "GET",
	    "url": "api/goals?user=" + Username + "&me=" + username,
	    "callbacks": {
	        "load": renderGoals,
	        "error": errors
	    }
	});
	loadxhr({
	    "method": "GET",
	    "url": "api/users/" + Username+ "?me=" + username,
	    "callbacks": {
	        "load": renderProfile,
	        "error": errors
	    }
	});
},
postGoal = function () {
	var form = document.getElementById("goalform");
	loadxhr({
        "method": "POST",
        "url": "api/goals/",
        "query": "goal=" + form.goal.value + "&due=" + form.due.value + "&me="+username,
        "callbacks": {
            "load": renderGoals,
            "error": errors
        }
    }); 
},
follow = function (user, img) {
	if (img.src.includes("not")) {
		console.log("following");
		loadxhr({
	        "method": "POST",
	        "url": "api/follows",
	        "query": "me=" + username + "&user=" + user,
	        "callbacks": {
	            "load": renderFollowUpdate,
	            "error": errors
	        }
	    });
	}
	else {
	   	console.log("un following");
		loadxhr({
	        "method": "DELETE",
	        "url": "api/follows?me=" + username + "&user=" + user,
	        "callbacks": {
	            "load": renderUnFollowUpdate,
	            "error": errors
	        }
	    });
	}
},
like = function (goalID, img) {
	if (img.src.includes("not")){
		console.log("liked");
		loadxhr({
	        "method": "POST",
	        "url": "api/likes",
	        "query": "me=" + username + "&goalID=" + goalID,
	        "callbacks": {
	            "load": renderLikeUpdate,
	            "error": errors
	        }
	    }); 
	}
	else {
		console.log("unliked");
		loadxhr({
	        "method": "DELETE",
	        "url": "api/likes?me=" + username + "&goalID=" + goalID,
	        "callbacks": {
	            "load": renderUnLikeUpdate,
	            "error": errors
	        }
	    }); 
	}
},
comment = function (goalData, goalDiv) {
	var alreadyacomment = goalDiv.querySelector("#commentform");
	if (!alreadyacomment) {
		removeForms();
		var form = document.createElement("form");
		form.id = "commentform";

		var label = document.createElement("label");
		label.setAttribute("for", "comment");
		var labeltext = document.createTextNode("Enter your Comment.");
		label.appendChild(labeltext);

		var input = document.createElement("input");
		input.setAttribute("type", "text");
		input.setAttribute("name", "comment");
		input.setAttribute("placeholder", "Same I want to go USA!");
		input.setAttribute("autofocus", "true");
		input.setAttribute("required", "true");

		var button = document.createElement("button");
		button.setAttribute("type", "button");
		var buttontext = document.createTextNode("Upload Goal");
		button.appendChild(buttontext);

		form.appendChild(label);
		form.appendChild(input);
		form.appendChild(button);
		
		goalDiv.appendChild(form);
		button.addEventListener("click", function () { 
			console.log("comment" + goalData.ID);
			loadxhr({
		        "method": "POST",
		        "url": "api/comments",
		        "query": "goalID=" + goalData.ID + "&comment=" + input.value + "&me=" + username,
		        "callbacks": {
		            "load": renderComments,
		            "error": errors
		        }
		    });
		});
	}
	else {
		alreadyacomment.remove();
	}
},
completion = function (goalID) {
	console.log("completion");
	loadxhr({
        "method": "PATCH",
        "url": "api/goals/" + goalID + "?completion=true",
        "callbacks": {
            "load": renderCompletionUpdate,
            "error": errors
        }
    }); 
},
deleteGoal = function (goalID) {
	console.log("deleting goal");
	loadxhr({
        "method": "DELETE",
        "url": "api/goals/" + goalID,
        "callbacks": {
           	"load": renderGoalDelete,
            "error": errors
        }
    }); 
},
editGoal = function (goalData, goalDiv) {
	var alreadyAEditGoalForm = goalDiv.querySelector("#editgoalform");
	if (!alreadyAEditGoalForm) {
		removeForms();
		var form = document.createElement("form");
		form.setAttribute("id", "editgoalform");

		var label = document.createElement("label");
		label.setAttribute("for", "goal");
		var labeltext = document.createTextNode("Enter your updated Goal.");
		label.appendChild(labeltext);

		var input = document.createElement("input");
		input.setAttribute("type", "text");
		input.setAttribute("name", "goal");
		input.setAttribute("placeholder", "I want to go USA!");
		input.setAttribute("autofocus", "true");
		input.setAttribute("required", "true");
		input.setAttribute("value", goalData.Goal)

		var button = document.createElement("button");
		button.setAttribute("type", "button");
		var buttontext = document.createTextNode("Update Goal");
		button.appendChild(buttontext);

		form.appendChild(label);
		form.appendChild(input);
		form.appendChild(button);
		
		goalDiv.appendChild(form);
		button.addEventListener("click", function () { 
			loadxhr({
		        "method": "PATCH",
		        "url": "api/goals/" + goalData.ID + "?goal=" + input.value,
		        "callbacks": {
		            "load": renderGoalUpdate,
		            "error": errors
		        }
		    });
		});
	}
	else {
		alreadyAEditGoalForm.remove();
	}
},
deleteComment = function (commentID) {
	console.log("deleting comment");
	loadxhr({
        "method": "DELETE",
        "url": "api/comments/" + commentID,
        "callbacks": {
            "load": renderCommentDelete,
            "error": errors
        }
    }); 
},
editComment = function (comment, goalDiv) {
	var alreadyAEditCommentForm = goalDiv.querySelector("#editcommentform");
	if (!alreadyAEditCommentForm) {
		removeForms();
		var form = document.createElement("form");
		form.setAttribute("id", "editcommentform");

		var label = document.createElement("label");
		label.setAttribute("for", "goal");
		var labeltext = document.createTextNode("Enter your updated Comment.");
		label.appendChild(labeltext);

		var input = document.createElement("input");
		input.setAttribute("type", "text");
		input.setAttribute("name", "goal");
		input.setAttribute("placeholder", "I want to go USA!");
		input.setAttribute("autofocus", "true");
		input.setAttribute("required", "true");
		input.setAttribute("value" , comment.Comment)

		var button = document.createElement("button");
		button.setAttribute("type", "button");
		var buttontext = document.createTextNode("Update Comment");
		button.appendChild(buttontext);

		form.appendChild(label);
		form.appendChild(input);
		form.appendChild(button);
		
		goalDiv.appendChild(form);
		button.addEventListener("click", function () { 
			loadxhr({
		        "method": "PATCH",
		        "url": "api/comments/" + comment.ID + "?comment=" + input.value,
		        "callbacks": {
		            "load": renderCommentUpdate,
		            "error": errors
		        }
		    });
		});
	}
	else {
		alreadyAEditCommentForm.remove();
	}
},
private = function (Username) {
	loadxhr({
        "method": "PATCH",
        "url": "api/users/" + Username,
        "callbacks": {
            "load": renderPrivateUpdate,
            "error": errors
        }
    }); 
},
addFollowButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	if (data.Username1 == null) {
		img.setAttribute("src", "images/notfollowing.svg");
	   	img.setAttribute("alt", "Follow User");
	   	
	}
	else {
		img.setAttribute("src", "images/following.svg");
	   	img.setAttribute("alt", "Unfollow User");
	}
	img.addEventListener("click", function () {
		follow(data.Username, img);
	});
	img.setAttribute("id", "follow");
	goalImgDiv.appendChild(img);
},
addLikeButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	if (data.GoalID == null) {
	   	img.setAttribute("src", "images/notliked.svg");
		img.setAttribute("alt", "Like Goal");
	}
	else {
	   	img.setAttribute("src", "images/liked.svg");
		img.setAttribute("alt", "Unlike Goal");
	}
	img.addEventListener("click", function () {
		like(data.ID, img);
	});
	img.classList.add("like");
	goalImgDiv.appendChild(img);
},
addCommentButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/comment.svg");
	img.setAttribute("alt", "Comment On Goal");
	goalImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
	   	comment(data, goalImgDiv.parentNode);
	});
},
addCompletionButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	if (data.Complete == 1) {
		img.setAttribute("src", "images/complete.svg");
		img.setAttribute("alt", "Goal Completed");
	}
	else {
		img.setAttribute("src", "images/notcomplete.svg");
		img.setAttribute("alt", "Goal Not Completed");
	}
	img.classList.add("completion");
	goalImgDiv.appendChild(img);
	if (data.Username == username){
		img.addEventListener("click",  function () {
		   	completion(data.ID);
		});
	}
},
addDeleteGoalButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/delete.svg");
	img.setAttribute("alt", "Delete Goal");
	goalImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
	   	deleteGoal(data.ID);
	});
},
addEditGoalButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/edit.svg");
	img.setAttribute("alt", "Edit Goal");
	goalImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
		editGoal(data, goalImgDiv.parentNode);
	});
},
addDeleteCommentButton = function (data, commentImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/delete.svg");
	img.setAttribute("alt", "Delete Goal");
	commentImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
	   	deleteComment(data.ID);
	});
},
addEditCommentButton = function (data, commentImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/edit.svg");
	img.setAttribute("alt", "Edit Goal");
	commentImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
		editComment(data, commentImgDiv.parentNode);
	});
},
addUsernameButton = function (data, goalDiv) {
	var ptext, p = document.createElement("p");
	ptext = document.createTextNode(data.Username);
	p.appendChild(ptext);
	p.classList.add("username");
	goalDiv.appendChild(p);
	p.addEventListener("click",  function () {
		getProfile(data.Username);
	});
},
addPrivateButton = function (data, profileImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("id", "profileprivate");
	if (data.Private == 1) {
		img.setAttribute("src", "images/private.svg");
		img.setAttribute("alt", "Private");
	}
	else {
		img.setAttribute("src", "images/notprivate.svg");
		img.setAttribute("alt", "Not Private");
	}
	img.setAttribute("id", "private");
	profileImgDiv.appendChild(img);
	if (data.Username == username){
		img.addEventListener("click",  function () {
		   	private(data.Username);
		});
	}
},
renderGoalUpdate = function (response) {
	var i, div, result = JSON.parse(response.target.responseText);
	removeForms();
	if (result.rows) {
		console.log("goal update");
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div = document.getElementById("goal" + result.rows[i].ID);
	        	div.querySelector(".goaltext").innerHTML = result.rows[i].Goal;
	        }
	    }
	}
},
renderGoalDelete = function (response) {
	var i, div, result = JSON.parse(response.target.responseText);
	removeForms();
	if (result.rows) {
		console.log("goal delete1" +  result.rows.GoalID +  result.rows);
	    document.getElementById("goal" + result.rows.GoalID).remove();

	}
},
renderCommentUpdate = function (response) {
	var i, div, result = JSON.parse(response.target.responseText);
	removeForms();
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div = document.getElementById("comment" + result.rows[i].ID);
	        	div.querySelector(".commenttext").innerHTML = result.rows[i].Comment;
	        }
	    }
	}
},
renderCommentDelete = function (response) {
	var i, div, result = JSON.parse(response.target.responseText);
	removeForms();
	if (result.rows) {
	    document.getElementById("comment" + result.rows.commentID).remove();
	}
},
renderFollowUpdate = function (response) {
	var result = JSON.parse(response.target.responseText);
	if (result.rows) {
		for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
				document.querySelector("#follow").src = "images/following.svg";
			}
		}
	}
},
renderUnFollowUpdate = function (response) {
	var i, result = JSON.parse(response.target.responseText);
	if (result.rows) {
		for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
				document.querySelector("#follow").src = "images/notfollowing.svg";
			}
		}
	}
},
renderLikeUpdate = function (response) {
	var i, div, result = JSON.parse(response.target.responseText);
	if (result.rows) {
		for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
			    div = document.getElementById("goal" + result.rows[i].GoalID);
			    div.querySelector(".like").setAttribute("src", "images/liked.svg");
			}
		}
	}
},
renderUnLikeUpdate = function (response) {
	var i, div, result = JSON.parse(response.target.responseText);
	if (result.rows) {
		for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
			    div = document.getElementById("goal" + result.rows[i].goalID);
			    div.querySelector(".like").src = "images/notliked.svg";
			}
		}
	}
},
renderCompletionUpdate = function (response) {
	var i, div, img, result = JSON.parse(response.target.responseText);
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
				div = document.getElementById("goal" + result.rows[i].ID);
	        	img = div.querySelector(".completion");
				if (result.rows[i].Complete == 1) {
					img.src = "images/complete.svg";
				}
				else {
					img.src = "images/notcomplete.svg";
				}
	        }
	    }
	}
},
renderPrivateUpdate = function (response) {
	var i, img, result = JSON.parse(response.target.responseText);
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	img = document.querySelector("#private");
				if (result.rows[i].Private == 1) {
					img.src = "images/private.svg";
				}
				else {
					img.src = "images/notprivate.svg";
				}
	        }
	    }
	}
},
renderProfilePicture = function (response) {
	var i, img, result = JSON.parse(response.target.responseText);
	if (result.rows) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	img = document.querySelector("#profilepicture");
				if (result.rows[i].Picture != null) {
					img.src = "api/pictures/" + result.rows[i].Picture;
				}
				else {
					img.src = "images/profilepicture.svg";
				}
	        }
	    }
	}
},
resetGoalForm = function () {
	var form = document.getElementById("goalform");
	form.due.value = "";
	form.goal.value = "";
},
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
removeProfileDiv = function () {
	var profile = document.getElementById("profile");
	profile.innerHTML = "";
	profile.style.display = "none";
};

document.getElementById("signupbutton").addEventListener("click", signUp);
document.getElementById("loginbutton").addEventListener("click", logIn);

/*document.getElementById("signout").addEventListener("click", signoutHandler);
deleteAccountHandler = function () {
	document.getElementById("goals").innerHTML = "<p>Delete Account</p>";
}*/