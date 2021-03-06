<?php
global $global_array;
$global_array = array();

function set_global_var($name, $var)
{

	global $global_array;
	$global_array[$name] = $var;
}

function get_global_var($name)
{
	global $global_array;
	return $global_array[$name];
}

class Flusso
{
	private $nome;
	private $site_view;
	private $area;
	private $sub_area;
	private $page;
	private $callback_elaborazione;
	
	function __construct($name, $funzione_elab)
	{
		//controllo se questo flusso è giù memorizzato nella sessione
		if (isset($_SESSION[md5($name)]))
		{
			//recupero informazioni in memoria
			//leggo l'oggetto in sessione con l'api di riflessione	e ottengo tutte le proprietà
			$object = unserialize($_SESSION[md5($name)]);
			$object_reflected = new ReflectionObject($object);
			$this_reflected =  new ReflectionObject($this);
			$array_properties  = $object_reflected->getProperties();

			
			//scorro tutte le proprietà per carpirne i valori
			foreach ($array_properties as $property)
			{
				try
				{
					//leggo il valore della proprietà in sessione e lo imposto a quella attuale
					$method = $object_reflected->getMethod("get_" . $property->getName());					
					$this_method=$this_reflected->getMethod("set_" . $property->getName());
					$this_method->invoke($this, $method->invoke($object));
				}
				catch (ReflectionException $e){}
			}
			
			$_SESSION[md5($this->get_nome())] = serialize($this);
		}
		else
		{
			//inizializzazione classe
			$this->set_nome($name);
			$this->set_site_view("none");
			$this->set_area("none");
			$this->set_callback_elaborazione($funzione_elab);
			
			$_SESSION[md5($this->get_nome())] = serialize($this);
		}

	} 
	
	function __destruct()
	{
		$_SESSION[md5($this->get_nome())] = serialize($this);
	}
	/**
	*
	*     NOME
	*/
	function get_nome()
	{
		return $this->nome;
	}
	
	function set_nome($name)
	{
		$this->nome = $name;
	}
	
	/**
	*
	*      SITE VIEW
	*/
	function get_site_view()
	{
		if (isset($_SESSION['site_view']))
			{
			$this->site_view = $_SESSION['site_view'];
			return $_SESSION['site_view'];
			}
		else
			return $this->site_view;
	}
	
	function set_site_view($view)
	{
		$this->site_view = $view;
	}
	
	/**
	*
	*     AREA
	*/
	function get_area()
	{
	//	if (isset($_REQUEST['area']))
	//		{
	//		$this->area = $_REQUEST['area'];
	//		return $_REQUEST['area'];
	//		}
	//	else
			return $this->area;
	}
	
	function set_area($area)
	{
		$this->area = $area;
	}		
		
	/**
	*
	*     SUBAREA
	*/
	function get_sub_area()
	{
	//	if (isset($_REQUEST['sub_area']))
	//		{
	//		$this->sub_area = $_REQUEST['sub_area'];
	//		return $_REQUEST['sub_area'];
	//		}
	//	else
			return $this->sub_area;
	}
	
	function set_sub_area($area)
	{
		$this->sub_area = $area;
	}		
		
	/**
	*
	*     CALLBACK ELABORAZIONE
	*/
	function get_callback_elaborazione()
	{
		return $this->callback_elaborazione;
	}
	
	function set_callback_elaborazione($nome_funzione)
	{
		$this->callback_elaborazione = $nome_funzione;
	}		
	
	function first_time_run()
	{
		if ( (strcmp($this->site_view, "none")==0) && (strcmp($this->area, "none")==0) )
			return true;
		else
			return false;
	}
	
	function get_action(){
		//nessuna variabile REQUEST deve iniziare con "action_" a meno che non sia una richiesta di azione
		if(isset($_REQUEST["action"]))
		return $_REQUEST["action"];
		else
		{
			if (isset($_REQUEST))
			{
				$req_keys=array_keys($_REQUEST);
				foreach ($req_keys as $req_value)
				{
					if(preg_match("/action_(.*)/",$req_value,$action))
					{
						return $action[1];
						break;
					}
				}
			}

		}

		return "";
	}


