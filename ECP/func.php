<?php

function is_loged_check()
{
	if(!isset($_SESSION['is_logged']))
	{
		header('Location:index.php');
		session_unset();
		exit();
	}
}


function connect_to_db_PDO()
{
	$host="localhost";
	$db_user="root";
	$db_password="";
	$db_name="ECP";
	
	try{
	$db= new PDO("mysql:host={$host};dbname={$db_name};charset=utf8",$db_user, $db_password, [
	PDO::ATTR_EMULATE_PREPARES=>false, 
	PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION
	]);
	
	} catch (PDOException $PDO_error)

	{
		echo $PDO_error->getMessage();
		exit('Database error');
	}
	
}
		
function how_many_in_db($table_name, $atribut_name, $variable_name)
{
		require('connect.php');
		
		$query="SELECT * FROM $table_name  WHERE $atribut_name = '$variable_name'";
		try{
		$result=$db->query($query);
		}catch(PDOException $error){
			echo $error->getMessage();
			exit();
		}
		$how_many=$result->rowCount();
		return $how_many;
}

function delete_worker_via_ID($id)
{
	require('connect.php');
	$permissions_quwery=$db->query("SELECT * FROM workers WHERE ID=$id");
	$db_record=$permissions_quwery->fetch();
	
	if ($db_record['permissions']!=3)
	{
		try{
		$sql="DELETE FROM `workers` WHERE ID=$id";
		$db->query($sql);
		}catch (PDOException $error){
		}
		
	if ($db->errorCode()>0){
			$_SESSION['DeleteStatusER']=$error->getMessage();
		}
		else{
			$_SESSION['DeleteStatusOK']="Worker delete success";
		}
	}
	Else
	{
		$_SESSION['DeleteStatusER']="You can't delete this user!";
	}
	
}

function update_worker()
{
	$update_worker= new Worker;
	$update_worker->correct_data_flag=true;
	$update_worker->errors_drscriptions="";
	
	if(strlen($_POST['update_worker_name'])>0)
	{
		$update_worker->set_name($_POST['update_worker_name']);
	}
	
	if(strlen($_POST['update_worker_surname'])>0)
	{
		$update_worker->set_surname($_POST['update_worker_surname']);
	}
	
	if(strlen($_POST['update_worker_email'])>0)
	{
		$update_worker->set_email($_POST['update_worker_email']);
	}
	
	if(strlen($_POST['update_worker_phone'])>0)
	{
		$update_worker->set_phone($_POST['update_worker_phone']);
	}
	
	if(strlen($_POST['update_worker_computer'])>0)
	{
		$update_worker->set_computer_num($_POST['update_worker_computer']);
	}
	
	if(strlen($_POST['update_worker_birthday'])>0)
	{
		$update_worker->set_birthday($_POST['update_worker_birthday']); 
	}
	
	$update_worker->set_permissions($_POST['update_worker_permissions']); 

	
	if ($update_worker->correct_data_flag==true)
	{
		$update_worker->update_worker_in_db($_POST['worker_id_to_update']);
	}
	
}

function add_worker()
	{
		$new_worker= new Worker;
		$new_worker->correct_data_flag=true;
		$new_worker->errors_drscriptions="";
		$new_worker->set_name($_POST['new_worker_name']);
		$new_worker->set_surname($_POST['new_worker_surname']);
		$new_worker->set_email($_POST['new_worker_email']);
		$new_worker->set_login($_POST['new_worker_login']);
		$new_worker->set_password($_POST['new_worker_password'],$_POST['new_worker_password_1']);		
		$new_worker->set_phone($_POST['new_worker_phone']);
		$new_worker->set_computer_num($_POST['new_worker_computer']);
		$new_worker->set_birthday($_POST['new_worker_birthday']);
		$new_worker->set_permissions($_POST['new_worker_permissions']);
		
		if ($new_worker->correct_data_flag==true)
		{
			$new_worker->insert_worker_to_db();
		}
		if ($new_worker->correct_data_flag==false)
		{
			$_SESSION['AddStatusER']=$new_worker->errors_descriptions;
		}
	}
	
function change_password($id, $old_password, $new_password1, $new_password2) 
{
	require('connect.php');
	$worker= new Worker;
	$worker->correct_data_flag=true;
	$worker->errors_drscriptions="";
	$sql_check_per="SELECT * FROM workers WHERE ID=$id";
	try{
		$actual_permissions=$db->query($sql_check_per);
		$db_record=$actual_permissions->fetch();
	}catch (PDOException $error){		
		$_SESSION['PassError']=$error->getMessage();
		exit();
	}
		
	
	if ($db_record['permissions']==3 and $_SESSION['logged_worker_permissions']!=3)
		{
			$worker->correct_data_flag=false;
			$worker->errors_descriptions="You have no permissions to change password for this user";
		}
	elseif ($db_record['permissions']<3 and $_SESSION['logged_worker_permissions']<2)
		{
			$worker->correct_data_flag=false;
			$worker->errors_descriptions="You have no permissions to change password for this user";
		}
	else
		{
			if (password_verify($old_password,$db_record['password'])==True)
			{
				$worker->set_password($new_password1,$new_password2);
				if($worker->correct_data_flag==TRUE)
				{	
					$password=$worker->get_password();
					$sql="UPDATE workers SET password='$password' WHERE ID=$id";
					try{
					$db->query($sql);
					}catch(PDOException $error){	
						$_SESSION['PassError']=$error->getMessage();
						exit();
					}
				}
			}
			else
			{
				$worker->correct_data_flag=false;
				$worker->errors_descriptions="Old password is incorrect";
			}
		}
	
	if ($worker->correct_data_flag==false)
	{
		$_SESSION['PassError']=$worker->errors_descriptions;
	}
	
	if(!isset($_SESSION['PassError'])){
		$_SESSION['PassStatusOK']="Password changed successfully";
	}
}

