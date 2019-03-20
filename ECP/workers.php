<?php
	session_start();
	include 'func.php';
	is_loged_check();
	$db_table_name="workers";
	
	require_once('connect.php');
	
	if(isset($_POST['new_worker_name']) and $_SESSION['logged_worker_permissions']>1 )
	{
		$_SESSION['worker_mode']=1;
		add_worker();
	}
	
	
	if(isset($_POST['worker_id_to_update']) and $_SESSION['logged_worker_permissions']>1)
	{
		$_SESSION['worker_mode']=2;
		update_worker();
	}
	
	if(isset($_POST['workers_id_to_delete']) and $_SESSION['logged_worker_permissions']>1)
	{
		$_SESSION['worker_mode']=3;
		delete_worker_via_ID($_POST['workers_id_to_delete']);
	}
	
	if(isset($_POST['change_password_old']) and $_SESSION['logged_worker_permissions']>1)
	{
		$_SESSION['worker_mode']=4;
		change_password($_POST['workers_change_password'],$_POST['change_password_old'],$_POST['change_password_new_1'],$_POST['change_password_new_2']);
	}
	
		
	$sql="SELECT*FROM {$db_table_name}";
	
	if($result=$db->query($sql))
	{
		$num_workers=$result->rowCount();
		$workers_table=$result->fetchAll();
	} 
?>

 <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>ALPHAROB PROJECT PLATFORM</title>
<link rel="stylesheet" type="text/css" href="mystyle.css">
<link href="https://fonts.googleapis.com/css?family=Lato:400,700,900" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Francois+One&amp;subset=latin-ext" rel="stylesheet"> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="scripts.js"></script>

