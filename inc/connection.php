<?php
	$dsn = "mysql:host=localhost;charset-UTF-8";
	$username = "root";
	$password = "";
	$dbase = "lials";
	$createquery = "CREATE TABLE IF NOT EXISTS user (
					username VARCHAR(100) not null,
					password VARCHAR(500) not null,
					picture blob,
					private boolean not null,
				    PRIMARY KEY (username)
				    ); CREATE TABLE IF NOT EXISTS goal (
				    id int auto_increment,
					goal VARCHAR(100) not null,
				    due date not null,
				    upload date not null,
				    username VARCHAR(100) not null,
					complete boolean default false,
				    PRIMARY KEY (id),
					CONSTRAINT usernameFK
					FOREIGN KEY (username) REFERENCES user(username)
				    ); CREATE TABLE IF NOT EXISTS comment (
				    id int auto_increment,
					comment VARCHAR(100) not null,
					goalID int not null,
					username VARCHAR(100) not null,
					upload date not null,
				    PRIMARY KEY (id),
					CONSTRAINT goalIDFK
					FOREIGN KEY (goalID) REFERENCES goal(id)
				    ); CREATE TABLE IF NOT EXISTS following (
				    username1 VARCHAR(100) not null,
				    username2 VARCHAR(100) not null,
				    PRIMARY KEY (username1, username2),
					CONSTRAINT username1FK
					FOREIGN KEY (username1) REFERENCES user(username),
					CONSTRAINT username2FK
					FOREIGN KEY (username2) REFERENCES user(username)
				    ); CREATE TABLE IF NOT EXISTS liked (
				    username VARCHAR(100) not null,
				    goalID int not null,
				    PRIMARY KEY (username, goalID),
					CONSTRAINT usernameFK3
					FOREIGN KEY (username) REFERENCES user(username),
					CONSTRAINT goalIDFK2
					FOREIGN KEY (goalID) REFERENCES goal(id)
				    );";
?>