function create_table($workers_table, $table_title, $db_name, $table_headers, $row_number)
{
$column_number=count($db_name);
$colspan_num=$column_number+1;

//$project_id=$_SESSION['$Project_Id_table'];

echo<<<END
<table class="table">
<tr bgcolor="#555555">
<th colspan="$colspan_num"> $table_title</th>
</tr>
<tr bgcolor="#666666">
END;
echo "<td>LP</td>";
for ($i=0;$i<$column_number; $i++)
{
	echo "<td>$table_headers[$i]</td>";
}

echo"</tr>";
						
for ($j = 0; $j < $row_number; $j++) 
{
echo '<tr class="table_row" bgcolor="#BBBBBB">';
	
	$id=$j+1;
	echo '<td class="table_column">'.$id.'</td>';
	
	for ($i=0;$i<$column_number; $i++)
	{
		$table_val=$workers_table[$j][$db_name[$i]];
		if(isset($workers_table[$j]['color'])){
			echo '<td class="table_column" style="background-color:'.$workers_table[$j]['color'].';">'.$table_val.'</td>';
		}else{
			echo '<td class="table_column">'.$table_val.'</td>';
		}
		
	}
echo '</tr>';
}
echo '</table>';

}



function Projects_Id_aTable()
{
	require('connect.php');
	$sql="SELECT * FROM projects  ORDER BY id DESC";
	$result=$db->query($sql);
	$ProjectID=$result->fetchAll();
	
	for ($i=0; $i<count($ProjectID); $i++ )
	{
		$Project_Id_table[$ProjectID[$i]['id']]=$ProjectID[$i]['project_name'];
	}
	return $Project_Id_table;
}

function Projects_Id_Table()
{
	require('connect.php');
	$sql="SELECT * FROM projects ORDER BY id DESC";
	$result=$db->query($sql);
	$ProjectID=$result->fetchAll();
	
	for ($i=0; $i<count($ProjectID); $i++ )
	{
		$Project_Id_table[$i]=array($ProjectID[$i]['id'],$ProjectID[$i]['project_name']);
	}
	return $Project_Id_table;
}

function Get_first_area_in_project($project){
	require('connect.php');
	$sql="SELECT id FROM areas WHERE project_id='$project' ORDER BY id ASC LIMIT 1";
	$result=$db->query($sql);
	$result_table=$result->fetchAll();
	if (isset($result_table[0]['id'])){
		$Area=$result_table[0]['id'];
	}else{
		$Area=0;
	}
	
	return $Area;
}

function Get_first_robot_in_area($area){
	require('connect.php');
	$sql="SELECT id FROM robots WHERE area_id='$area' ORDER BY id ASC LIMIT 1";
	$result=$db->query($sql);
	$result_table=$result->fetchAll();
	if (isset($result_table[0]['id'])){
		$Robot=$result_table[0]['id'];
	}else{
		$Robot=0;
	}
	
	return $Robot;
}

function Get_last_area_in_project($project){
	require('connect.php');
	$sql="SELECT id FROM areas WHERE project_id='$project' ORDER BY id DESC  LIMIT 1";
	$result=$db->query($sql);
	$result_table=$result->fetchAll();
	if (isset($result_table[0]['id'])){
		$Area=$result_table[0]['id'];
	}else{
		$Area=0;
	}
	
	return $Area;
}

function Get_last_project_id(){
	require('connect.php');
	$sql="SELECT id FROM projects ORDER BY id DESC LIMIT 1";
	$result=$db->query($sql);
	$result_table=$result->fetchAll();
	if (isset($result_table[0]['id'])){
		$Project=$result_table[0]['id'];
	}else{
		$Project=0;
	}
	
	return $Project;
}

//generate assotiative table where id is name 
function Areas_Id_aTable()
{
	require('connect.php');
	$sql="SELECT * FROM areas ORDER BY name ASC";
	$result=$db->query($sql);
	$AreasID=$result->fetchAll();
	
	for ($i=0; $i<count($AreasID); $i++ )
	{
		$Areas_Id_table[$AreasID[$i]['id']]=$AreasID[$i]['name'];
	}
	if (isset($Areas_Id_table)){
		return $Areas_Id_table;
	}else{
		$Areas_Id_table=array();
		return $Areas_Id_table;
	}
	
}

function Areas_Id_Table($selected_project)
{
	require('connect.php');
	$sql="SELECT * FROM areas WHERE project_id='{$selected_project}'  ORDER BY name ASC";
	$result=$db->query($sql);
	$AreasID=$result->fetchAll();
	
	for ($i=0; $i<count($AreasID); $i++ )
	{
		$Areas_Id_table[$i]=array($AreasID[$i]['id'],$AreasID[$i]['name']);
	}
	
	if (isset($Areas_Id_table)){
		return $Areas_Id_table;
	}else{
		$Areas_Id_table=array();
		return $Areas_Id_table;
	}
}

function Robots_Id_aTable()
{
	require('connect.php');
	$sql="SELECT * FROM robots ORDER BY name ASC";
	$result=$db->query($sql);
	$RobotsID=$result->fetchAll();
	
	for ($i=0; $i<count($RobotsID); $i++ )
	{
		$Robots_Id_table[$RobotsID[$i]['id']]=$RobotsID[$i]['name'];
	}
	if (isset($Robots_Id_table)){
		return $Robots_Id_table;
	}else{
		$Robots_Id_table=array();
		return $Robots_Id_table;
	}
	
}

function Robots_Id_Table($area)
{
	require('connect.php');
	$sql="SELECT * FROM robots WHERE area_id='{$area}' ORDER BY name ASC";
	$result=$db->query($sql);
	$RobotsID=$result->fetchAll();
	
	for ($i=0; $i<count($RobotsID); $i++ )
	{
		$Robots_Id_table[$i]=array($RobotsID[$i]['id'],$RobotsID[$i]['name']);
	}
	
	if (isset($Robots_Id_table)){
		return $Robots_Id_table;
	}else{
		$Robots_Id_table=array();
		return $Robots_Id_table;
	}
}

