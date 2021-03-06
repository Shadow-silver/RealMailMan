 <?php

	//Classe astratta utilizzata per le info restituite da una funzione di esecuzione
	abstract class ReturnedObject
	{
		public $name_class = __CLASS__; //giusto per riempirla
	}
	
	
	//Classe da utilizzare per restituire una pagina da visualizzare
	class ReturnedPage extends ReturnedObject
	{
		public $page;
		public $parameters;
		public $name_class = __CLASS__;
		function __construct($page, $parameters=array())
		{
			$this->page = $page;
			$this->parameters = $parameters;
		}
	}
	
	class ReturnedArea extends ReturnedObject
	{
		public $sub_area;
		public $area;
		public $site_view;		
		public $action;
		
		public $name_class = __CLASS__;
		function __construct($site_view, $area, $sub_area = FALSE, $action="")
		{
			$this->area = $area;
			$this->site_view = $site_view;

			//nel caso in cui la sotto area non è specificata, viene preso lo stesso nome dell'area
			if ($sub_area == FALSE)
				$this->sub_area = $area;
			else
				$this->sub_area = $sub_area;
			$this->action = $action;
		}
	}
	
	//Classe da utilizzare per una richiesta di tipo Ajax
	class ReturnedAjax extends ReturnedObject
	{
		public $name_class = __CLASS__;
		public $code;
		
		function __construct($mode="json",$code)
		{
			if (strcmp($mode,"json") == 0)
				$this->code = json_encode($code);
			else
				$this->code = $code;
		}
	}

 
	
?>
