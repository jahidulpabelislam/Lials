var username,
setupsignup = function () {
	document.getElementById("orlogin").addEventListener("click", setuplogin);
	document.getElementById("loginbutton").style.display = "none";
	document.getElementById("orsignup").style.display = "none";
	document.getElementById("signupbutton").style.display = "block";
	document.getElementById("orlogin").style.display = "block";
	document.getElementById("signupbutton").addEventListener("click", signup);
	if (document.getElementById("nogoals")) {
	    document.getElementById("nogoals").remove();
	}
},
signup = function () {
	var signupform = document.getElementById("loginform");
	loadxhr({
        "method": "POST",
        "url": "api/users/add",
        "query": "username=" + signupform.username.value + "&password=" + signupform.password.value,
        "callbacks": {
            "load": loggedIn,
            "error": errors
        }
    });
},
setuplogin = function () {
	document.getElementById("orsignup").addEventListener("click", setupsignup);
	document.getElementById("signupbutton").style.display = "none";
	document.getElementById("orlogin").style.display = "none";
	document.getElementById("loginbutton").style.display = "block";
	document.getElementById("orsignup").style.display = "block";
	document.getElementById("loginbutton").addEventListener("click", login);
},
login = function () {
	var loginform = document.getElementById("loginform");
	loadxhr({
	    "method": "GET",
	    "url": "api/users?username=" + loginform.username.value + "&password=" + loginform.password.value,
	    "callbacks": {
	        "load": loggedIn,
	        "error": errors
	    }
	});
},
loadGoals = function (response) {
	var div, p, ptext, p2, p2text, p3, p3text, div2, img1, img2, img3, img4, p4, p4text;
	var goals = document.getElementById("goals");
	var result = JSON.parse(response.target.responseText);
	if (result.rows.length > 0) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	if (document.getElementById("nogoals")) {
	        		document.getElementById("nogoals").remove();
	        	}
	        	div = document.createElement("div");
	        	div.setAttribute("id", result.rows[i].id);
	        	div.classList.add("goal");

	        	p = document.createElement("p");
	        	ptext = document.createTextNode(result.rows[i].goal);
	        	p.appendChild(ptext);
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

	        	commentbutton(result.rows[i], div2);

	   			if (result.rows[i].username != username) {
	   				followbutton(result.rows[i], div2);
	   				
	   				likebutton(result.rows[i], div2);
	   			}
	   			else {
	   				img2 = document.createElement("img");
	   				if (result.rows[i].complete == true) {
	   					img2.setAttribute("src", "images/complete.svg");
		        		img2.setAttribute("alt", "Goal Completed");
	   				}
		        	else {
		        		img2.setAttribute("src", "images/notcomplete.svg");
		        		img2.setAttribute("alt", "Goal Not Completed");
		        	}
		   			div2.appendChild(img2);

		   			img3 = document.createElement("img");
		        	img3.setAttribute("src", "images/edit.svg");
		        	img3.setAttribute("alt", "Edit Goal");
		   			div2.appendChild(img3);

		   			img4 = document.createElement("img");
		        	img4.setAttribute("src", "images/delete.svg");
		        	img4.setAttribute("alt", "Delete Goal");
		   			div2.appendChild(img4);
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
			            "load": loadComments,
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
loadComments = function (response) {
	var div2, p1, p1text, p2, p2text, p3, p3text, div3, img1, img2, img3;
	var result = JSON.parse(response.target.responseText);
	var div = document.getElementById(result.rows[0].goalID);
	if (result.rows.length > 0) {
	    for (i = 0; i < result.rows.length; i ++) {
	        if (result.rows.hasOwnProperty(i)) {
	        	div2 = document.createElement("div");
	        	div2.classList.add("acomment");

	        	p1 = document.createElement("p");
	        	p1text = document.createTextNode(result.rows[i].comment);
	        	p1.appendChild(p1text);
	        	div2.appendChild(p1);
	        	
	        	p2 = document.createElement("p");
	        	p2.classList.add("username");
	        	p2text = document.createTextNode(result.rows[i].username);
	        	p2.appendChild(p2text);
	        	div2.appendChild(p2);

	        	div3 = document.createElement("div");
	        	div3.classList.add("images");

	        	img1 = document.createElement("img");
	        	img1.setAttribute("src", "images/unfollow.svg");
	        	img1.setAttribute("alt", "Follow User");
	   			div3.appendChild(img1);

	   			img2 = document.createElement("img");
	        	img2.setAttribute("src", "images/edit.svg");
	        	img2.setAttribute("alt", "Edit Comment");
	   			div3.appendChild(img2);

	   			img3 = document.createElement("img");
	        	img3.setAttribute("src", "images/delete.svg");
	        	img3.setAttribute("alt", "Delete Comment");
	   			div3.appendChild(img3);
				
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
write = function () {
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
	var p, ptext;
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
		            "load": loadGoals,
		            "error": errors
		        }
		    });
			document.getElementById("postgoal").addEventListener("click", write);	
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
            "load": loadGoals,
            "error": errors
        }
    });
},
follow = function (username2) {
	console.log("following");
	loadxhr({
        "method": "POST",
        "url": "api/follow",
        "query": "username1=" + username + "&username2=" + username2,
        "callbacks": {
            "load": loadGoals,
            "error": errors
        }
    }); 
},
like = function (goalID) {
	console.log("liked");
	loadxhr({
        "method": "POST",
        "url": "api/like",
        "query": "username=" + username + "&goalID=" + goalID,
        "callbacks": {
            "load": loadGoals,
            "error": errors
        }
    }); 
},
comment = function (goal) {
	var form = document.createElement("form");

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
	button.setAttribute("id", "postgoal");
	button.setAttribute("placeholder", "Same I want to go USA!");
	var buttontext = document.createTextNode("Upload Goal");
	button.appendChild(buttontext);

	form.appendChild(label);
	form.appendChild(input);
	form.appendChild(button);
	
	goal.parentNode.insertBefore(form, goal.nextSibling);
	button.addEventListener("click", function () { 
		console.log("comment" + goal.id);
		loadxhr({
	        "method": "POST",
	        "url": "api/comments/add",
	        "query": "goalID=" + goal.id + "&comment=" + input.value + "&username=" + username,
	        "callbacks": {
	            "load": loadComments,
	            "error": errors
	        }
	    });
	});
},
followbutton = function (data, goal) {
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
	}
	goal.appendChild(img);
},
likebutton = function (data, goal) {
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
	}
	goal.appendChild(img);
},
commentbutton = function (data, goal) {
	var img = document.createElement("img");
	img.setAttribute("src", "images/comment.svg");
	img.setAttribute("alt", "Comment On Goal");
	goal.appendChild(img);
	img.addEventListener("click",  function () {
	   	comment(goal.parentNode);
	});
};