function Workers_Id_Table()
{
	require('connect.php');
	$sql="SELECT * FROM workers ORDER BY id ASC";
	$result=$db->query($sql);
	$WorkersID=$result->fetchAll();
	
	for ($i=0; $i<count($WorkersID); $i++ )
	{
	$Workers_Id_table[$i]=array($WorkersID[$i]['ID'],$WorkersID[$i]['name'], $WorkersID[$i]['surname']);
	}
	
	if (isset($Workers_Id_table)){
		return $Workers_Id_table;
	}else{
		$Workers_Id_table=array();
		return $Workers_Id_table;
	}
}

function Workers_Id_aTable()
{
	require('connect.php');
	$sql="SELECT * FROM workers ORDER BY id ASC";
	$result=$db->query($sql);
	$WorkersID=$result->fetchAll();
	
	for ($i=0; $i<count($WorkersID); $i++ )
	{
	$worker_full_name=$WorkersID[$i]['name']." ".$WorkersID[$i]['surname'];
	$Workers_Id_table[$WorkersID[$i]['ID']]=$worker_full_name;
	}
	
	if (isset($Workers_Id_table)){
		return $Workers_Id_table;
	}else{
		$Workers_Id_table=array();
		return $Workers_Id_table;
	}
}
class Worker
{
	private $id;
	private $name;
	private $surname;
	private $email;
	private $login;
	private $password;
	private $phone;
	private $computer_num;
	private $birthday;
	private $permissions;
	public $correct_data_flag;
	public $errors_descriptions;
	
	public function set_name($name)
	{
		$this->name=$name;
	}	
	
	public function set_surname($surname)
	{
		$this->surname=$surname;
	}
	
	public function set_email($email)
	{
		$correct_email_flag=TRUE;
		$email_checked=filter_var($email, FILTER_SANITIZE_EMAIL);
		if((filter_var($email_checked, FILTER_VALIDATE_EMAIL)==FALSE) || ($email_checked!=$email))
			{
				$correct_email_flag=FALSE;
				$this->correct_data_flag=FALSE;
				$this->errors_descriptions=$this->errors_descriptions." It is not correct email ! ,";
			}
		
		$is_mail_exist=how_many_in_db("workers", "email",$email);
		
		if ($is_mail_exist>0)
			{
				$correct_email_flag=FALSE;
				$this->correct_data_flag=FALSE;
				$this->errors_descriptions=$this->errors_descriptions." E-mail is busy ! ,";
			}
		
		if ($correct_email_flag==TRUE)
			{
				$this->email=$email;
			}
	}
	
	public function set_login($login)
	{
		$correct_login_flag=TRUE;
		$login_checked=filter_var($login, FILTER_SANITIZE_EMAIL);
		if($login_checked!=$login)
		{
			$correct_login_flag=FALSE;
			$this->correct_data_flag=FALSE;
			$this->errors_descriptions=$this->errors_descriptions." It is not correct login ! ,";
		}
		
		$is_login_exist=how_many_in_db("workers", "login",$login);
		if ($is_login_exist>0)
		{
			$correct_login_flag=FALSE;
			$this->correct_data_flag=FALSE;
			$this->errors_descriptions=$this->errors_descriptions." Login is busy ! ,";
		}
		
		If((strlen($login)<3)||(strlen($login)>20))
		{
			$correct_login_flag=FALSE;
			$this->correct_data_flag=FALSE;
			$this->errors_descriptions=$this->errors_descriptions." Login lenght is not correct ! , ";
		}
		
		if ($correct_login_flag==TRUE)
			{
				$this->login=$login;
			}
	
	}
	
	public function set_password($password, $password1)
	{
		$correct_password_flag=TRUE;
		if ($password!=$password1)
		{
			$correct_password_flag=FALSE;
			$this->correct_data_flag=FALSE;
			$this->errors_descriptions=$this->errors_descriptions." Passwords are not the same ! ,";
		}
		
		If((strlen($password)<8)||(strlen($password)>20))
		{
			$correct_password_flag=FALSE;
			$this->correct_data_flag=FALSE;
			$this->errors_descriptions=$this->errors_descriptions." Passwords lenght is not correct ! ";
		}
		
		if ($correct_password_flag==TRUE)
		{
			$this->password=password_hash($password , PASSWORD_DEFAULT);
		}
		
	}
	
	public function set_phone($phone)
		{
			$this->phone=$phone;
		}
	
	public function set_computer_num($computer_num)
		{
			$this->computer_num=$computer_num;
		}
		
	public function set_birthday($birthday)
		{
			$this->birthday=$birthday;
		}
		
