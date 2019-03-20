<?php

	session_start();
	
	if ((isset($_SESSION['is_loged'])) && ($_SESSION['is_loged']==true))
	{
		header('Location: workers.php');
		exit();
	}

?>
 
 <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ALPHAROB PROJECT PLATFORM</title>
<link rel="stylesheet" type="text/css" href="mystyle.css">
<link href="https://fonts.googleapis.com/css?family=Francois+One&amp;subset=latin-ext" rel="stylesheet"> 
</head>

<body>
		<header>
			<h1 id="logo"> 
				<font color="#cc3333" size="7">alpha </font>
				<font color="lightgray"  size="7">rob  </font> 
				<font color="white"  size="7"> offline robots programing </font>
			</h1>
		</header>
		
		
		<main>
			<div id="container">

				<section>
					<div id="form_login">
					
						<form action="login.php" method="post">
							<div>
								<input type="text" class="form_field" name="login" style="width:200px;" placeholder="LOGIN" autofocus >		
							</div>
							<div>
								<input type="password"  class="form_field" name="password" style="width:200px;" placeholder="PASSWORD">
							</div>
							
							<input type="submit" value="LOGIN" id="add_button" class="form_button" style="width:200px;">
					
						</form>
						<br/>
						<?php
							if(isset($_SESSION['login_error']))	echo $_SESSION['login_error'];
						?>
					</div>
				</section>
			</div>
		</main>
		<footer>
			2018 ALPHAROB SP Z O. O. WSZELKIE PRAWA ZASTRZEŻONE
		</footer>

</body>

</html> 