</head>
<body onload='onload_module(4,"worker_mode_number")'>	
		<header>
			<h1 id="logo"> 
				<span id="logo1">alpha</span><span id="logo2">rob </span><span>offline robots programing</span>
			</h1>
		</header>
		<div>
			<nav> 
				<ul class="navigation">
					<li style="background-color:#424242";"> <a href="workers.php" >Workers</a></li>
					<li> <a href="projects.php" >Projects</a></li>
					<li> <a href="areas.php" >Areas</a></li>
					<li> <a href="robots.php" >Robots</a></li>
					<li> <a href="ecp.php" >ECP</a></li>
					<li> <a href="logout.php"> Log Out: <?php echo $_SESSION['logged_worker_name']." ".$_SESSION['logged_worker_surname']; ?> </a></li>
				</ul>
			</nav>
		</div>
		<main>
		<div id="container">
				<section>
					<?php
					$db_name=array('name','surname','email','login','phone','computer_num','birthday','permissions');
					$table_headers=array('Name','Surname','Email','Login','Phone','Computer number','Birthday','Permissions');
					$row_number=$num_workers;
					$table_title="WORKERS LIST";

					$table_array=create_table($workers_table, $table_title, $db_name, $table_headers, $row_number);
					
					?>

				</section>

				<section>

					<?php 
						if($_SESSION['logged_worker_permissions']<2)
						{
							echo '<div id="worker_edition_form" style="display:none;">';
						}
						else
						{
							echo '<div id="worker_edition_form" style="display:block;">';
						}
						
					?>
					<div>
						<ul class="mode_navigation">
							<li onclick='module_nav_click(1,4,"worker_mode_number")' id="mode_butt_1">Add new worker</li>
							<li onclick='module_nav_click(2,4,"worker_mode_number")' id="mode_butt_2">Update worker</li>
							<li onclick='module_nav_click(3,4,"worker_mode_number")' id="mode_butt_3">Delete worker</li>
							<li onclick='module_nav_click(4,4,"worker_mode_number")' id="mode_butt_4">Change password </li>
						</ul>
					</div>
						<div class="form_container">
							<div id="mode1" class="single_mode_container">
								<form method="post">
									<div class="grid-container">
												<div class="grid-item">LOGIN<input type="text" class="form_field" name="new_worker_login" required></div>
												<div class="grid-item">PASSWORD<input type="password" class="form_field" name="new_worker_password" required></div>
												<div class="grid-item">REPEAT PASSWORD<input type="password" class="form_field" name="new_worker_password_1" required></div>
												<div class="grid-item">NAME<input type="text" class="form_field" name="new_worker_name" id="WorkerNameField"></div>	
												<div class="grid-item">SURNAME<input type="text"  class="form_field" name="new_worker_surname"></div>
												<div class="grid-item">E-MAIL<input type="email" class="form_field" name="new_worker_email"></div>
												<div class="grid-item">PHONE<input type="tel" class="form_field" name="new_worker_phone"></div>
												<div class="grid-item">COMPUTER NUMBER<input type="text" class="form_field" name="new_worker_computer"></div>
												<div class="grid-item">BIRTHDAY<input type="date" class="form_field" name="new_worker_birthday"></div>
												<div class="grid-item full_width_item"><label> PERMISSIONS <select class="selector" name="new_worker_permissions">
													<option value=0>0-User is blocked</option>
													<option value=1 selected>1-Normal user </option>
													<option value=2>2-Admin</option>
												</select></label>
												</div>
										<?php 
											if (isset($_SESSION['AddStatusOK'])){
												echo '<div class="form_success grid-item full_width_item">'.$_SESSION['AddStatusOK'].'</div>';
												unset($_SESSION['AddStatusOK']);
											}
											
											if(isset($_SESSION['AddStatusER'])){
												echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['AddStatusER'].'</div>';
												unset($_SESSION['AddStatusER']);
											}
										?>
										<div class="grid-item full_width_item">
										<input type="submit" value="ADD WORKER" id="add_button" class="form_button">
										</div>
								</div>
								</form>
							</div>
							
							<div id="mode2" class="single_mode_container">
								<form method="post">
									<div class="grid-container2item">
										<div class="grid-item">
											<label> WORKER 
											<select name="worker_id_to_update" class="selector" >
											<?php
											for ($i = 0; $i < $num_workers; $i++) 
												{
												echo '<option value="'.$workers_table[$i]['ID'].'">'.$workers_table[$i]['login'].'</option>';
												}
											?>
											</select> </label>
										</div>
										<div class="grid-item">
											<label> PERMISSIONS <select class="selector" name="update_worker_permissions"> 
												<option value=9 selected>No change</option>
												<option value=0>0-User is blocked</option>
												<option value=1 >1-Normal user </option>
												<option value=2>2-Admin</option>
											</select></label>
										</div>
									</div>
									<div class="grid-container">	
										<div class="grid-item"><label> NAME <input type="text" class="form_field" name="update_worker_name"> </label></div>				
										<div class="grid-item"><label> SURNAME <input type="text"  class="form_field" name="update_worker_surname"> </label></div>
										<div class="grid-item"><label> E-MAIL <input type="email" class="form_field" name="update_worker_email"> </label></div>
										
										
										<div class="grid-item"><label> PHONE<input type="tel" class="form_field" name="update_worker_phone"> </label></div>
										<div class="grid-item"><label> COMPUTER NUMBER<input type="text" class="form_field" name="update_worker_computer"> </label></div>
										<div class="grid-item"><label> birthday<input type="date" class="form_field" name="update_worker_birthday"> </label></div>
										
										
										<?php 
											if (isset($_SESSION['UpdateStatusOK'])){
												echo '<div class="form_success grid-item full_width_item">'.$_SESSION['UpdateStatusOK'].'</div>';
												unset($_SESSION['UpdateStatusOK']);
											}
											
											if(isset($_SESSION['UpdateStatusER'])){
												echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['UpdateStatusER'].'</div>';
												unset($_SESSION['UpdateStatusER']);
											}
										?>
										
										<div class="grid-item full_width_item"><input type="submit" value="UPDATE WORKER"  class="form_button"></div>
									</div>
								</form>
							</div>
							
							<div id="mode3" class="single_mode_container">
								<form method="post">
									<div class="grid-container">
										<div class="grid-item center_item">
										<label> WORKER 
										<select name="workers_id_to_delete" class="selector">
										<?php
										for ($i = 0; $i < $num_workers; $i++) 
											{
											echo '<option value="'.$workers_table[$i]['ID'].'">'.$workers_table[$i]['name']." ".$workers_table[$i]['surname'].'</option>';
											}
										?>
										</select></label>
										<?php 
											if (isset($_SESSION['DeleteStatusOK'])){
												echo '<div class="form_success grid-item full_width_item">'.$_SESSION['DeleteStatusOK'].'</div>';
												unset($_SESSION['DeleteStatusOK']);
											}
											
											if(isset($_SESSION['DeleteStatusER'])){
												echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['DeleteStatusER'].'</div>';
												unset($_SESSION['DeleteStatusER']);
											}
										?>
										</div>
										<div class="grid-item full_width_item">
											<input type="submit" value="DELETE WORKER" class="form_button form_mid_button">
										</div>
									</div>
								</form>
							</div>
							
							<div id="mode4" class="single_mode_container">
								<form method="post" >
									<div class="grid-container">
										<div class="grid-item center_item">
											<label> WORKER 
											<select name="workers_change_password" class="selector">
											<?php
											for ($i = 0; $i < $num_workers; $i++) 
												{	
													echo '<option value="'.$workers_table[$i]['ID'].'">'.$workers_table[$i]['name']." ".$workers_table[$i]['surname'].'</option>';
												}
											?>
											</select></label>
										</div>
										<div class="grid-item center_item"> <label> OLD PASSWORD <input type="password" class="form_field" name="change_password_old"> </label> </div>
										<div class="grid-item center_item"> <label> NEW PASSWORD <input type="password" class="form_field" name="change_password_new_1"> </label> </div>
										<div class="grid-item center_item"> <label> REPEAT PASSWORD <input type="password" class="form_field" name="change_password_new_2"> </label> </div>
									<?php 
										if (isset($_SESSION['PassStatusOK'])){
											echo '<div class="form_success grid-item full_width_item">'.$_SESSION['PassStatusOK'].'</div>';
											unset($_SESSION['PassStatusOK']);
										}
										
										if(isset($_SESSION['PassError']))
										{
										echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['PassError'].'</div>';
										unset($_SESSION['PassError']);
										}
									?>
									</div>
									<input type="submit" value="CHANGE PASSWORD" class="form_button form_mid_button">
									</div>
								</form>
							</div>
						</div>
				</section>
				</div>
			</div>
		</main>
		<footer>
			2018 ALPHAROB SP Z O. O. WSZELKIE PRAWA ZASTRZEŻONE
		</footer>

</body>

</html> 