	public function set_permissions($permissions)
		{
			if ($permissions!=9){
			$this->permissions=$permissions;}
		}
	
	
	public function insert_worker_to_db()
	{
		require('connect.php');
		$sql="INSERT INTO workers VALUES (NULL, '$this->name', '$this->surname', '$this->email', '$this->login',
		'$this->password', '$this->phone', '$this->computer_num', '$this->birthday', '$this->permissions')";
		try{
		$db->query($sql);
		}catch(PDOException $error){	
			$_SESSION['AddStatusER']=$error->getMessage();
		}
		
		
		if ($db->errorCode()==0){
				$_SESSION['AddStatusOK']="Worker added successfully";
			}

	}
	
	public function get_password()
	{
		return $this->password;
	}
	
	public function update_worker_in_db($id)
	{
		require('connect.php');
		
		$sql="UPDATE workers SET ID=$id ";

		$is_change=false;
		if (isset($this->name))
		{
		$sql=$sql." ,name='$this->name'";
		$is_change=true;
		}
		
		if (isset($this->surname))
		{
		$sql=$sql." ,surname='$this->surname'";
		$is_change=true;
		}
		
		if (isset($this->email))
		{
		$sql=$sql." ,email='$this->email'";
		$is_change=true;
		}
		
		if (isset($this->phone))
		{
		$sql=$sql." ,phone='$this->phone'";
		$is_change=true;
		}
		
		if (isset($this->computer_num))
		{
		$sql=$sql." ,computer_num='$this->computer_num'";
		$is_change=true;
		}
		
		if (isset($this->birthday))
		{
		$sql=$sql." ,birthday='$this->birthday'";
		$is_change=true;
		}
		
		if (isset($this->permissions))
		{
			$sql_check_per="SELECT permissions FROM workers WHERE ID=$id";
			$actual_permissions=$db->query($sql_check_per);
			$db_record=$actual_permissions->fetch();
			if ($db_record['permissions']!=3 and $_SESSION['logged_worker_permissions']>1)
			{
				$sql=$sql." ,permissions='$this->permissions'";
				$is_change=true;
			}
			elseif($db_record['permissions']==3 and $_SESSION['logged_worker_permissions']==3)
			{
				$sql=$sql." ,permissions='$this->permissions'";
				$is_change=true;
			}
			else
			{
				$_SESSION['UpdateStatusER']="You can't change permissions fo this user!";
			}

		}

		$sql=$sql." WHERE ID=$id";
		
		if ($is_change==true)
		{
			try{
			$db->query($sql);
			}catch (PDOException $error){
				$_SESSION['UpdateStatusER']=$error->getMessage();
			}
			
			if(!isset($_SESSION['UpdateStatusER'])){
				$_SESSION['UpdateStatusOK']="Worker updated successfully";
			}

		/* header ("Refresh:0"); */
	}
	

	}
}

function add_project(){
	$new_project= new Project;
	//$new_worker->correct_data_flag=true;
	//$new_worker->errors_drscriptions="";
	$new_project->project_name=$_POST['new_project_name'];
	$new_project->brand=$_POST['new_project_brand'];
	$new_project->software=$_POST['new_project_software'];
	$new_project->RCS=$_POST['new_project_rcs'];
	$new_project->robots_type=$_POST['new_project_robots'];		
	$new_project->takt_time=$_POST['new_project_takt'];
	$new_project->customer=$_POST['new_project_customer'];
	$new_project->range_main_tasks=$_POST['new_project_main'];
	$new_project->range_service_tasks=$_POST['new_project_service'];
	$new_project->range_signals=$_POST['new_project_signals'];
	$new_project->range_download=$_POST['new_project_download'];
	$new_project->range_mrs=$_POST['new_project_mrs'];
	$new_project->range_upload=$_POST['new_project_upload'];
	
	$new_project->insert_project_to_db();

}

function add_area(){
	$new_area= new Area;
	//$new_worker->correct_data_flag=true;
	//$new_worker->errors_drscriptions="";
	$new_area->project_id=$_SESSION['area_selected_project'];
	$new_area->name=$_POST['add_area_name'];
	$new_area->part=$_POST['add_area_part'];
	$new_area->numbers_of_robots=$_POST['add_num_robots'];
	
	$new_area->insert_area_to_db();

}

function add_robot(){
	$new_robot= new Robot;
	//$new_worker->correct_data_flag=true;
	//$new_worker->errors_drscriptions="";
	$new_robot->name=$_POST['add_robot_name'];
	$new_robot->brand=$_POST['add_robot_brand'];
	$new_robot->project_id=$_SESSION['robot_selected_project'];
	$new_robot->area_id=$_SESSION['robot_selected_area'];
	$new_robot->tasks=$_POST['add_robot_tasks'];
	$new_robot->seventh_axis=$_POST['add_robot_seventh_axis'];
	$new_robot->type=$_POST['add_robot_type'];
	
	$new_robot->insert_robot_to_db();

}

function add_ecp(){
	$new_ecp= new ECP_record;
	$new_ecp->set_start_time();
	$new_ecp->set_end_time();
	$new_ecp->set_sum_time();
	$new_ecp->set_worker();
	$new_ecp->place=$_POST['ecp_place'];
	$new_ecp->project=$_SESSION['ecp_selected_project'];
	$new_ecp->area=$_SESSION['ecp_selected_area'];
	$new_ecp->robot=$_SESSION['ecp_selected_robot'];
	$new_ecp->op_time=$_POST['ecp_time'];
	$new_ecp->set_AH();
	$new_ecp->work_type=$_POST['ecp_type_of_work'];
	$new_ecp->description=$_POST['ecp_description'];
	$new_ecp->insert_ecp_to_db();
	
}

class Project{
	private $id;
	public $project_name;
	public $brand;
	public $software;
	public $RCS;
	public $robots_type;
	public $takt_time;
	public $customer;
	public $range_main_tasks;
	public $range_service_tasks;
	public $range_signals;
	public $range_download;
	public $range_mrs;
	public $range_upload;
	
	
	public function insert_project_to_db()
	{
		require('connect.php');
		$this->id="null";
		$sql="INSERT INTO projects VALUES ('$this->id', '$this->project_name', '$this->brand', '$this->software',
		'$this->RCS', '$this->robots_type', '$this->takt_time', '$this->customer', '$this->range_main_tasks',
		'$this->range_service_tasks', '$this->range_signals', '$this->range_download', '$this->range_mrs', '$this->range_upload')";
		try{
		$db->query($sql);
		}catch(PDOException $error){	
			$_SESSION['AddProjectStatusER']=$error->getMessage();
		}
		
		
		if ($db->errorCode()==0){
				$_SESSION['AddProjectStatusOK']="Project added successfully";
			}
	}
	
}

class Area{
	private $id;
	public $project_id;
	public $name;
	public $part;
	public $numbers_of_robots;
	