window.addEventListener("load", setupsignup);

/*document.getElementById("private").addEventListener("click", privateHandler);
document.getElementById("signout").addEventListener("click", signoutHandler);
document.getElementById("profilepicture").addEventListener("click", changePPHandler);

deleteGoal = function () {
	var goal = this.parentNode.parentNode.id;
	postit('api/deleteGoal.php', "goal=" + goal, errors);
	setup();
},
deleteComment = function () {
	var comment = this.parentNode.parentNode.id;
	postit('api/deleteComment.php', "comment=" + comment, errors);
},
editGoal = function () {
	var goal = this.parentNode.parentNode.id;
	postit('api/editGoal.php', "goal=" + goal + "&" + "newgoal=jfhewhje", errors);
},
editComment = function () {
	var comment = this.parentNode.parentNode.id;
	postit('api/editComment.php', "comment=" + comment + "&" + "newcomment=hjrgkkeg", errors);
},
completion = function () {
	var goal = this.parentNode.parentNode.id;
	postit('api/completion.php', "goal=" + goal, errors);
},
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
	var comments = document.getElementsByClassName("comment");
	for (var i = 0; i < comments.length; i++) {
	    comments[i].addEventListener("click", comment);
	}
	var likes = document.getElementsByClassName("like");
	for (var i = 0; i < likes.length; i++) {
	    likes[i].addEventListener("click", like);
	}
	var deleteGoals = document.getElementsByClassName("deleteGoal");
	for (var i = 0; i < deleteGoals.length; i++) {
	    deleteGoals[i].addEventListener("click", deleteGoal);
	}
	var deleteComments = document.getElementsByClassName("deleteComment");
	for (var i = 0; i < deleteComments.length; i++) {
	    deleteComments[i].addEventListener("click", deleteComment);
	}
	var adds = document.getElementsByClassName("add");
	for (var i = 0; i < adds.length; i++) {
	    adds[i].addEventListener("click", add);
	}
	var editGoals = document.getElementsByClassName("editGoal");
	for (var i = 0; i < editGoals.length; i++) {
	    editGoals[i].addEventListener("click", editGoal);
	}
	var editComments = document.getElementsByClassName("editComment");
	for (var i = 0; i < editComments.length; i++) {
	    editComments[i].addEventListener("click", editComment);
	}
	var completes = document.getElementsByClassName("complete");
	for (var i = 0; i < completes.length; i++) {
	    completes[i].addEventListener("click", completion);
	}
},*/