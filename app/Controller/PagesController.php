<?php
App::uses('AppController', 'Controller');

App::uses('HttpSocket', 'Network/Http');


class PagesController extends AppController {


	public $uses = array();


	public function index() {
		$files = scandir(dirname(APP)."/example-orders");
		$testFiles = array();
		foreach($files as $file){
			if(strtolower(pathinfo($file, PATHINFO_EXTENSION))=="json"){
				$testFiles[] = $file;
			}
		}
		$this->set("files",$testFiles);
		
		if(isset($_POST['file'])){
			$jsonContent = file_get_contents("../../example-orders/".$_POST['file']);
			$order = json_decode($jsonContent,true);

	        $link =  Router::url(array(
					'controller' => 'orders',
					'action' => 'discount'
				),true).".json";

			$data = array();
	        $httpSocket = new HttpSocket();

	        $response = $httpSocket->post($link, $order );
			
			$this->set("json_send",$jsonContent);
			$this->set("json_response",$response['body']);
		}
	}
}