	public function insert_area_to_db()
	{
		require('connect.php');
		$this->id="null";
		$sql="INSERT INTO areas VALUES ('$this->id', '$this->project_id', '$this->name', '$this->part','$this->numbers_of_robots')";
		//echo $sql;
		//exit();
		try{
		$db->query($sql);
		}catch(PDOException $error){	
			$_SESSION['AddAreaStatusER']=$error->getMessage();
		}
		
		if ($db->errorCode()==0){
				$_SESSION['AddAreaStatusOK']="Area added successfully";
		}
	}
}

class Robot{
	private $id;
	public $name;
	public $brand;
	public $project_id;
	public $area_id;
	public $tasks;
	public $seventh_axis;
	public $type;
	
	public function insert_robot_to_db()
	{
		require('connect.php');
		$this->id="null";
		$sql="INSERT INTO robots VALUES ('$this->id', '$this->name', '$this->brand', '$this->project_id','$this->area_id','$this->tasks','$this->seventh_axis','$this->type')";

		try{
		$db->query($sql);
		}catch(PDOException $error){	
			$_SESSION['AddRobotStatusER']=$error->getMessage();
		}
		
		if ($db->errorCode()==0){
				$_SESSION['AddRobotStatusOK']="Robot added successfully";
		}
	}
	
	
}

class ECP_record{ 
	private $id;
	private $worker;
	private $start_time;
	private $end_time;
	private $sum_time;
	public $place;
	public $project;
	public $area;
	public $robot;
	public $op_time;
	public $additional_hour;
	public $work_type;
	public $decription;
	private $start_timestamp;
	private $end_timestamp;
	
	
	public function set_start_time(){
		$Starttime= $_POST['ecp_date']." ".$_POST['ecp_starttime'];
		$newStarttime= new DateTime($Starttime);
		$this->start_time=$Starttime;
		/* $this->start_time=$newStarttime->format('U'); */
	}
	public function set_end_time(){
		$Endtime= $_POST['ecp_date']." ".$_POST['ecp_endtime'];
		$newEndtime= new DateTime($Endtime);
		$this->end_time=$Endtime;
		/* $this->end_time=$newEndtime->format('U'); */

				
	}
	public function set_worker(){
		$this->worker=$_SESSION['logged_worker_id'];
	}
	
	public function set_sum_time(){
		$Starttime= $_POST['ecp_date']." ".$_POST['ecp_starttime'];
		$TS_Start= new DateTime($Starttime);
		$Endtime= $_POST['ecp_date']." ".$_POST['ecp_endtime'];
		$TS_End= new DateTime($Endtime);
		$sum=($TS_End->format('U')-$TS_Start->format('U'))/60;
		$this->sum_time=$sum;

	}
	
	public function set_AH(){
		if(isset($_POST['ecp_additionalhours'])){
			$this->additional_hour=1;
		}else {
			$this->additional_hour=0;
		}
	}
	
	public function insert_ecp_to_db()
	{
		require('connect.php');
		$this->id="null";
		$sql="INSERT INTO ecp VALUES ('$this->id', '$this->worker', '$this->start_time', '$this->end_time','$this->sum_time','$this->place',
		'$this->project','$this->area','$this->robot','$this->op_time','$this->additional_hour','$this->work_type','$this->description')";

		try{
		$db->query($sql);
		}catch(PDOException $error){	
			$_SESSION['AddECPStatusER']=$error->getMessage();
		}
		
		if ($db->errorCode()==0){
				$_SESSION['AddECPStatusOK']="ECP record added successfully";
		}
	}
	
}

function remove_record_in_db($record_ID, $TableName){
	require('connect.php');
	$sql="DELETE FROM $TableName WHERE id='$record_ID'";
	//echo $sql;
	try{
		$db->query($sql);
	}catch(PDOException $error){	
		$_SESSION['RemoveRecordErr']=$error->getMessage();
	}
	if ($db->errorCode()==0){
		$_SESSION['RemoveRecordOK']="Record deleted successfully";
	}
}

function import_csv(){
require('connect.php');
$row = 0;
$dummy_ecp_table=array();
$uchwyt = fopen ("bazy/ECP_DOLINSKI.csv","r");
while (($data=fgetcsv($uchwyt, 5000, ";"))!==false)
{
	$num = count($data);
    $row++;
	$dummy_ecp_table[$row]=$data;
}
fclose ($uchwyt);

for($i=1; $i<=count($dummy_ecp_table); $i++){
	if($dummy_ecp_table[$i][6]!=""){
		$worker= 7;
		$starttime= $dummy_ecp_table[$i][0]." ".$dummy_ecp_table[$i][7];
		$endtime= $dummy_ecp_table[$i][0]." ".$dummy_ecp_table[$i][8];
		$TS_Start= new DateTime($starttime);
		$TS_End= new DateTime($endtime);
		$sum=($TS_End->format('U')-$TS_Start->format('U'))/60;
		if($dummy_ecp_table[$i][5]==""){
			$place= "Gliwice";
		}else{
			$place= $dummy_ecp_table[$i][5];
		}
		$project= $dummy_ecp_table[$i][3];
		$cell= $dummy_ecp_table[$i][4];
		$description= $dummy_ecp_table[$i][6];
		$sql="INSERT INTO old_ecp VALUES (null,'$worker', '$starttime', '$endtime', '$sum','$place','$project','$cell','$description')";
		//echo $sql;
		//echo "</br>";
		//$db->query($sql);
	}
}
}

function unset_worker_mode()
{
if (isset($_SESSION['worker_mode'])) unset($_SESSION['worker_mode']);	
}

function day_eng_to_pl_conv($eng_day)
{
	switch($eng_day){
	case "Monday":
		return "PN";
		break;
	case "Tuesday":
		return "WT";
		break;
	case "Wednesday":
		return "ŚR";
		break;
	case "Thursday":
		return "CZ";
		break;
	case "Friday":
		return "PT";
		break;
	case "Saturday":
		return "SO";
		break;
	case "Sunday":
		return "ND";
	break;}
}

