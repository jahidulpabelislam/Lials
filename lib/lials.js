var username,
setUpSignup = function () {
	document.getElementById("orlogin").addEventListener("click", setUpLogin);
	document.getElementById("loginbutton").style.display = "none";
	document.getElementById("orsignup").style.display = "none";
	document.getElementById("signupbutton").style.display = "block";
	document.getElementById("orlogin").style.display = "block";
	document.getElementById("signupbutton").addEventListener("click", signUp);
},
signUp = function () {
	var signupForm = document.getElementById("userform");
	loadxhr({
        "method": "POST",
        "url": "api/users/add",
        "query": "username=" + signupForm.username.value + "&password=" + signupForm.password.value,
        "callbacks": {
            "load": loggedIn,
            "error": errors
        }
    });
},
setUpLogin = function () {
	document.getElementById("orsignup").addEventListener("click", setUpSignup);
	document.getElementById("signupbutton").style.display = "none";
	document.getElementById("orlogin").style.display = "none";
	document.getElementById("loginbutton").style.display = "block";
	document.getElementById("orsignup").style.display = "block";
	document.getElementById("loginbutton").addEventListener("click", logIn);
},
logIn = function () {
	var loginForm = document.getElementById("userform");
	loadxhr({
	    "method": "GET",
	    "url": "api/users?username=" + loginForm.username.value + "&password=" + loginForm.password.value,
	    "callbacks": {
	        "load": loggedIn,
	        "error": errors
	    }
	});
},
renderGoals = function (response) {
	var div, p, ptext, p2, p2text, p3, p3text, div2, p4, p4text;
	var goals = document.getElementById("goals");
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	if (document.getElementById("nogoals")) {
	        		document.getElementById("nogoals").remove();
	        	}
	        	div = document.createElement("div");
	        	div.setAttribute("id", "goal" + result.rows[i].id);
	        	div.classList.add("goal");

	        	p = document.createElement("p");
	        	ptext = document.createTextNode(result.rows[i].goal);
	        	p.appendChild(ptext);
	        	p.classList.add("goaltext");
	        	div.appendChild(p);
	        	
	        	p2 = document.createElement("p");
	        	p2text = document.createTextNode(result.rows[i].due);
	        	p2.appendChild(p2text);
	        	div.appendChild(p2);

	        	p3 = document.createElement("p");
	        	p3.classList.add("username");
	        	p3text = document.createTextNode(result.rows[i].username);
	        	p3.appendChild(p3text);
	        	div.appendChild(p3);

	        	div2 = document.createElement("div");
	        	div2.classList.add("images");

	        	addCommentButton(result.rows[i], div2);

	        	addCompletionButton(result.rows[i], div2);
	        	
	   			if (result.rows[i].username != username) {
	   				addFollowButton(result.rows[i], div2);
	   				
	   				addLikeButton(result.rows[i], div2);
	   			}
	   			else {
		   			addEditGoalButton(result.rows[i], div2);

		   			addDeleteGoalButton(result.rows[i], div2);
	   			}
	   			
				div.appendChild(div2);

	   			p4 = document.createElement("p");
	        	p4.classList.add("upload");
	        	p4text = document.createTextNode(result.rows[i].upload);
	        	p4.appendChild(p4text);
	        	div.appendChild(p4);

	   			loadxhr({
			        "method": "GET",
			        "url": "api/comments?goalID=" + result.rows[i].id + "&username="+username,
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
    	p.setAttribute("id", "nogoals");
	    ptext = document.createTextNode("Sorry, no Goals to show!");
	    p.appendChild(ptext);
	    goals.appendChild(p);
    }
},
renderComments = function (response) {
	var div2, p1, p1text, p2, p2text, p3, p3text, div3, img1, img2;
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
		var div = document.getElementById("goal" + result.rows[0].goalID);
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div2 = document.createElement("div");
	        	div2.classList.add("comment");
	        	div2.setAttribute("id", "comment" + result.rows[i].id);

	        	p1 = document.createElement("p");
	        	p1text = document.createTextNode(result.rows[i].comment);
	        	p1.appendChild(p1text);
	        	p1.classList.add("commenttext");
	        	div2.appendChild(p1);
	        	
	        	p2 = document.createElement("p");
	        	p2.classList.add("username");
	        	p2text = document.createTextNode(result.rows[i].username);
	        	p2.appendChild(p2text);
	        	div2.appendChild(p2);

	        	div3 = document.createElement("div");
	        	div3.classList.add("images");

	        	if (result.rows[i].username != username) {
	   				addFollowButton(result.rows[i], div3);
	   			}
	   			else {
		   			addEditCommentButton(result.rows[i], div3);

		   			addDeleteCommentButton(result.rows[i], div3);
	   			}

				div2.appendChild(div3);

	   			p3 = document.createElement("p");
	        	p3.classList.add("upload");
	        	p3text = document.createTextNode(result.rows[i].upload);
	        	p3.appendChild(p3text);
	        	div2.appendChild(p3);

	        	div.appendChild(div2);
	        }
	    }            	
    }
},
postGoal = function () {
	var form = document.getElementById("goalform");
	loadxhr({
        "method": "POST",
        "url": "api/goals/add",
        "query": "goal=" + form.goal.value + "&due=" + form.due.value + "&username="+username,
        "callbacks": {
            "load": loadGoals,
            "error": errors
        }
    }); 
},
loggedIn = function (response) {
	var div = document.getElementById("formdiv");
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
	    if (result.rows.hasOwnProperty(0)) {
	       	username = result.rows[0].username;
	       	div.style.display = "none";
	       	document.getElementById("search").addEventListener("input", search);
			loadxhr({
		        "method": "GET",
		        "url": "api/goals?username="+username,
		        "callbacks": {
		            "load": renderGoals,
		            "error": errors
		        }
		    });
			document.getElementById("postgoal").addEventListener("click", postGoal);	
	    }            	
    } 
    else {
	    document.getElementById("feedback").innerHTML = result.meta.feedback;
    }
},
errors = function (response) {
	document.getElementById("errors").innerHTML = response;
},
search = function () {
	var search = document.getElementById("search").value;
	document.getElementById("goals").innerHTML = "";
	loadxhr({
        "method": "GET",
        "url": "api/goals/search?username="+username+"&search=" + search,
        "callbacks": {
            "load": renderGoals,
            "error": errors
        }
    });
},
follow = function (username2) {
	console.log("following");
	loadxhr({
        "method": "POST",
        "url": "api/follow/add",
        "query": "username1=" + username + "&username2=" + username2,
        "callbacks": {
            "load": renderFollowUpdate,
            "error": errors
        }
    }); 
},
unFollow = function (username2) {
	console.log("un following");
	loadxhr({
        "method": "DELETE",
        "url": "api/follow/delete",
        "query": "username1=" + username + "&username2=" + username2,
        "callbacks": {
            "load": renderFollowUpdate,
            "error": errors
        }
    }); 
},
like = function (goalID) {
	console.log("liked");
	loadxhr({
        "method": "POST",
        "url": "api/like/add",
        "query": "username=" + username + "&goalID=" + goalID,
        "callbacks": {
            "load": renderLikeUpdate,
            "error": errors
        }
    }); 
},
unLike = function (goalID) {
	console.log("un liked");
	loadxhr({
        "method": "DELETE",
        "url": "api/like/delete",
        "query": "username=" + username + "&goalID=" + goalID,
        "callbacks": {
            "load": renderLikeUpdate,
            "error": errors
        }
    }); 
},
comment = function (goal) {
	var alreadyacomment = goal.querySelector("#commentform");
	if (!alreadyacomment) {
		if (document.getElementById("commentform")) {
			document.getElementById("commentform").remove();
		}
		var form = document.createElement("form");
		form.setAttribute("id", "commentform");

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
		
		goal.appendChild(form);
		button.addEventListener("click", function () { 
			console.log("comment" + goal.id);
			loadxhr({
		        "method": "POST",
		        "url": "api/comments/add",
		        "query": "goalID=" + goal.id + "&comment=" + input.value + "&username=" + username,
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
        "url": "api/goals/edit/completion",
        "query": "goalID=" + goalID,
        "callbacks": {
            "load": renderCompletionUpdate,
            "error": errors
        }
    }); 
},
deleteComment = function (commentID) {
	console.log("deleting goal");
	loadxhr({
        "method": "DELETE",
        "url": "api/goals/delete?commentID=" + commentID,
        "callbacks": {
            //"load": loadGoals,
            "error": errors
        }
    }); 
},
editGoal = function (goalDate, goal) {
	var alreadyAEditGoalForm = goal.querySelector("#editgoalform");
	if (!alreadyAEditGoalForm) {
		if (document.getElementById("editgoalform")) {
			document.getElementById("editgoalform").remove();
		}
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
		input.setAttribute("value", goalDate.goal)

		var button = document.createElement("button");
		button.setAttribute("type", "button");
		var buttontext = document.createTextNode("Update Goal");
		button.appendChild(buttontext);

		form.appendChild(label);
		form.appendChild(input);
		form.appendChild(button);
		
		goal.appendChild(form);
		button.addEventListener("click", function () { 
			loadxhr({
		        "method": "PATCH",
		        "url": "api/goals/edit/goal?goalID=" + goalDate.id + "&goal=" + input.value,
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
        "url": "api/comments/delete?commentID=" + commentID,
        "callbacks": {
            //"load": loadGoals,
            "error": errors
        }
    }); 
},
editComment = function (comment, goalDiv) {
	var alreadyAEditCommentForm = goalDiv.querySelector("#editcommentform");
	if (!alreadyAEditCommentForm) {
		if (document.getElementById("editcommentform")) {
			document.getElementById("editcommentform").remove();
		}
		var form = document.createElement("form");
		form.setAttribute("id", "editcommentform");

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
		input.setAttribute("value" , comment.comment)

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
		        "url": "api/comments/edit?commentID=" + comment.id + "&comment=" + input.value,
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
addFollowButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	if (data.username1 == null) {
		img.setAttribute("src", "images/follow.svg");
	   	img.setAttribute("alt", "Follow User");
	   	img.addEventListener("click", function () {
	   		follow(data.username);
	   	});
	}
	else {
		img.setAttribute("src", "images/unfollow.svg");
	   	img.setAttribute("alt", "Unfollow User");
	   	img.addEventListener("click", function () {
	   		unFollow(data.username);
	   	});
	}
	img.classList.add("follow");
	goalImgDiv.appendChild(img);
},
addLikeButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	if (data.goalid == null) {
	   	img.setAttribute("src", "images/unlike.svg");
		img.setAttribute("alt", "Like Goal");
		img.addEventListener("click", function () {
		    like(data.id);
		});
	}
	else {
	   	img.setAttribute("src", "images/like.svg");
		img.setAttribute("alt", "Unlike Goal");
		img.addEventListener("click", function () {
		    unLike(data.id);
		});
	}
	img.classList.add("like");
	goalImgDiv.appendChild(img);
},
addCommentButton = function (data, goalImgDiv) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/comment.svg");
	img.setAttribute("alt", "Comment On Goal");
	goalImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
	   	comment(data.id);
	});
},
addCompletionButton = function (data, goalImgDiv) {
	img = document.createElement("img");
	if (data.complete == 1) {
		img.setAttribute("src", "images/complete.svg");
		img.setAttribute("alt", "Goal Completed");
	}
	else {
		img.setAttribute("src", "images/notcomplete.svg");
		img.setAttribute("alt", "Goal Not Completed");
	}
	img.classList.add("completion");
	goalImgDiv.appendChild(img);
	if (data.username == username){
		img.addEventListener("click",  function () {
		   	completion(data.id);
		});
	}
},
addDeleteGoalButton = function (data, goalImgDiv) {
	img = document.createElement("img");
	img.setAttribute("src", "images/delete.svg");
	img.setAttribute("alt", "Delete Goal");
	goalImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
	   	deleteGoal(data.id);
	});
},
addEditGoalButton = function (data, goalImgDiv) {
	img = document.createElement("img");
	img.setAttribute("src", "images/edit.svg");
	img.setAttribute("alt", "Edit Goal");
	goalImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
		editGoal(data, goalImgDiv.parentNode);
	});
},
addDeleteCommentButton = function (data, commentImgDiv) {
	img = document.createElement("img");
	img.setAttribute("src", "images/delete.svg");
	img.setAttribute("alt", "Delete Goal");
	commentImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
	   	deleteComment(data.id);
	});
},
addEditCommentButton = function (data, commentImgDiv) {
	img = document.createElement("img");
	img.setAttribute("src", "images/edit.svg");
	img.setAttribute("alt", "Edit Goal");
	commentImgDiv.appendChild(img);
	img.addEventListener("click",  function () {
		editComment(data, commentImgDiv.parentNode);
	});
},
renderGoalUpdate = function (response) {
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
		var div = document.getElementById("goal" + result.rows[0].id);
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div.querySelector("goaltext").innerHTML = result.rows[i].goal;
	        }
	    }
	}
},
renderCommentUpdate = function (response) {
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
		var div = document.getElementById("comment" + result.rows[0].id);
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div.querySelector("commenttext").innerHTML = result.rows[i].comment;
	        }
	    }
	}
},
renderFollowUpdate = function (response) {
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
		//var div = document.getElementById("goal" + result.rows[0].id);
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	//div.querySelector("goaltext").innerHTML = result.rows[i].goal;
	        }
	    }
	}
},
renderLikeUpdate = function (response) {
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
		var div = document.getElementById("goal" + result.rows[0].id);
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	//div.querySelector("commenttext").innerHTML = result.rows[i].comment;
	        }
	    }
	}
},
renderCompletionUpdate = function (response) {
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
				var div = document.getElementById("goal" + result.rows[i].id);
	        	var img = div.querySelector("completion");
				if (result.rows[i].complete == 1) {
					img.setAttribute("src", "images/complete.svg");
				}
				else {
					img.setAttribute("src", "images/notcomplete.svg");
				}
	        }
	    }
	}
};

