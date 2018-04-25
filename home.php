<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Victim Site</title>

	<!-- CSS -->
	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
	<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/css/form-elements.css">
	<link rel="stylesheet" href="assets/css/style.css">

    <link rel="shortcut icon" href="assets/ico/favicon.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">

    </head>
    <style>
    #commentTable td
    {
    	border: 3px dashed;
    	border-color: #f6d9c6;
    	border-collapse: collapse;
    }
    .font{
    	color: white;
    }
    form
    {
    	float:left;
    }

</style>
<body>
	<div class="container">

		<?php
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "cyber";

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		$user_id = 0;

		$keywords = ['SELECT','FROM','WHERE','AND','OR','DROP','LIMIT']; // A list of SQL keywords used in the program

		if (isset($_POST['uname']))
		{
			// Use of Moving Target Defense against SQL Injection

			// Make it invulnerable against both lower case and upper case SQL injection attempts
			$uname = strtoupper($_POST["uname"]);
			$psw = strtoupper($_POST["psw"]);

			$random_key = sha1(microtime(true).mt_rand(10000,90000)); // Random key generation

			// Rewrite keywords of the SELECT query with the random key appended
			$select =  "SELECT" . $random_key;
			$from = "FROM" . $random_key;
			$where = "WHERE" . $random_key;
			$and = "AND" . $random_key;
			$limit = "LIMIT" . $random_key;

			// Create a duplicate version of the SELECT query that includes the new keywords
			$sql_copy = $select . " ID, Username, Password " . $from . " users " . $where . " Username = '$uname' " . $and . " Password = '$psw' " . $limit . " 0,1";

			$words = explode(' ', $sql_copy); // Split the query to words

			$check = 1; // check = 1 if it is a valid SQL query

			// Check each word in the query whether it includes any SQL keywords,
			// If so, then it should also include the appended keyword,
			// If it doesn't, then it is a SQL Injection attempt, set the check to 0
			for ($x = 0; $x < count($words); $x++) {
				for ($y = 0; $y < count($keywords); $y++) {
					if (strpos($words[$x], $keywords[$y]) !== false) {
						if (strpos($words[$x], $random_key) === false) {
							$check = 0; // SQL Injection attempt
						}
						else{
							$words[$x] = str_replace($random_key, "", $words[$x]); // remove the random key from the keyword
						}
					}
				}
			}

			// Rebuild the SQL query
			$sql = $words[0];

			for ($i = 1; $i < count($words); $i++) {
				$sql = $sql . " " . $words[$i];
			}

			if($check != 1){ // If it is not a valid query, then don't forward it to the database
				$count = 0; // The number of user records received from the database, if 0, the user won't be authenticated
			}
			else{
				$result = mysqli_query($conn, $sql); // Execution of the query

				if($result){ // If there is a user record matching in the database
					$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
					$user_id = $row["ID"];
					$count = mysqli_num_rows($result);

					$cookie_name = "authKey";
					$cookie_value = md5(microtime());
					setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
				}
				else{
					$count = 0;
				}
			}

			// Use of PDO against SQL Injection
			// $pdo = new PDO('mysql:host=localhost;dbname=cyber','root',''); // New PHP Data Object (PDO)

			// $sql = "SELECT ID, Username, Password FROM users where Username = :uname and Password = :psw"; // SQL Query

			// $stmt = $pdo->prepare($sql); // Prepared Statement

			// $stmt -> bindParam(":uname",$_POST["uname"]); // Bind parameters which have the input values, to the statement
			// $stmt -> bindParam(":psw",$_POST["psw"]);

			// $stmt -> execute(); // Execute the prepared statement

			// if($result=$stmt->fetch(PDO::FETCH_OBJ)){
			// 	$user_id = $result->ID;
			// 	$count = 1;
			// 	$cookie_name = "authKey";
			// 	$cookie_value = md5(microtime());
			// 	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
			// }
			// else{
			// 	$count = 0;
			// }

			// SQL Injection Vulnerable Login Prompt
			// $uname = $_POST["uname"];
			// $psw = $_POST["psw"];

			// $sql = "SELECT ID, Username, Password FROM users where Username = '$uname' and Password = '$psw' LIMIT 0,1";

			// $result = mysqli_query($conn, $sql);

			// if($result){
			// 	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			// 	$user_id = $row["ID"];
			// 	$count = mysqli_num_rows($result);

			// 	$cookie_name = "authKey";
			// 	$cookie_value = md5(microtime());
			// 	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
			// }
			// else{
			// 	$count = 0;
			// }

		}
		else{
			$user_id = $_POST["id"];
			$count = 1;
		}

		if (isset($_POST['clear']))
		{
			if($user_id < 0){
				$uid = $_POST["id"];
				$table_id = -1 * $uid;
				$fake_table_name = "t".$table_id;
		        $sql = "TRUNCATE TABLE $fake_table_name";
				if ($conn->query($sql) !== TRUE)
				{
					echo "Error: Unable to Clear Table". $conn->error;
				}
			}
			else{
				$sql = "TRUNCATE TABLE comments";
				if ($conn->query($sql) !== TRUE)
				{
					echo "Error: Unable to Clear Table". $conn->error;
				}
			}

		}

		// Use of Cyber Deception against XSS
		if (isset($_POST['comment']))
		{
			// If $user_id < 0, then it is an attacker, fetch the fake table created for him/her
			if($user_id < 0){
				$uid = $_POST["id"];
				$table_id = -1 * $uid;
				$fake_table_name = "t".$table_id;
				$comment =  ($_POST["comment"]);
		        $sql = "INSERT INTO $fake_table_name (UserID, Content) VALUES ($uid, '".addslashes($comment)."')";
		        $conn->query($sql);
			}
			else{
				$uid = $_POST["id"];
				if(preg_match('/[^a-zA-Z0-9\s\-_\.\?]/', $_POST["comment"])){ // XSS control
				    // Generate a new id for the attacker
				    // Set the $new_id to a random negative number so that the program understands that
				    // it should fetch the fake comments table that was created for the attacker
				    // Users have positive id, attackers have negative id
				    $new_id = rand(-1, -10000);
				    $id_update = "UPDATE users SET ID = $new_id WHERE ID = $user_id";
				    $conn->query($id_update);

				    // Generate a fake table name using the new id of the attacker
				    $table_id = -1 * $new_id; // "-" sign is not allowed in table names, so convert the id to positive
				    $fake_table_name = "t".$table_id; // add a "t" in front of the id, indicating a table

				    // Create a duplicate of the comments table, using the generated fake table name
				    $fake_table_sql = "CREATE TABLE $fake_table_name AS SELECT * FROM comments";
					$conn->query($fake_table_sql);

					// Update the id of the old posts of the attacker on the fake table
					$fake_table_update = "UPDATE $fake_table_name SET UserID = $new_id WHERE UserID = $user_id";
					$conn->query($fake_table_update);

					// Insert the XSS post to the fake table
					$comment = ($_POST["comment"]);
				    $sql = "INSERT INTO $fake_table_name (UserID, Content) VALUES ($new_id, '".addslashes($comment)."')";
				    $conn->query($sql);

				    // For the safety of the users, old posts of the attacker
				    // gets removed from the comments table
				    $old_posts = "DELETE FROM comments WHERE UserID = $user_id";
				    $conn->query($old_posts);

				    // Set the user id to the new id of the attacker
				    $user_id = $new_id;
				}
				else{
					$comment = strip_tags($_POST["comment"]);
					$sql = "INSERT INTO comments (UserID, Content) VALUES ($uid, '".addslashes($comment)."')";

					if ($conn->query($sql) !== TRUE)
					{
						echo "Error: Unable to add comment";
					}
				}
			}
		}

		// Use of Input Validation & Sanitization against XSS
		// if (isset($_POST['comment']))
		// {
		// 	$uid = $_POST["id"];
		// 	if(preg_match('/[^a-zA-Z0-9\s\-_\.\?]/', $_POST["comment"])){ // Input validation
		// 		echo "<script>alert('XSS attempt detected!')</script>"; // XSS Detected, give alert
		// 	}
		// 	else{
		// 		$comment = strip_tags($_POST["comment"]); // Input sanitization
		// 		$sql = "INSERT INTO comments (UserID, Content) VALUES ($uid, '".addslashes($comment)."')";

		// 		if ($conn->query($sql) !== TRUE)
		// 		{
		// 			echo "Error: Unable to add comment";
		// 		}
		// 	}
		// }

		// XSS Vulnerable Post
		// if (isset($_POST['comment']))
		// {
		// 	$uid = $_POST["id"];

		// 	$comment =  ($_POST["comment"]);
		// 	$sql = "INSERT INTO comments (UserID, Content) VALUES ($uid, '".addslashes($comment)."')";

		// 	if ($conn->query($sql) !== TRUE)
		// 	{
		// 		echo "Error: Unable to add comment";
		// 	}
		// }

		if($user_id < 0){
			?>
			<table align="center" style = "margin:20px;">
				<tr><td>
					<form action="home.php" method="POST">
						<input type="hidden" name="id" value = '<?php echo $user_id ?>' />
						<textarea rows="6" cols="50" name="comment" placeholder="Post something..." maxlength="400"></textarea>
						<br>
						<br>
						<table style = "color: black" align="center"><tr><td>
							<input type="submit" value="Post"/>
						</td></tr></table>
					</form>
				</td></tr>
			</table>
			<br>
			<hr>
			<h2 style = "color:#f6d9c6">Posts</h2>
			<div style="border: solid black 1px;">
				<table id="commentTable">
					<?php
					$table_id = -1 * $user_id;
				    $fake_table_name = "t".$table_id;
					$comms = "SELECT Username, Content FROM $fake_table_name, users where users.ID = $fake_table_name.UserID";
					$result = $conn->query($comms);
					if ($result->num_rows > 0) {
					    // output data of each row
						while($row = $result->fetch_assoc()) {
							echo "<tr><td style='width:35%;padding:10px;' class = 'font'>Post by ".$row["Username"]."<hr />".$row["Content"]."</td></tr>";
						}
					} else {
						echo "<tr><td style='width:35%'>No Posts!</td></tr>";
					}
					?>
				</table>
			</div>
			<hr>
			<table align="center">
				<tr><td>
					<div style="color:#00b36b">
						<form action="home.php" method="post">
							<input type="hidden" name="id" value = '<?php echo $user_id ?>' />
							<input type="submit" name="clear" value="Clear Table"/>
						</form>
					</div>
					<div style="color: #e3123c">
						<form action="index.php">
							<input type="submit" value="Sign out" />
						</form>
					</div>
				</td></tr>
			</table>
			<?php
		}
		elseif ($count == 1) {
			?>
			<table align="center" style = "margin:20px;">
				<tr><td>
					<form action="home.php" method="POST">
						<input type="hidden" name="id" value = '<?php echo $user_id ?>' />
						<textarea rows="6" cols="50" name="comment" placeholder="Post something..." maxlength="400"></textarea>
						<br>
						<br>
						<table style = "color: black" align="center"><tr><td>
							<input type="submit" value="Post"/>
						</td></tr></table>
					</form>
				</td></tr>
			</table>
			<br>
			<hr>
			<h2 style = "color:#f6d9c6">Posts</h2>
			<div style="border: solid black 1px;">
				<table id="commentTable">
					<?php
					$comms = "SELECT Username, Content FROM comments, users where users.ID = comments.UserID";
					$result = $conn->query($comms);
					if ($result->num_rows > 0) {
					    // output data of each row
						while($row = $result->fetch_assoc()) {
							echo "<tr><td style='width:35%;padding:10px;' class = 'font'>Post by ".$row["Username"]."<hr />".$row["Content"]."</td></tr>";
						}
					} else {
						echo "<tr><td style='width:35%'>No Posts!</td></tr>";
					}
					?>
				</table>
			</div>
			<hr>
			<table align="center">
				<tr><td>
					<div style="color:#00b36b">
						<form action="home.php" method="post">
							<input type="hidden" name="id" value = '<?php echo $user_id ?>' />
							<input type="submit" name="clear" value="Clear Table"/>
						</form>
					</div>
					<div style="color: #e3123c">
						<form action="index.php">
							<input type="submit" value="Sign out" />
						</form>
					</div>
				</td></tr>
			</table>
			<?php
		} else {
			echo "<script type='text/javascript'>location.href = '/index.php';</script>";
		}
		$conn->close();
		?>

	</div>
	<script src="assets/js/jquery-1.11.1.min.js"></script>
	<script src="assets/bootstrap/js/bootstrap.min.js"></script>
	<script src="assets/js/jquery.backstretch.min.js"></script>
	<script src="assets/js/scripts.js"></script>
</body>
</html>