function how_many_day_in_range($first_date, $second_date)
{
	$first_date= new DateTime ($first_date);
	$second_date= new DateTime ($second_date);
	if ($first_date<=$second_date){
		return (floor(($second_date->format('U')-$first_date->format('U'))/86400))+1; 
	}
	else return 0;
}

function min_to_hour_conv($min){
	if($min%60!=0)
	{
		$hour=floor($min/60)."h".($min%60)."min";
	}else{
		$hour=floor($min/60)."h";
	}
	
	return $hour;
}

function check_holiday($date){
$holidays=array(
	"2019-01-01"=>"Nowy Rok", 
	"2019-01-06"=>"Święto Trzech Króli", 
	"2019-04-21"=>"Niedziela Wielkanocna",
	"2019-04-22"=>"Poniedziałek Wielkanocny",
	"2019-05-01"=>"Święto Pracy",
	"2019-05-03"=>"Święto Konstytucji 3 maja",
	"2019-06-09"=>"Zesłanie Ducha Świętego",
	"2019-06-20"=>"Boże Ciało",
	"2019-08-15"=>"Święto Wojska Polskiego",
	"2019-11-01"=>"Wszystkich Świętych",
	"2019-11-11"=>"Święto Niepodległości",
	"2019-12-25"=>"Boże Narodzenie",
	"2019-12-26"=>"Boże Narodzenie",
	
	"2020-01-01"=>"Nowy Rok", 
	"2020-01-06"=>"Święto Trzech Króli", 
	"2020-04-12"=>"Niedziela Wielkanocna",
	"2020-04-13"=>"Poniedziałek Wielkanocny",
	"2020-05-01"=>"Święto Pracy",
	"2020-05-03"=>"Święto Konstytucji 3 maja",
	"2020-05-31"=>"Zesłanie Ducha Świętego",
	"2020-06-11"=>"Boże Ciało",
	"2020-08-15"=>"Święto Wojska Polskiego",
	"2020-11-01"=>"Wszystkich Świętych",
	"2020-11-11"=>"Święto Niepodległości",
	"2020-12-25"=>"Boże Narodzenie",
	"2020-12-26"=>"Boże Narodzenie"
	);
	
	if (isset($holidays[$date])){
		return $holidays[$date];
	}else{
		return false;
	}
}

function get_overtime_num($sumTime, $date){
	$newDate= new DateTime($date);
	$day=$newDate->format('l');
	if ($day=="Saturday" or $day=="Sunday" or check_holiday($date)!=false){
		return $sumTime*2;
	}else{
		return ($sumTime-480)*1.5;
	}
}

function get_type_of_work_table(){
	$table=array("Sprawdzanie symulacji","Przygotowanie robota do OLP","Przejazdy- główne programy","Przejazdy- serwisowe programy",
	"Clash","Sygnały/actiony","Standard w programach","Download","Przygotowanie celek do wysłania","Dokumentacja",
	"MRS/SOP","Zmiany symulacyjne","Wgrywanie na fabryce","Inne","URLOP","Wolne","L4","Święto");
	return $table;
}


