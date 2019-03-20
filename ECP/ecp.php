<?php
 	session_start();
	include 'func.php';
	require_once('connect.php');
	is_loged_check();
	$db_table_name="ecp";
	$workers_table=Workers_Id_Table();
	
	if(!isset($_SESSION['ecp_date_filter1'])){
		$_SESSION['ecp_date_filter1']=date("Y-m")."-01 00:00:00";
		$_SESSION['ecp_date_filter2']=date("Y-m-d")." 23:59:59";
		$_SESSION['ecp_date_filter3']=date("Y-m")."-01";
		$_SESSION['ecp_date_filter4']=date("Y-m-d");
		$_SESSION['ecp_worker']=$_SESSION['logged_worker_id'];
	}
	
	if(isset($_POST['ecp_date_filter1'])){
		$_SESSION['ecp_date_filter1']=$_POST['ecp_date_filter1']." 00:00:00";
		
		if ($_POST['ecp_date_filter2']>=$_POST['ecp_date_filter1']){
			$_SESSION['ecp_date_filter2']=$_POST['ecp_date_filter2']." 23:59:59";
		}else{
			$_SESSION['ecp_date_filter2']=$_POST['ecp_date_filter1']." 23:59:59";
		}
		
		$_SESSION['ecp_date_filter3']=$_POST['ecp_date_filter1'];
		
		if ($_POST['ecp_date_filter2']>=$_POST['ecp_date_filter1']){
			$_SESSION['ecp_date_filter4']=$_POST['ecp_date_filter2'];
		}else{
			$_SESSION['ecp_date_filter4']=$_POST['ecp_date_filter1'];
		}
		$_SESSION['ecp_worker']=$_POST['ecp_worker'];
	}
	
	
	if (isset($_POST['ecp_date'])){
		add_ecp();
		$_SESSION['ecp_mode']=1;
		
		if ($_POST['ecp_date']>$_SESSION['ecp_date_filter2']){
			$_SESSION['ecp_date_filter2']=$_POST['ecp_date']." 23:59:59";
			$_SESSION['ecp_date_filter4']=$_POST['ecp_date'];
		}
		
	}
	
	if (isset($_POST['record_to_remove'])){
		remove_record_in_db($_POST['record_to_remove'], "ecp");
		unset($_POST['record_to_remove']);
	}
	
	if (!isset($_SESSION['ecp_selected_project'])){
		$_SESSION['ecp_selected_project']=Get_last_project_id();
		$_SESSION['ecp_selected_area']=Get_first_area_in_project($_SESSION['ecp_selected_project']);
		$_SESSION['ecp_selected_robot']=Get_first_robot_in_area($_SESSION['ecp_selected_area']);
	}
	
	if(isset($_POST['ecp_selected_robot'])){
		$_SESSION['ecp_selected_robot']=$_POST['ecp_selected_robot'];
		unset($_POST['ecp_selected_robot']);
	}
	
	if(isset($_POST['ecp_selected_area'])){
		$_SESSION['ecp_selected_area']=$_POST['ecp_selected_area'];
		unset($_POST['ecp_selected_area']);
		$_SESSION['ecp_selected_robot']=Get_first_robot_in_area($_SESSION['ecp_selected_area']);
	}
	
	if(isset($_POST['ecp_selected_project'])){
		$_SESSION['ecp_selected_project']=$_POST['ecp_selected_project'];
		unset($_POST['ecp_selected_project']);
		$_SESSION['ecp_selected_area']=Get_first_area_in_project($_SESSION['ecp_selected_project']);
		$_SESSION['ecp_selected_robot']=Get_first_robot_in_area($_SESSION['ecp_selected_area']);
	}

	$Project_id_name_atable=Projects_Id_aTable();
	$project_id_name_table=Projects_Id_Table();
	$Areas_id_name_table=Areas_Id_Table($_SESSION['ecp_selected_project']);	
	$Areas_id_name_atable=Areas_Id_aTable();
	$Robots_id_name_table=Robots_Id_Table($_SESSION['ecp_selected_area']);	
	$Robots_id_name_atable=Robots_Id_aTable();
	
	$sql="SELECT*FROM {$db_table_name} WHERE start_time>='{$_SESSION['ecp_date_filter1']}' 
	and start_time<='{$_SESSION['ecp_date_filter2']}' and worker_id='{$_SESSION['ecp_worker']}' ORDER BY start_time DESC";
	
	if($result=$db->query($sql))
	{
		$num_ecp_records=$result->rowCount();
		$ecp_table=$result->fetchAll();
	} 
	
	$days_in_ecp_range=how_many_day_in_range($_SESSION['ecp_date_filter3'], $_SESSION['ecp_date_filter4']);
	
	for($i=0; $i<$num_ecp_records; $i++){
		
		$ecp_table[$i]['project_name']=$Project_id_name_atable[$ecp_table[$i]['project_id']];
		$ecp_table[$i]['area_name']=$Areas_id_name_atable[$ecp_table[$i]['area_id']];
		$ecp_table[$i]['robot_name']=$Robots_id_name_atable[$ecp_table[$i]['robot_id']];
		$StartTime= new DateTime ($ecp_table[$i]['start_time']);
		$EndTime= new DateTime ($ecp_table[$i]['end_time']);
		$ecp_table[$i]['date']=$StartTime->format('Y-m-d');
		$ecp_table[$i]['start_time1']=$StartTime->format('H:i');
		$ecp_table[$i]['end_time1']=$EndTime->format('H:i');
		$ecp_table[$i]['day']= day_eng_to_pl_conv($EndTime->format('l'));
		
		if($ecp_table[$i]['additional_hour']==0){
			$ecp_table[$i]['AH']="NO";	
		}else{
			$ecp_table[$i]['AH']="YES";	
		}
	}
	$NumberOfRecords=0;
	$NumberOfWorkingDay=0;
	$workingHours=0;
	$overtime=0;
	$SumAdditionalHours=0;
	
	for($i=0; $i<$days_in_ecp_range; $i++){
		$TempStartTime= new DateTime($_SESSION['ecp_date_filter4'],new DateTimeZone('Europe/Warsaw'));
		$TempStartTime->modify('-'.$i.' day');
		$TempTime=$TempStartTime->format('Y-m-d');
		$tempDay=$TempStartTime->format('l');
		$is_in_ecp=false;
		
		//echo $TempTime;
		
		for($j=0; $j<$num_ecp_records; $j++){
			if($TempTime==$ecp_table[$j]['date']){
				$is_in_ecp=True;
				$ecp_full_table[$NumberOfRecords]['day']=$ecp_table[$j]['day'];
				$ecp_full_table[$NumberOfRecords]['date']=$ecp_table[$j]['date'];
				$ecp_full_table[$NumberOfRecords]['KW']=$TempStartTime->format('W');
				$ecp_full_table[$NumberOfRecords]['start_time1']=$ecp_table[$j]['start_time1'];
				$ecp_full_table[$NumberOfRecords]['end_time1']=$ecp_table[$j]['end_time1'];
				$ecp_full_table[$NumberOfRecords]['sum_time']=$ecp_table[$j]['sum_time'];
				$ecp_full_table[$NumberOfRecords]['place']=$ecp_table[$j]['place'];
				$ecp_full_table[$NumberOfRecords]['project_name']=$ecp_table[$j]['project_name'];
				$ecp_full_table[$NumberOfRecords]['area_name']=$ecp_table[$j]['area_name'];
				$ecp_full_table[$NumberOfRecords]['robot_name']=$ecp_table[$j]['robot_name'];
				$ecp_full_table[$NumberOfRecords]['operation_time']=$ecp_table[$j]['operation_time'];
				$ecp_full_table[$NumberOfRecords]['AH']=$ecp_table[$j]['AH'];
				$ecp_full_table[$NumberOfRecords]['type_of_work']=$ecp_table[$j]['type_of_work'];
				$ecp_full_table[$NumberOfRecords]['description']=$ecp_table[$j]['description'];
				$ecp_full_table[$NumberOfRecords]['id']=$ecp_table[$j]['id'];
				if($ecp_full_table[$NumberOfRecords]['type_of_work']=="URLOP"){
					$ecp_full_table[$NumberOfRecords]['color']="rgba(236, 214, 169, 0.8)";
				}elseif($ecp_full_table[$NumberOfRecords]['day']=="ND"){
					$ecp_full_table[$NumberOfRecords]['color']="rgba(255, 173, 167, 0.4)";
				}elseif($ecp_full_table[$NumberOfRecords]['day']=="SO"){
					$ecp_full_table[$NumberOfRecords]['color']="rgba(154, 240, 255, 0.4)";
				}
				
				$NumberOfRecords++;
			}
		}
		
		if($is_in_ecp==False){
			if (check_holiday($TempTime)!=false){
				$ecp_full_table[$NumberOfRecords]['description']=check_holiday($TempTime);
				$ecp_full_table[$NumberOfRecords]['day']= day_eng_to_pl_conv($TempStartTime->format('l'));
				$ecp_full_table[$NumberOfRecords]['date']=$TempStartTime->format('Y-m-d');
				$ecp_full_table[$NumberOfRecords]['KW']=$TempStartTime->format('W');
				$ecp_full_table[$NumberOfRecords]['color']="rgba(255, 173, 167, 0.4)";
			}else{
				$ecp_full_table[$NumberOfRecords]['description']="";
				$ecp_full_table[$NumberOfRecords]['day']= day_eng_to_pl_conv($TempStartTime->format('l'));
				$ecp_full_table[$NumberOfRecords]['date']=$TempStartTime->format('Y-m-d');
				$ecp_full_table[$NumberOfRecords]['KW']=$TempStartTime->format('W');
			}
				$ecp_full_table[$NumberOfRecords]['start_time1']="";
				$ecp_full_table[$NumberOfRecords]['end_time1']="";
				$ecp_full_table[$NumberOfRecords]['sum_time']="";
				$ecp_full_table[$NumberOfRecords]['place']="";
				$ecp_full_table[$NumberOfRecords]['project_name']="";
				$ecp_full_table[$NumberOfRecords]['area_name']="";
				$ecp_full_table[$NumberOfRecords]['robot_name']="";
				$ecp_full_table[$NumberOfRecords]['operation_time']="";
				$ecp_full_table[$NumberOfRecords]['AH']="";
				$ecp_full_table[$NumberOfRecords]['type_of_work']="";
				if($ecp_full_table[$NumberOfRecords]['day']=="ND"){
					$ecp_full_table[$NumberOfRecords]['color']="rgba(255, 173, 167, 0.4)";
				}elseif($ecp_full_table[$NumberOfRecords]['day']=="SO"){
					$ecp_full_table[$NumberOfRecords]['color']="rgba(154, 240, 255, 0.4)";
				}
			$NumberOfRecords++;
		}
		
		if($tempDay!="Sunday" && $tempDay!="Saturday" && check_holiday($TempTime)==false) $NumberOfWorkingDay++;
			
	}
	
	for($i=0; $i<$NumberOfRecords; $i++){
		if($i==0){
		$last_date=$ecp_full_table[$i]['date'];}
		if($i>0 && $ecp_full_table[$i]['date']==$last_date){
			$ecp_full_table[$i]['date']="";
			$ecp_full_table[$i]['day']="";
			$ecp_full_table[$i]['start_time1']="";
			$ecp_full_table[$i]['end_time1']="";
			$ecp_full_table[$i]['sum_time']="";
			$ecp_full_table[$i]['place']="";
			$ecp_full_table[$i]['KW']="";
		}else{
			$last_date=$ecp_full_table[$i]['date'];
		}
		
		if (is_numeric($ecp_full_table[$i]['sum_time'])) 
		{
			$workingHours=$workingHours+$ecp_full_table[$i]['sum_time'];
			$ecp_full_table[$i]['sum_time_string']=min_to_hour_conv($ecp_full_table[$i]['sum_time']);
			$overtime=$overtime+get_overtime_num($ecp_full_table[$i]['sum_time'],$ecp_full_table[$i]['date']);
			$ecp_full_table[$i]['overtime']=get_overtime_num($ecp_full_table[$i]['sum_time'],$ecp_full_table[$i]['date']);
			$ecp_full_table[$i]['overtime_string']=min_to_hour_conv($ecp_full_table[$i]['overtime']);
			$date=new DateTime($ecp_full_table[$i]['date']); 
			
		}else{
			$ecp_full_table[$i]['sum_time_string']="";
			$ecp_full_table[$i]['overtime']="";
			$ecp_full_table[$i]['overtime_string']="";
		}
		
		if($ecp_full_table[$i]['AH']=="YES") $SumAdditionalHours=$SumAdditionalHours+$ecp_full_table[$i]['operation_time'];
			
	}
	
	$sumMin=$workingHours-($NumberOfWorkingDay*8*60);
	$sumHours=min_to_hour_conv($sumMin);
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
<script src="jquery-3.3.1.min.js"></script>
<script src="scripts.js"></script>
<script>StickyNavigation();</script>
</head>


