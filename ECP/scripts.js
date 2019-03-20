function module_nav_click(mode_number, number_of_modes, session_var_name){
	
	for (let i=1; i<=number_of_modes; i++){
		var elementID="mode"+i;
		if(mode_number==i){
			var jQID="#mode"+i;
			var temp=$(jQID).css('display');
			if($(jQID).css('display')=="block"){
				$(document).ready(function(){$(jQID).slideUp(200);});
				document.getElementById("mode_butt_"+i).style.backgroundColor="";
				sessionStorage.setItem(session_var_name, "");
			}else{
				$(document).ready(function(){$(jQID).slideDown(200);});
				document.getElementById("mode_butt_"+i).style.backgroundColor="#888888";
				sessionStorage.setItem(session_var_name, mode_number);
			}
		}else{
			document.getElementById(elementID).style.display="none";
			document.getElementById("mode_butt_"+i).style="";
		}
	}
	
	var table=document.getElementById("table_container");
	if(table){
	if (table.style.display=="none" && sessionStorage.getItem("project_mode_number")!=4)$(document).ready(function(){$("#table_container").slideDown(200);});
	}

	return;
}

function module_nav_hide(number_of_modes){
		for (let i=1; i<=number_of_modes; i++){
		var elementID="#mode"+i;
			$(elementID).css('display')=="none";
			document.getElementById("mode_butt_"+i).style.backgroundColor="#595959";
	}
}

 function onload_module(number_of_modes, session_var_name){
	 
	var mode=sessionStorage.getItem(session_var_name);
	
	if(mode!=""){
		for (let i=1; i<=number_of_modes; i++){
			var elementID="mode"+i;
			if(mode==i){
				document.getElementById(elementID).style.display="block";
				document.getElementById("mode_butt_"+i).style.backgroundColor="#888888";
			}else{
				document.getElementById(elementID).style.display="none";
				document.getElementById("mode_butt_"+i).style="";
			}
		} 
	}else{
		for (let i=1; i<=number_of_modes; i++){
			var elementID="mode"+i;
			document.getElementById(elementID).style.display="none";
			document.getElementById("mode_butt_"+i).style="";
		} 
	}	
} 

function toggle_table(){
	var table=document.getElementById("table_container");
	var form=document.getElementById("mode4")
	if (sessionStorage.getItem("project_mode_number")==4){
		$(document).ready(function(){$("#table_container").slideUp(200);});
		$(document).ready(function(){$("#mode4").slideDown(200);});
		
	}else{
		$(document).ready(function(){$("#table_container").slideDown(200);});
		$(document).ready(function(){$("#mode4").slideUp(200);});
		/* sessionStorage.setItem("project_mode_number",0); */
	}
}

function hide_table(){
	var table=document.getElementById("table_container");
	if (sessionStorage.getItem("project_mode_number")==4){
		table.style.display="none";
	}else{
		table.style.display="block";
	}
}

function toggle_summary(module_name){
	$(document).ready(function(){$(module_name).toggle(200);});
}

function StickyNavigation()
{
	$(document).ready(function() {
	var NavY = $('.nav').offset().top;
	 
	var stickyNav = function(){
	var ScrollY = $(window).scrollTop();
		  
	if (ScrollY > NavY) { 
		$('.nav').addClass('sticky');
	} else {
		$('.nav').removeClass('sticky'); 
	}
	};
	 
	stickyNav();
	 
	$(window).scroll(function() {
		stickyNav();
	});
	});
}

function change_form_data(form_data_name){
	switch(form_data_name){
		case "date":
			var data=document.getElementById("ecp_form_date").value;
			sessionStorage.setItem('form_data', data);
			break;
			
		case "start_time":
			var data=document.getElementById("ecp_form_start_time").value;
			sessionStorage.setItem('form_start_time', data);
			break;
		
		case "end_time":
			var data=document.getElementById("ecp_form_end_time").value;
			sessionStorage.setItem('form_end_time', data);
			break;
			
		case "place":
			var data=document.getElementById("ecp_form_place").value;
			sessionStorage.setItem('form_place', data);
			break;
			
		case "time":
			var data=document.getElementById("ecp_form_time").value;
			sessionStorage.setItem('form_time', data);
			break;
		
		case "work":
			var data=document.getElementById("ecp_form_work").value;
			sessionStorage.setItem('form_work', data);
			break;
		
		case "ah":
			if (document.getElementById("ecp_form_ah").checked==true){
				sessionStorage.setItem('form_ah', 1);
			}else{
				sessionStorage.setItem('form_ah', 0);	
			}
			break;
		
		case "desc":
			var data=document.getElementById("ecp_form_desc").value;
			sessionStorage.setItem('form_desc', data);
			break;
	}
}

function set_form_inputs(){
	if(sessionStorage.getItem('form_data')){
		document.getElementById("ecp_form_date").value=sessionStorage.getItem('form_data');
	}
	
	if(sessionStorage.getItem('form_start_time')){
		document.getElementById("ecp_form_start_time").value=sessionStorage.getItem('form_start_time');
	}
	
	if(sessionStorage.getItem('form_end_time')){
		document.getElementById("ecp_form_end_time").value=sessionStorage.getItem('form_end_time');
	}
	
	if(sessionStorage.getItem('form_place')){
		document.getElementById("ecp_form_place").value=sessionStorage.getItem('form_place');
	}
	
	if(sessionStorage.getItem('form_time')){
		document.getElementById("ecp_form_time").value=sessionStorage.getItem('form_time');
	}
	
	if(sessionStorage.getItem('form_work')){
		document.getElementById("ecp_form_work").value=sessionStorage.getItem('form_work');
	}
	
	if(sessionStorage.getItem('form_ah')){
		if(sessionStorage.getItem('form_ah')==1){
			document.getElementById("ecp_form_ah").checked=true;
		}else{
			document.getElementById("ecp_form_ah").checked=false;
		}
	}

	if(sessionStorage.getItem('form_desc')){
		document.getElementById("ecp_form_desc").value=sessionStorage.getItem('form_desc');
	}
	
}

function clear_form_inputs(){
	sessionStorage.setItem('form_ah', 0);
	//document.getElementById("ecp_form_ah").checked=false;
	sessionStorage.setItem('form_desc', '');
	sessionStorage.setItem('form_time', '');
	//document.getElementById("ecp_form_desc").value="";
	//document.getElementById("ecp_form_time").value="";
}
