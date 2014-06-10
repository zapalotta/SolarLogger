<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

  

	public function index()
	{
	  $this->load->model ( 'Solarlog_model' );

	  $data = array ( 
			 "chart" => $this->Solarlog_model->create_day_chart ( ),
			 "nav" => $this->Solarlog_model->create_nav ()
			  );

	  $this->load->view( 'data_view', $data);
	}

	public function display() {

	  $this->load->model ( 'Solarlog_model' );
	  $data["nav"] = $this->Solarlog_model->create_nav ();
	  
	  echo "<pre>";
	  //print_r ( $this->Solarlog_model->InverterInfo );
		  
	  $this->Solarlog_model->get_day_data ( '', false );
	  print_r ( $this->Solarlog_model->format_power () );
	  echo "</pre>";

	  $this->load->view( 'display', $data);
	}

	public function getJSON() {
	  
	  header("content-type: application/json"); 

	  $array = array(7,4,2,8,4,1,9,3,2,16,7,12);

	  echo $_GET['callback']. '('. json_encode($array) . ')';    
	  
	}

	public function cal ( $combined = "1", $energy = "0", $normalized = "0", $year = "", $month = "" ) {
	  //	  echo base_url() . "index.php/data/month/" . subsTr( $year, 2, 2 ) . $month . "/$combined/$energy/$normalized";
	  $check = mktime(0, 0, 0, $month, 1, $year );
	  $today = mktime(0, 0, 0, date("m"), date("d"), date("y"));
	  if ( $check > $today ) {
	    $month = date ( "m" );
	  }
	  redirect ( base_url() . "index.php/data/month/" . substr( $year, 2, 2 ) . $month . "/$combined/$energy/$normalized" );
	}

	public function today( $combined = "1", $energy = "0", $normalized = "0" )
	{
	  $this->load->model ( 'Solarlog_model' );

	  // FIXME: Check $date
	  $data = array ( 
			 "chart" => $this->Solarlog_model->create_day_chart ( "", $combined, $energy, $normalized ),
			 "nav" => $this->Solarlog_model->create_nav ( "today", "", $combined, $energy, $normalized )
			  );

	  $this->load->view( 'data_view', $data);
	}

	public function day ( $date = "", $combined = "1", $energy = "0", $normalized = "0" ) {
	  $this->load->model ( 'Solarlog_model' );

	  // FIXME: Check $date
	  $data = array ( 
			 "chart" => $this->Solarlog_model->create_day_chart ( $date, $combined, $energy, $normalized ),
			 "nav" => $this->Solarlog_model->create_nav ( "day", $date, $combined, $energy, $normalized )
			  );

	  $this->load->view( 'data_view', $data);
	  
	}


	public function month ( $date = "", $combined = "1", $energy = "0", $normalized = "0" ) {
	  $this->load->model ( 'Solarlog_model' );

	  // FIXME: Check $date
	  $data = array ( 
			 "chart" => $this->Solarlog_model->create_month_chart ( $date, $combined, $energy, $normalized ),
			 "nav" => $this->Solarlog_model->create_nav ( "month", $date, $combined, $energy, $normalized )
			  );

	  $this->load->view( 'data_view', $data);
	  
	}



}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */