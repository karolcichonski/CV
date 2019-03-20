<?php
 	session_start();
	include 'func.php';
	require_once('connect.php');
	is_loged_check();
	unset_worker_mode();
	$db_table_name="old_ecp";
	
	$workers_table=Workers_Id_Table();
	
	if(!isset($_SESSION['old_ecp_date_filter1'])){
		$_SESSION['old_ecp_date_filter1']="2018-12-01 00:00:00";
		$_SESSION['old_ecp_date_filter2']="2018-12-31 00:00:00";
		$_SESSION['old_ecp_date_filter3']="2018-12-01";
		$_SESSION['old_ecp_date_filter4']="2018-12-31";
		$_SESSION['old_ecp_worker']=$_SESSION['logged_worker_id'];
		
	}
	
	if(isset($_POST['old_ecp_date_filter1'])){
		$_SESSION['old_ecp_date_filter1']=$_POST['old_ecp_date_filter1']." 00:00:00";
		$_SESSION['old_ecp_date_filter2']=$_POST['old_ecp_date_filter2']." 23:59:59";
		$_SESSION['old_ecp_date_filter3']=$_POST['old_ecp_date_filter1'];
		$_SESSION['old_ecp_date_filter4']=$_POST['old_ecp_date_filter2'];
		$_SESSION['old_ecp_worker']=$_POST['old_ecp_worker'];
	}
		
	$sql="SELECT*FROM {$db_table_name} WHERE start_time>='{$_SESSION['old_ecp_date_filter1']}' 
				and start_time<='{$_SESSION['old_ecp_date_filter2']}' and worker='{$_SESSION['old_ecp_worker']}'  ORDER BY start_time DESC";
	
	if($result=$db->query($sql))
	{
		$num_ecp_records=$result->rowCount();
		$ecp_table=$result->fetchAll();
	} 
	
	for($i=0; $i<$num_ecp_records; $i++){
		$StartTime= new DateTime ($ecp_table[$i]['start_time']);
		$EndTime= new DateTime ($ecp_table[$i]['end_time']);
		$ecp_table[$i]['date']=$StartTime->format('Y-m-d');
		$ecp_table[$i]['start_time1']=$StartTime->format('H:i');
		$ecp_table[$i]['end_time1']=$EndTime->format('H:i');
		$ecp_table[$i]['day']= day_eng_to_pl_conv($EndTime->format('l'));
		if($ecp_table[$i]['day']=="ND"){
			$ecp_table[$i]['color']="rgba(255, 173, 167, 0.4)";
		}elseif($ecp_table[$i]['day']=="SO"){
			$ecp_table[$i]['color']="rgba(154, 240, 255, 0.4)";
		}
		if(strripos($ecp_table[$i]['description'],'rlop')){
			$ecp_table[$i]['color']="rgba(236, 214, 169, 0.8)";
		}
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
<script src="jquery-3.3.1.min.js"></script>
<script src="scripts.js"></script>
<script>StickyNavigation();</script>
</head>

<body>
		<header>
			<h1 id="logo"> 
				<font color="#cc3333" size="7">alpha </font>
				<font color="lightgray"  size="7">rob  </font> 
				<font color="white"  size="7"> offline robots programing </font>
			</h1>
		</header>
		
		<div class="nav">
			<nav> 
				<ul class="navigation">
					<li> <a href="workers.php" >Workers</a></li>
					<li> <a href="projects.php" >Projects</a></li>
					<li> <a href="areas.php" >Areas</a></li>
					<li> <a href="robots.php" >Robots</a></li>
					<li> <a href="ecp.php" >ECP</a></li>
					<li style="background-color:#424242";"> <a href="old_ecp.php" >OLD ECP</a></li>
					<li> <a href="logout.php"> Log Out: <?php echo $_SESSION['logged_worker_name']." ".$_SESSION['logged_worker_surname']; ?> </a></li>
				</ul>
			</nav>
		</div>
		
		<main>
			<div id="container">
				<section>
					<div class="form_row">
						<form method="post">
							 <label><select class="selector" name="old_ecp_worker" onchange="this.form.submit()">
								<?php
								if($_SESSION['logged_worker_permissions']>1){
								for ($i=0;$i<count($workers_table); $i++){
									if ($_SESSION['old_ecp_worker']==$workers_table[$i][0]){
										echo '<option value="'.$workers_table[$i][0].'" selected>'.$workers_table[$i][1]." ".$workers_table[$i][2].'</option>';
									}else{
										echo '<option value="'.$workers_table[$i][0].'">'.$workers_table[$i][1]." ".$workers_table[$i][2].'</option>';
									}
								}
								}else{
									echo '<option value="'.$_SESSION['logged_worker_id'].'" selected>'.$_SESSION['logged_worker_name']." ".$_SESSION['logged_worker_surname'].'</option>';
								}
								
								?>
							</select></label>
								<label><input type="date" class="form_field" name="old_ecp_date_filter1" onchange="this.form.submit()"  value="<?php echo $_SESSION['old_ecp_date_filter3']?>"></label>
								<label><input type="date" class="form_field" name="old_ecp_date_filter2" onchange="this.form.submit()" value="<?php echo $_SESSION['old_ecp_date_filter4']?>"></label>
							
						</form>
					</div>
				</section>
				<section>
					<?php
						$db_name=array('day','date','start_time1','end_time1','sum_time','place','project','cell','description');
						$table_headers=array('DAY','DATE','START','END','SUM','PLACE','PROJECT','CELL','DESCRIPTION');
						$row_number=$num_ecp_records;
						$table_title="ECP";

						$table_array=create_table($ecp_table, $table_title, $db_name, $table_headers, $row_number);
						//import_csv();
					?>
				</section>
			</div>
		</main>
		<footer>
			2018 ALPHAROB SP Z O. O. WSZELKIE PRAWA ZASTRZEŻONE
		</footer>

</body>

</html> 