	function elaborates()
	{
		$ret_object;

		$count = 0;

		//la prima volta prelevo l'azione dell'utente
		$action = $this->get_action();
		$area_original = $this->get_area();
		$sub_area_original = $this->get_sub_area();
		//inizio il ciclo per percorrere le varie aree
		do {
			

			//se però è stato passato via GET/POST il valore dell'area allora la visualizzo
			if (isset($_REQUEST["area"]) && $count==0) //spostare dentro il get_area stando attenti al n. di ciclo == 0
			{
				//via GET/POST non posso cambiare la site view
				if ($this->first_time_run())
				{
					$init = inizializza();
					$this->set_site_view($init->site_view);
				}
				$this->set_area( $_REQUEST["area"] );
				if (isset($_REQUEST["subarea"]))
    				$this->set_sub_area( @$_REQUEST["subarea"]);
                else    				 
    				$this->set_sub_area("default");     				
			}	

				//prelevo i vecchi valori della sub area
				$site_view_old = $this->get_site_view();
				$area_old = $this->get_area(); 
				$sub_area_old =  $this->get_sub_area();
				$action_old = $action;
				//imposto i valori correnti
				$parameter = array(
									0 => $this->get_site_view(),
									1 => $this->get_area(),
									2 => $this->get_sub_area(),
									3 => $action,
									4 => $this
				);

			//chiamo la funzione utente
			$ret_object = call_user_func_array($this->callback_elaborazione, $parameter );
			//print_r($ret_object);
			//se restituisco un'area aggiorno i valori e effettuo del debug
			if ($ret_object instanceof ReturnedArea)
			{
				//aggiornamento valori
				$this->set_site_view( $ret_object->site_view );
				$this->set_area( $ret_object->area );
				$this->set_sub_area( $ret_object->sub_area );
				$action = $ret_object->action;
				
				add_to_debug("Ciclo n. $count","Ingresso:> $site_view_old/$area_old/$sub_area_old ---[$action_old]--&gt;" . " Uscita:>" . $this->get_site_view()  . "/" .   $this->get_area() . "/" . $this->get_sub_area() . "/action=" . $action);				
	
			}
			else if ($ret_object instanceof ReturnedPage)
			{
				add_to_debug("Ciclo n. $count","Ingresso:> $site_view_old/$area_old/$sub_area_old" . " Uscita:> page=" . $ret_object->page); 	
			}

			//tengo traccia dei cicli fatti dal flusso
			$count ++;
		}
		while
		( 
			$count < 5 && //per evitare cicli infiniti
			($ret_object instanceof ReturnedObject) && //deve essere istanza di un'oggetto restituibile
			!($ret_object instanceof ReturnedPage || $ret_object instanceof ReturnedAjax)  //ma non deve essere una pagina da visualizzare
			//(($ret_object->site_view != $site_view_old) || $ret_object->area != $area_old) //l'area deve essere differente
		);
		
		
		if ($ret_object == FALSE)
			{
				die("FALSE - L'applicazione ha compiuto una operazione non valida");
			}
		else
			{
			
				if ($ret_object instanceof ReturnedAjax)
				{
					$this->set_area( $area_original );
					$this->set_sub_area( $sub_area_original );
					echo $ret_object->code;
					die(); //dopo aver stampato la risposta non deve essere eseguito null'altro
				}
				else
				{
					//restituisco il nome del file di presentazione
					add_to_debug("Classe restituita", $ret_object);
					add_to_debug("Numero di cicli", $count);
					
					//gestisco le aree di default, in modo da non espandere troppo al'alberatura
					$sub_area_array = explode("/",$this->get_sub_area());
					
					$returned_page=$this->get_site_view();

					$maxi=count($sub_area_array);
					
					//ciclo all'indietro fino a trovare una sub area diversa da default
					while ($maxi > 0)
					{
						if (strcmp($sub_area_array[$maxi - 1],"default") != 0 ) 
						{
							break;		
						}						
						$maxi--;
					}
					
					//se tutte le aree/subaree erano di default allora verifico che anche l'area lo sia
					if ($maxi < 1)
					{
						if (strcmp($this->get_area(),"default") != 0)
							$returned_page .= "/" . $this->get_area();
					}
					else
					{
						$returned_page .= "/" . $this->get_area();
						for ($i=0; $i< $maxi;$i++)
						{
								$returned_page .= "/". $sub_area_array[$i];
						}
					}
					$returned_page .= "/". $ret_object->page;
					add_to_debug("Pagina cercata", $returned_page);
					
					if (file_exists("presentation/" . $returned_page))
					{						
						$this->page =  "presentation/" . $returned_page; 
						$p=$ret_object->parameters;
						$p["site_view"]=$this->get_site_view();
						if (isset($_SESSION["user_detail"]))
						{
							$p["user"]=(object)$_SESSION["user_detail"];
						}
						include "presentation/" . $returned_page;					
						
					}
					else
					{
						add_to_debug("Pagina non trovata", "presentation/" . $returned_page);
					}
					if (PRODUCTION == FALSE)
					{
					    echo "<pre id=\"debug\">";
					    echo "SESSION ";
					    print_r($_SESSION);
					    print_debug();	
					    echo "REQUEST ";		
					    print_r($_REQUEST);
					    echo "</pre>";
					}
					return;
				}
			}
	}
		
}

?>
