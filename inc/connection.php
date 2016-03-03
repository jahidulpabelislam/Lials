<?php
	$dsn = "mysql:host=localhost;charset-UTF-8";
	$username = "root";
	$password = "root";
	$dbase = "Lials";
	$createquery = "CREATE TABLE IF NOT EXISTS User (
					Username VARCHAR(100) not null,
					Password VARCHAR(500) not null,
					Picture VARCHAR(50),
					Private boolean not null,
				    PRIMARY KEY (Username)
				    ); CREATE TABLE IF NOT EXISTS Goal (
				    ID int auto_increment,
					Goal VARCHAR(100) not null,
				    Due date not null,
				    Upload date not null,
				    Username VARCHAR(100) not null,
					Complete boolean default false,
				    PRIMARY KEY (ID),
					CONSTRAINT UsernameFK FOREIGN KEY (Username) REFERENCES User(Username)
				    ); CREATE TABLE IF NOT EXISTS Comment (
				    ID int auto_increment,
					Comment VARCHAR(100) not null,
					GoalID int not null,
					Username VARCHAR(100) not null,
					Upload date not null,
				    PRIMARY KEY (ID),
					CONSTRAINT GoalIDFK FOREIGN KEY (GoalID) REFERENCES Goal(ID)
				    ); CREATE TABLE IF NOT EXISTS Following (
				    Username1 VARCHAR(100) not null,
				    Username2 VARCHAR(100) not null,
				    PRIMARY KEY (Username1, Username2),
					CONSTRAINT Username1FK
					FOREIGN KEY (Username1) REFERENCES User(Username),
					CONSTRAINT Username2FK FOREIGN KEY (Username2) REFERENCES User(Username)
				    ); CREATE TABLE IF NOT EXISTS Liked (
				    Username VARCHAR(100) not null,
				    GoalID int not null,
				    PRIMARY KEY (Username, GoalID),
					CONSTRAINT UsernameFK3 FOREIGN KEY (Username) REFERENCES User(Username),
					CONSTRAINT GoalIDFK2 FOREIGN KEY (GoalID) REFERENCES Goal(ID)
				    );";
?>