<body onload='onload_module(3,"ecp_mode_number"), set_form_inputs()'>	
		<header>
			<h1 id="logo"> 
				<span id="logo1">alpha</span><span id="logo2">rob </span><span>offline robots programing</span>
			</h1>
		</header>
		
		<div class="nav">
			<nav> 
				<ul class="navigation">
					<li> <a href="workers.php" >Workers</a></li>
					<li> <a href="projects.php" >Projects</a></li>
					<li> <a href="areas.php" >Areas</a></li>
					<li> <a href="robots.php" >Robots</a></li>
					<li style="background-color:#424242";"> <a href="ecp.php" >ECP</a></li>
					<li> <a href="logout.php"> Log Out: <?php echo $_SESSION['logged_worker_name']." ".$_SESSION['logged_worker_surname']; ?> </a></li>
				</ul>
			</nav>
		</div>
		
		<main>
			<div id="container">
				<section>
					<div class="form_row">
						<form method="post">
							<div class="grid-container">
							 	<div class="grid-item"><label><select class="selector" name="ecp_worker" onchange="this.form.submit()">
								<?php
								if($_SESSION['logged_worker_permissions']>1){
								for ($i=0;$i<count($workers_table); $i++){
									if ($_SESSION['ecp_worker']==$workers_table[$i][0]){
										echo '<option value="'.$workers_table[$i][0].'" selected>'.$workers_table[$i][1]." ".$workers_table[$i][2].'</option>';
									}else{
										echo '<option value="'.$workers_table[$i][0].'">'.$workers_table[$i][1]." ".$workers_table[$i][2].'</option>';
									}
								}
								}else{
									echo '<option value="'.$_SESSION['logged_worker_id'].'" selected>'.$_SESSION['logged_worker_name']." ".$_SESSION['logged_worker_surname'].'</option>';
								}
								
								?>
							</select></label></div>
							<div class="grid-item"><label><input type="date" class="form_field" name="ecp_date_filter1" onchange="this.form.submit()"  value="<?php echo $_SESSION['ecp_date_filter3']?>"></label></div>
							<div class="grid-item"><label><input type="date" class="form_field" name="ecp_date_filter2" onchange="this.form.submit()" value="<?php echo $_SESSION['ecp_date_filter4']?>"></label></div>
							</div>
						</form>
						<div style="margin:20px;">
							<table class="table">
								<tr bgcolor="#555555">
									<td class="table_column"> Sum of hours </td>
									<td class="table_column"> Overtime </td>
									<td class="table_column"> Additional hours </td>
								</tr>
								<tr style="background-color:#BBBBBB; color:black";>
									<td class="table_column"><?php echo $sumHours; ?> </td>
									<td class="table_column"><?php echo min_to_hour_conv($overtime); ?> </td>
									<td class="table_column"><?php echo $SumAdditionalHours; ?> </td>
								</tr>
							</table>
						</div>
					</div>
				</section>
				<section>
				<?php
					$db_name=array('KW','date','day','start_time1','end_time1','sum_time_string','overtime_string',
										'place','project_name','area_name','robot_name','operation_time','AH','type_of_work','description');
					$table_headers=array('KW','DATE','DAY','START','END','SUM','OT','PLACE','PROJECT','AREA','ROBOT','TIME','AH','WORK','DESCRIPTION');
					$row_number=$NumberOfRecords;
					$table_title="ECP";

					$table_array=create_table($ecp_full_table, $table_title, $db_name, $table_headers, $row_number);
				
				?>
				</section>
				<section>
					<div>
						<ul class="mode_navigation">
							<li onclick='module_nav_click(1,3,"ecp_mode_number")' id="mode_butt_1">Add to ECP</li>
							<li onclick='module_nav_click(2,3,"ecp_mode_number")' id="mode_butt_2">Remove record</li>
							<li onclick='module_nav_click(3,3,"ecp_mode_number")' id="mode_butt_3">Generate ECP</li>
						</ul>
					</div>
				<div  class="form_container" >
					<div id="mode1" class="single_mode_container">
						<form method="post">
							<div class="grid-container">
								<form method="post" class="ecp_filter_form">
									<div class="grid-item"><label> PROJECT <select name="ecp_selected_project" class="selector" onchange="this.form.submit()">
										<?php
											for($i=0; $i<count($project_id_name_table); $i++)
											{
												if( isset($_SESSION['ecp_selected_project']) && $_SESSION['ecp_selected_project']==$project_id_name_table[$i][0]){
													echo '<option value="'.$project_id_name_table[$i][0].'" selected>'.$project_id_name_table[$i][1].'</option>';
												}elseif (!isset($_SESSION['ecp_selected_project']) && $i==0){
													echo '<option value="'.$project_id_name_table[$i][0].'" selected>'.$project_id_name_table[$i][1].'</option>';
												}else{
													echo '<option value="'.$project_id_name_table[$i][0].'">'.$project_id_name_table[$i][1].'</option>';
												}
											}
										
										?>
									</select></label></div>
								</form>
								<form method="post" class="ecp_filter_form">
									<div class="grid-item"><label> AREA <select name="ecp_selected_area" class="selector" onchange="this.form.submit()">
									
										<?php
											for($i=0; $i<count($Areas_id_name_table); $i++)
											{
												if( isset($_SESSION['ecp_selected_area']) && $_SESSION['ecp_selected_area']==$Areas_id_name_table[$i][0]){
													echo '<option value="'.$Areas_id_name_table[$i][0].'" selected>'.$Areas_id_name_table[$i][1].'</option>';
												}else{
													echo '<option value="'.$Areas_id_name_table[$i][0].'" >'.$Areas_id_name_table[$i][1].'</option>';
												}
											}
											
											if( isset($_SESSION['ecp_selected_area']) && $_SESSION['ecp_selected_area']==999){
												echo '<option value=999 selected >ALL</option>';
											}else{
												echo '<option value=999>ALL</option>';
											}
										?>
									</select></label></div>
								</form > 
								<form method="post" class="ecp_filter_form">
									<div class="grid-item"><label> ROBOT <select name="ecp_selected_robot" class="selector" onchange="this.form.submit()">
										<?php
											for($i=0; $i<count($Robots_id_name_table); $i++)
											{
												if( isset($_SESSION['ecp_selected_robot']) && $_SESSION['ecp_selected_robot']==$Robots_id_name_table[$i][0]){
													echo '<option value="'.$Robots_id_name_table[$i][0].'" selected>'.$Robots_id_name_table[$i][1].'</option>';
												}else{
													echo '<option value="'.$Robots_id_name_table[$i][0].'" >'.$Robots_id_name_table[$i][1].'</option>';
												}
											}
											if( isset($_SESSION['ecp_selected_robot']) && $_SESSION['ecp_selected_robot']==999){
												echo '<option value=999 selected >ALL</option>';
											}else{
												echo '<option value=999>ALL</option>';
											}
										?>
									</select></label></div>
								</form>
							</div>	
							<form method="post">
								<div class="grid-container4item">
									<div class="grid-item"><label> DATE <input id="ecp_form_date" type="date" class="form_field_mid" name="ecp_date" 
											value="<?php echo date('Y-m-d')?>" onchange='change_form_data("date")'> </label></div>	
									<div class="grid-item"><label> START TIME <input id="ecp_form_start_time" type="time"  class="form_field_mid" name="ecp_starttime" 
											value="06:00" onchange='change_form_data("start_time")'> </label></div>
									<div class="grid-item"><label> END TIME <input id="ecp_form_end_time" type="time" class="form_field_mid" name="ecp_endtime" 
											value="14:00" onchange='change_form_data("end_time")'> </label></div>
									<div class="grid-item"><label> PLACE <input id="ecp_form_place" type="text" class="form_field_mid" name="ecp_place" 
											value="Gliwice" onchange='change_form_data("place")'> </label></div>
								</div>
								<div class="grid-container3item">
									<div class="grid-item"><label> TIME <input id="ecp_form_time" type="number" class="form_field" name="ecp_time" 
											onchange='change_form_data("time")' required> </label></div>
									<div class="grid-item"><label> WORK <select id="ecp_form_work" name="ecp_type_of_work" class="selector" onchange='change_form_data("work")'>
										<?php $type_of_work_table=get_type_of_work_table();
										for($i=0; $i<count($type_of_work_table); $i++){
											echo "<option value='$type_of_work_table[$i]'>$type_of_work_table[$i]</option>";
										}?>
									</select></label></div>
									<div class="grid-item"><label> AH <input id="ecp_form_ah" type="checkbox"class="form_field_checkbox" name="ecp_additionalhours" value=1 
											onchange='change_form_data("ah")' > </label></div>
								</div>
								<div class="grid-container">
									<div class="grid-item full_width_item">
										<label> DESC. <input id="ecp_form_desc" type="text" class="form_field" name="ecp_description" style="width:600px;" 
												onchange='change_form_data("desc")'> </label>
									</div>
									<?php 
										if (isset($_SESSION['AddECPStatusOK'])){
											echo '<div class="form_success grid-item full_width_item">'.$_SESSION['AddECPStatusOK'].'</div>';
											unset($_SESSION['AddECPStatusOK']);
											}
										if(isset($_SESSION['AddECPStatusER'])){
											echo '<div class="form_error_com grid-item full_width_item">'.$_SESSION['AddECPStatusER'].'</div>';
											unset($_SESSION['AddECPStatusER']);
											}
									?>
									<div class="grid-item full_width_item"><input type="submit" class="form_button" value="ADD TO EPC" id="add_button" onclick="clear_form_inputs()"></div>
								</div>
						</form>
					</div>
					<div id="mode2" class="single_mode_container">
						<form method="post">
							<div class="grid-container">
								<div class="grid-item full_width_item"><label> LP <select name="record_to_remove" class="selector_short">
								<?php
									for($i=0; $i<$row_number; $i++){
										echo '<option value="'.$ecp_full_table[$i]["id"].'">'.($i+1).'</option>';
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
								<div class="grid-item full_width_item"><input type="submit" class="form_button form_short_button" value="REMOVE"></div>
							</div>
						</form>
					</div>					
					<div id="mode3" class="single_mode_container">
					<form method="post">
						<div class="grid-container">
							<div class="grid-item center_item"> <label>File path: <input type="text" name="ecpFilePath" class="form_field"/></label><br/> </div>
							<div class="grid-item center_item"><label>File name: <input type="text" name="ecpFileName" class="form_field"/></label><br/> </div>
							<div class="grid-item center_item"><input type="submit" value="GENERATE FILE" class="form_button form_mid_button" ></div>
						</div>
					</form>
					<!-- <input type="file" webkitdirectory directory multiple/> -->
						<?php
							if(isset($_POST['ecpFilePath'])){

							}
							/*$db_name=array('KW','date','day','place','project_name','area_name','robot_name','description','start_time1','end_time1','sum_time');
							$table_headers=array('KW','DATE','DAY','PLACE','PROJECT','AREA','ROBOT','DESCRIPTION','START','END','SUM');
							$row_number=$NumberOfRecords;
							$table_title="ECP";
							$table_array=create_table($ecp_new_order, $table_title, $db_name, $table_headers, $row_number);*/
							
							
						?>
					</div>
				</div>
			</div>
		</main>
		<footer>
			2018 ALPHAROB SP Z O. O. WSZELKIE PRAWA ZASTRZEŻONE
		</footer>

</body>

</html> 