function create_summary_table($table, $projectID)
{
require('connect.php');
$column_number=6;
$colspan_num=6;
$project_name=$table[0]['project_name'];
$db_name=array('project_name','brand','software','rcs','robots_type','takt_time');
$table_headers=array('Project name','Brand','Software','RCS','Robots type','Takt time');
$sum_day_array=array();
$row_number=0;
$workers_table=Workers_Id_Table();

$sql="SELECT * FROM areas WHERE project_id=".$projectID;
$num_of_areas=how_many_in_db1($sql);
$sql="SELECT * FROM robots WHERE project_id=".$projectID." ORDER BY name ASC;";
$num_of_robots=how_many_in_db1($sql);
$sql="SELECT DISTINCT robot_id FROM ecp WHERE project_id=".$projectID.";";
$num_of_teached_robots=how_many_in_db1($sql);
$sql_old_ecp="SELECT * FROM old_ecp WHERE project LIKE '%".$project_name."%'";
$sql_new_ecp="SELECT DISTINCT start_time FROM ecp WHERE project_id=".$projectID;
$NOR_old=how_many_in_db1($sql_old_ecp);
$NOR_new=how_many_in_db1($sql_new_ecp);
$NOR_sum=$NOR_old+$NOR_new;

	if($NOR_sum>0){
			$sum_day_array[$row_number][0]="SUMARY";
			$sum_day_array[$row_number][1]=$NOR_sum;
			$sql="SELECT SUM(operation_time) FROM ecp WHERE project_id=$projectID";
			if($result=$db->query($sql)){
				$sum_time_for_project=$result->fetchAll();
				$sum_day_array[$row_number][2]=$sum_time_for_project[0][0];
			}
			$row_number++;
	}
	
for ($i=0; $i<count($workers_table); $i++){
	$sql_old_ecp="SELECT * FROM old_ecp WHERE project LIKE '%".$project_name."%' and worker=".$workers_table[$i][0]."";
	$sql_new_ecp="SELECT DISTINCT start_time FROM ecp WHERE project_id=".$projectID." and worker_id=".$workers_table[$i][0]."";
	$NOR_old=how_many_in_db1($sql_old_ecp);
	$NOR_new=how_many_in_db1($sql_new_ecp);
	$NOR_sum=$NOR_old+$NOR_new;
	
	if($NOR_sum>0){
		$sum_day_array[$row_number][0]=$workers_table[$i][1];
		$sum_day_array[$row_number][1]=$NOR_sum;
			$sql="SELECT SUM(operation_time) FROM ecp WHERE project_id=$projectID AND worker_id=".$workers_table[$i][0];
			if($result=$db->query($sql)){
				$sum_time_for_worker=$result->fetchAll();
				$sum_day_array[$row_number][2]=$sum_time_for_worker[0][0];
				//$sum_day_array[$row_number][2]=999;
			}
			$row_number++;
	}
		
}



echo '<div class="summary_graph" >';
$module_jq_name="summary_module1";
echo "<div class='graph_header' onclick='toggle_summary($module_jq_name)'>";
echo "<h3>Project $project_name overview</h3>";
echo "</div>";
echo '<div id="summary_module1" style="display:none;" class="summary_module">';
echo<<<END
<table class="table">
<tr bgcolor="#666666">
END;
for ($i=0;$i<$column_number; $i++)
{
	echo "<td>$table_headers[$i]</td>";
}

echo '</tr>';
echo '<tr class="table_row" bgcolor="#BBBBBB">';
	for ($i=0;$i<$column_number; $i++)
	{
		$table_val=$table[0][$db_name[$i]];
		echo '<td class="table_column">'.$table_val.'</td>';
		
	}
echo '</tr>';
echo<<<END
<tr></tr>
<tr bgcolor="#555555">
<th colspan="$colspan_num">Project range</th>
</tr>
<tr bgcolor="#666666">
END;
$db_name=array('main_tasks','service_tasks','signals','downloads','mrs','upload_on_plant');
$table_headers=array('Main Tasks','Service Tasks','Signals','Download','MRS','Upload');
$table_title="Project range";
for ($i=0;$i<$column_number; $i++)
{
	echo "<td>$table_headers[$i]</td>";
}

echo '</tr>';
echo '<tr class="table_row" bgcolor="#BBBBBB">';
	for ($i=0;$i<$column_number; $i++)
	{
		$table_val=$table[0][$db_name[$i]];
		if($table_val==1){
			echo '<td class="table_column">YES</td>';
		}else{
			echo '<td class="table_column">NO</td>';	
		}
		
	}

echo '</tr>';
echo '<tr></tr>';
echo '</table><br/></br>';
echo '</div>';
echo '</div>';

echo '<div class="summary_graph" >';
$module_jq_name="summary_module2";
echo "<div class='graph_header' onclick='toggle_summary($module_jq_name)'>";
echo "<h3>Areas/Robots table</h3>";
echo "</div>";
echo '<div id="summary_module2" style="display:none;" class="summary_module">';
echo '<table class="table">';
echo "<tr bgcolor='#666666'>";
echo "<th colspan=3>Areas ($num_of_areas)</th>";
echo "<th colspan=3>Robots ($num_of_robots)</th>";

	$Areas_id_name_table=Areas_Id_Table($projectID);	
	
	for($i=0;$i<count($Areas_id_name_table);$i++){
		$Robots_id_name_table=Robots_Id_Table($Areas_id_name_table[$i][0]);
		$robots_count=count($Robots_id_name_table);
			echo '<tr class="table_row" bgcolor="#BBBBBB"> ';
			echo "<td rowspan=$robots_count colspan=3>".$Areas_id_name_table[$i][1]."</td>";
				for($j=0;$j<$robots_count;$j++){
					echo "<td class='table_row' colspan=3 bgcolor='#BBBBBB'>".$Robots_id_name_table[$j][1]."</td>";
				echo '</tr>'; 
				}

		}
		echo '<tr></tr>'; 
echo "</tr>";
echo '</table>';
echo "</div>";
echo "</div>";


echo '<div class="summary_graph">';
$module_jq_name="summary_module3";
echo "<div class='graph_header' onclick='toggle_summary($module_jq_name)'>";
echo "<h3>Sum of days on project</h3>";
echo '</div>';
echo '<div id="summary_module3" style="display:none;" class="summary_module">';
echo '<table class="no_border_table">';
echo '<tr style="background-color:rgba(155, 155, 155,0.2);">';
echo '<td style="padding:5px;">Worker name</td>';
echo '<td style="padding:5px;">Sum of day</td>';
echo '<td style="padding:5px;">Sum of hour</td>';
echo '</tr>';
echo '<tr></tr>';
for ($i=0; $i<count($sum_day_array);$i++){
	echo '<tr>';
	echo '<td style="padding:5px;">'.$sum_day_array[$i][0].'</td>';
	echo '<td style="padding:5px;">'.$sum_day_array[$i][1].'</td>';
	echo '<td style="padding:5px;">'.$sum_day_array[$i][2].'</td>';
	echo '</tr>';	
}
echo '<tr></tr>';
echo '<tr bgcolor="#555555">';
echo '<td></td><td>Days/robot</td><td> Hours/robot</td>';
echo '</tr>';
echo '<tr>';
if ($num_of_teached_robots!=0 && $sum_day_array[0][1]!=0 && $sum_day_array[0][2]!=0){
	//echo '<td colspan=3>'.($sum_day_array[0][1]/$num_of_robots).'</td>';
	echo '<td></td><td>'.round($sum_day_array[0][1]/$num_of_teached_robots,1)."</td><td>".round($sum_day_array[0][2]/$num_of_teached_robots,1).'</td>';
}elseif($num_of_teached_robots!=0 && $sum_day_array[0][1]!=0 && $sum_day_array[0][2]=0){
	echo '<td>'.round($sum_day_array[0][1]/$num_of_teached_robots,1).'</td>';
	//echo '<td colspan=3>'.($sum_day_array[0][1]/$num_of_robots)." / ".($sum_day_array[0][2]/$num_of_robots).'</td>';
}else{
	echo '<td colspan=3>No data</td>';	
}
echo '</tr>';	
echo '</table><br/></br>';
echo '</div>';
echo '</div>';


workers_time_tasks_array($projectID);
}

