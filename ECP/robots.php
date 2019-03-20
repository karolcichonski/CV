<?php
	session_start();
	include 'func.php';
	require_once('connect.php');
	is_loged_check();
	$db_table_name="robots";
	
	if (isset($_POST['add_robot_name'])&& $_SESSION['logged_worker_permissions']>1){
		add_robot();
	}	
	
	if (!isset($_SESSION['robot_selected_project'])){
		$_SESSION['robot_selected_project']=Get_last_project_id();
		$_SESSION['robot_selected_area']=Get_first_area_in_project($_SESSION['robot_selected_project']);
	}
	
	if(isset($_POST['robot_selected_area'])){
		$_SESSION['robot_selected_area']=$_POST['robot_selected_area'];
		unset($_POST['robot_selected_area']);
	}
	
	if(isset($_POST['robot_selected_project'])){
		$_SESSION['robot_selected_project']=$_POST['robot_selected_project'];
		unset($_POST['robot_selected_project']);
		$first_area=Get_first_area_in_project($_SESSION['robot_selected_project']);
		$last_area=Get_last_area_in_project($_SESSION['robot_selected_project']);
			if ($_SESSION['robot_selected_area']<$first_area || $_SESSION['robot_selected_area']>$last_area ){
					$_SESSION['robot_selected_area']=Get_first_area_in_project($_SESSION['robot_selected_project']);
			}
	}
	
	if (isset($_POST['record_to_remove'])){
		remove_record_in_db($_POST['record_to_remove'], "robots");
		unset($_POST['record_to_remove']);
	}
	
	$Areas_id_name_table=Areas_Id_Table($_SESSION['robot_selected_project']);	
	$Areas_id_name_atable=Areas_Id_aTable();
	$Project_id_name_atable=Projects_Id_aTable();
	$project_id_name_table=Projects_Id_Table();
	
	$sql="SELECT*FROM {$db_table_name} WHERE project_id='{$_SESSION['robot_selected_project']}' AND area_id='{$_SESSION['robot_selected_area']}' ORDER BY id ASC";	
	
	if($result=$db->query($sql))
	{
		$num_robots=$result->rowCount();
		$robots_table=$result->fetchAll();
	} 
	
	for($i=0; $i<$num_robots; $i++){
		
		$robots_table[$i]['project_name']=$Project_id_name_atable[$robots_table[$i]['project_id']];
		$robots_table[$i]['area_name']=$Areas_id_name_atable[$robots_table[$i]['area_id']];
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
<script src="jquery-3.3.1.min.js"></script>
</head>

<body onload='onload_module(3,"robot_mode_number")'>
		<div id="header">
			<header>
				<h1 id="logo"> 
					<span id="logo1">alpha</span><span id="logo2">rob </span><span>offline robots programing</span>
				</h1>
			</header>
		</div>
		<div id="nav">
			<nav> 
				<ul class="navigation">
					<li> <a href="workers.php" >Workers</a></li>
					<li> <a href="projects.php" >Projects</a></li>
					<li> <a href="areas.php" >Areas</a></li>
					<li style="background-color:#424242";"> <a href="robots.php" >Robots</a></li>
					<li> <a href="ecp.php" >ECP</a></li>
					<li> <a href="logout.php"> Log Out: <?php echo $_SESSION['logged_worker_name']." ".$_SESSION['logged_worker_surname']; ?> </a></li>
				</ul>
			</nav>
		</div>
		<main>
			<div id="container">
				<div class="grid-container2item">
					<form method="post">
						<div class="grid-item"><label> PROJECT <select name="robot_selected_project" class="selector" onchange="this.form.submit()" style="width:200px;">
							<?php
								for($i=0; $i<count($project_id_name_table); $i++)
								{
									if( isset($_SESSION['robot_selected_project']) && $_SESSION['robot_selected_project']==$project_id_name_table[$i][0]){
										echo '<option value="'.$project_id_name_table[$i][0].'" selected>'.$project_id_name_table[$i][1].'</option>';
									}elseif (!isset($_SESSION['robot_selected_project']) && $i==0){
										echo '<option value="'.$project_id_name_table[$i][0].'" selected>'.$project_id_name_table[$i][1].'</option>';
									}else{
										echo '<option value="'.$project_id_name_table[$i][0].'">'.$project_id_name_table[$i][1].'</option>';
									}
								}
							
							?>
						</select></label></div>
					</form>
					<form method="post">
						<div class="grid-item"><label> AREA <select name="robot_selected_area" class="selector" onchange="this.form.submit()" style="width:200px;">
							<?php
								for($i=0; $i<count($Areas_id_name_table); $i++)
								{
									if( isset($_SESSION['robot_selected_area']) && $_SESSION['robot_selected_area']==$Areas_id_name_table[$i][0]){
										echo '<option value="'.$Areas_id_name_table[$i][0].'" selected>'.$Areas_id_name_table[$i][1].'</option>';
									}else{
										echo '<option value="'.$Areas_id_name_table[$i][0].'" >'.$Areas_id_name_table[$i][1].'</option>';
									}
								}
							?>
							
						</select></label></div>
					</form>
				</div>
				<section>
				<?php
					$db_name=array('name','brand','project_name','area_name','tasks','seventh_axis','type');
					$table_headers=array('Name','Brand','Project','Area','Tasks','7th Axis', 'Type');
					$row_number=$num_robots;
					$table_title="ROBOTS LIST";

					$table_array=create_table($robots_table, $table_title, $db_name, $table_headers, $row_number);
				
				?>
				</section>
				
				<section>
					<?php 
						if($_SESSION['logged_worker_permissions']<2)
						{
							echo '<div id="edition_form" style="display:none;">';
						}
						else
						{
							echo '<div id="edition_form" style="display:block;">';
						}
						
					?>	
					<div>
						<ul class="mode_navigation">
							<li onclick='module_nav_click(1,3,"robot_mode_number")' id="mode_butt_1">Add robot</li>
							<li onclick='module_nav_click(2,3,"robot_mode_number")' id="mode_butt_2">Update robot</li>
							<li onclick='module_nav_click(3,3,"robot_mode_number")' id="mode_butt_3">Delete robot</li>
						</ul>
					</div>
						<div  class="form_container" >
							<div id="mode1" class="single_mode_container">
								<form method="post">
									<div class="grid-container">
										<div class="grid-item"><label> ROBOT NAME <input type="text" class="form_field" name="add_robot_name" required> </label></div>	
										<div class="grid-item"><label> ROBOT BRAND<input type="text" class="form_field" name="add_robot_brand" > </label></div>
										<div class="grid-item"><label> ROBOT TYPE<input type="text" class="form_field"  name="add_robot_type" > </label></div>
										<div class="grid-item"><label> seventh Axis(rail)  
											<select id="ax_select" name="add_robot_seventh_axis" class="selector">
												<option value="YES" > YES </option>
												<option value="NO" selected> NO </option>
											</select>
										</label></div>
										<div class="grid-item item2-4"><label> TASKS <input type="text" class="form_field" style="width:450px;" name="add_robot_tasks" > </label></div>
									
										<?php 
										if (isset($_SESSION['AddRobotStatusOK'])){
											echo '<div class="form_success grid-item full_width_item">'.$_SESSION['AddRobotStatusOK'].'</div>';
											/* sleep(5); */
											unset($_SESSION['AddRobotStatusOK']);
											/* header ("Refresh:0"); */
											}
										
										
										if(isset($_SESSION['AddRobotStatusER'])){
											echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['AddRobotStatusER'].'</div>';
											/* sleep(5); */
											unset($_SESSION['AddRobotStatusER']);
											/* header ("Refresh:0"); */
											}
										?>
										<div class="grid-item full_width_item"><input type="submit" value="ADD ROBOT" class="form_button"></div>
									</div>
								</form>
							</div>
							<div id="mode2" class="single_mode_container">
								<form method="post">
									update test
								</form>
							</div>
							<div id="mode3" class="single_mode_container">
								<form method="post">
									<div class="grid-container">
										<div class="grid-item center_item"><label> LP <select name="record_to_remove" class="selector_short">
										<?php
											for($i=0; $i<$row_number; $i++){
												echo '<option value="'.$robots_table[$i]["id"].'">'.($i+1).'</option>';
											}
											echo "</select></label></div>";
											if (isset($_SESSION['RemoveRecordOK'])){
												echo '<div class="form_success grid-item full_width_item">'.$_SESSION['RemoveRecordOK'].'</div>';
												unset($_SESSION['RemoveRecordOK']);
												}

											if(isset($_SESSION['RemoveRecordErr'])){
												echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['RemoveRecordErr'].'</div>';
												unset($_SESSION['RemoveRecordErr']);
												}
										?>
										<div class="grid-item center_item full_width_item"><input type="submit" class="form_button form_short_button" value="REMOVE" /></div>
									</div>
								</form>
							</div>
						</div>
					</div>	
				</section>
			</div>
		</main>
		<div>
			<footer>
				2018 ALPHAROB SP Z O. O. WSZELKIE PRAWA ZASTRZEŻONE
			</footer>
		</div>

</body>

</html> 