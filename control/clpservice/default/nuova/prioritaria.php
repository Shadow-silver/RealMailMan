<?php/*** $action variabile che contiene il nome dell'area corrente**/	add_to_debug("Azione",  $action);	switch ($action)	{		case "insert":			$rows = array();			for($i=0; $i<count($_REQUEST["prot"]); $i++)			{				$rows[$i]=array("pieghi"=>$_REQUEST["pieghi"][$i],				                "prot"=>$_REQUEST["prot"][$i],				                "categoria"=>$_REQUEST["categoria"][$i]);			}						$time = str_replace('/', '-', $_SESSION["date"]);			$time=strtotime($time);			if (verify_nonce($_REQUEST["nonce"]))			{				$id=get_next_distinta_id(date("Y"),$time);				$result = insert_new_prioritaria($id,$time,$rows);			}			return new ReturnedArea("clpservice","default","distinte");		case "back":				verify_nonce($_REQUEST["nonce"]);				return new ReturnedArea("clpservice","default","distinte"); 					default:				$year=date("Y");				if (isset($_SESSION["latest_year_viewed"]))			{			    $year = $_SESSION["latest_year_viewed"];			}						return new ReturnedPage("nuova_prioritaria.php", array("date"=>$_SESSION["date"],"latest_year_viewed"=>$year));	}?>