function workers_time_tasks_array($projectID){
	
	require('connect.php');
	$sql="SELECT DISTINCT worker_id FROM ecp WHERE project_id=$projectID";
	$work_type=get_type_of_work_table();
	$work_sum_table=array();
	$workers_array=Workers_Id_aTable();
	$num_of_work_type=14;
	$sum_time_on_project=0;
	if($result=$db->query($sql))
	{
		$num_workers=$result->rowCount();
		$workers_table=$result->fetchAll();
		$work_sum_table[0][0]="";
		$work_sum_table[$num_workers+1][0]="SUM";
		$work_sum_table[$num_workers+2][0]="%";
		for ($i=0;$i<$num_workers;$i++){
				$work_sum_table[$i+1][0]=$workers_array[$workers_table[$i][0]];
			for($j=0; $j<$num_of_work_type;$j++){
				$sql="SELECT SUM(operation_time) FROM ecp WHERE project_id=$projectID AND worker_id=".$workers_table[$i][0]." AND type_of_work='".$work_type[$j]."'";
				if($result=$db->query($sql)){
					$work_sum_table[0][$j+1]=$work_type[$j];
					$sum_time_for_task=$result->fetchAll();
					$work_sum_table[$i+1][$j+1]=$sum_time_for_task[0][0];
					$sum_time_on_project=$sum_time_on_project+$sum_time_for_task[0][0];
					if(isset($work_sum_table[$num_workers+1][$j+1])){
					$work_sum_table[$num_workers+1][$j+1]=$work_sum_table[$num_workers+1][$j+1]+$sum_time_for_task[0][0];
					}else{
						$work_sum_table[$num_workers+1][$j+1]=$sum_time_for_task[0][0];
					}
					if($sum_time_on_project!=0){
					$work_sum_table[$num_workers+2][$j+1]=round(($work_sum_table[$num_workers+1][$j+1]/$sum_time_on_project)*100,1);
					}
				}
			}
		}
	}
	
	if ($num_workers>0){
		for($i=1; $i<=$num_of_work_type; $i++ ){
			if ($sum_time_on_project!=0){
				$work_sum_table[$num_workers+2][$i]=round(($work_sum_table[$num_workers+1][$i]/$sum_time_on_project)*100,1);
			}else{
				$work_sum_table[$num_workers+2][$i]=0;
			}
		}
		echo '<div class="summary_graph">';
		$module_jq_name="summary_module4";
		echo '<div class="graph_header" onclick="toggle_summary('.$module_jq_name.')"><h3>Hours per tasks</h3></div>';
		echo '<div id="summary_module4" class="summary_module">';
		echo '<table class="no_border_table" >';
		echo '<tr></tr>';
			for($j=0; $j<=$num_of_work_type;$j++){
					echo '<tr>';
				for ($i=0;$i<=$num_workers+1;$i++){	
					if($j%2!=0){
						echo '<td>';
					}else{
						echo '<td style="background-color:rgba(255, 255, 255,0.05);">';
					}
					echo $work_sum_table[$i][$j];
					echo "</td>";
						
			}
			echo "</tr>";
		}
		echo '<tr>';
		echo '<th colspan='.($num_workers+2).' style="background-color:rgba(153, 204, 0,0.2);" >Sum time on project [h]</th>';
		echo '</tr>';
		echo '<tr>';
		echo '<th colspan='.($num_workers+2).' style="background-color:rgba(153, 204, 0,0.2);">'.$sum_time_on_project.'</th>';
		echo '</tr>';
		echo '</table><br/></br>';
		echo '</div>';
		echo '</div>';
	}
	$row_num=0;
	for($i=1; $i<=$num_of_work_type;$i++){
		if(isset($work_sum_table[0][$i]) &&  $work_sum_table[$num_workers+2][$i]>0){
			$percentage_table0[$row_num]=$work_sum_table[0][$i];
			$percentage_table1[$row_num]=$work_sum_table[$num_workers+2][$i];
			$row_num++;
		}
	}

	

	if(isset($percentage_table1)){
		array_multisort($percentage_table1,SORT_DESC,  $percentage_table0);
		create_graph($percentage_table0,$percentage_table1 );
	}
}

function create_graph($table_name, $table_percentage){
echo "<div class='summary_graph'>";
$module_jq_name="summary_module5";
echo "<div class='graph_header' onclick='toggle_summary($module_jq_name)'>";
echo "<h3>Summary graph</h3>";
echo "</div>";
echo "<div id='summary_module5' class='summary_module graph_container '>";
echo "100%";
 for($i=0; $i<count($table_name);$i++){
	echo "<div class='single_graph_comment'>".$table_name[$i].": ".$table_percentage[$i]."% </div>";
	echo "<div class='single_graph_bar' style='width:".(6.5*$table_percentage[$i])."px;'></div>";
	/* echo "<div style='clear:both'></div>"; */
 }
echo "</div>";
echo "</div>";
}

function how_many_in_db1($sql){
	require('connect.php');

		try{
		$result=$db->query($sql);
		}catch(PDOException $error){	
			$_SESSION['HowManyStatusER']=$error->getMessage();
		}
		if ($db->errorCode()==0){
			$_SESSION['HowManyStatusOK']="Worker added successfully";
			$how_many=$result->rowCount();
			return $how_many;
		}else{
			$how_many=0;
			return $how_many;
		}
}

function createEcpFile($ecp_full_table){
	$directory=$_POST['ecpFilePath']."\\".$_POST['ecpFileName'].".csv";
	$ecp_new_order=array();
	$ecp_row_count=count($ecp_full_table);
	$handle = fopen($directory, "a");
	
for ($i=0; $i<$ecp_row_count; $i++){
	$ecp_new_order[$i]= $ecp_full_table[$ecp_row_count-$i-1];
	fwrite($handle, $ecp_new_order[$i]['KW'].";".$ecp_new_order[$i]['date'].";".$ecp_new_order[$i]['day'].";".
	$ecp_new_order[$i]['place'].";".$ecp_new_order[$i]['project_name'].";".$ecp_new_order[$i]['area_name'].";".
	$ecp_new_order[$i]['robot_name'].";".$ecp_new_order[$i]['description'].";".$ecp_new_order[$i]['start_time1'].";".
	$ecp_new_order[$i]['end_time1'].";".$ecp_new_order[$i]['sum_time'].";"."\r\n");
}		
	fclose($handle);
}