window.addEventListener("load", setUpSignup);

/*document.getElementById("private").addEventListener("click", privateHandler);
document.getElementById("signout").addEventListener("click", signoutHandler);
document.getElementById("profilepicture").addEventListener("click", changePPHandler);

user = function () {
	var username = this.innerHTML;
	postit('api/user.php', "username=" + username, load);
},
profileHandler = function () {
	document.getElementById("formdiv").style.display = "block";
	document.getElementById("formdiv").innerHTML = '<p id="login">Log in</p>'+'<p id="signup">Signup</p>';
	document.getElementById("orlogin").addEventListener("click", login);
	document.getElementById("signup").addEventListener("click", signup);
},
myProfileHandler = function () {
	document.getElementById("goals").innerHTML = "<p>Clicked on your profile.</p>";
},
changePPHandler = function () {
	document.getElementById("goals").innerHTML = "<p>Changed Profile Picture</p>";
},
privateHandler = function () {
	var image = this.style.backgroundImage;
	if(image == 'url("images/private.svg")') {
		this.style.backgroundImage = "url('images/notprivate.svg')";
	}
	else if(image != 'url("images/private.svg")') {
		this.style.backgroundImage = "url('images/private.svg')";
	}
	document.getElementById("goals").innerHTML = "<p>Made your profile Private</p>";
},
signoutHandler = function () {
	document.getElementById("goals").innerHTML = "<p>Sign Out</p>";
},
loadlisteners = function () {
	var users = document.getElementsByClassName("username");
	for (var i = 0; i < users.length; i++) {
	    users[i].addEventListener("click", user);
	}
},*/