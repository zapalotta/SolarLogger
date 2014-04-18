<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Solar extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://solar.doerflinger.org/index.php/solar
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
			  );

	  $this->load->view( 'solar_view', $data);
	}


	/**
	 * day_ajax function.
	 * 
	 * @access public
	 * @return void
	 */
	public function day_ajax ( ) {
	  $type = "pac";
	  $date = $this->input->get('date', TRUE);
	  $normalized = $this->input->get('normalize', TRUE);
	  $energy = $this->input->get('energy', TRUE);
	  $combined = $this->input->get('combined', TRUE);
	  $this->load->model ( 'Solarlog_model' );
	  echo ( json_encode ( $this->Solarlog_model->get_day_power_ajax( $type, $date, $combined, $energy, $normalized ), JSON_NUMERIC_CHECK|JSON_FORCE_OBJECT ) );
	  //print_r ( $this->Solarlog_model->get_day_power_ajax( $type, $date, $combined, $energy, $normalized ) );
	}

	/**
	 * month_ajax function.
	 * 
	 * @access public
	 * @return void
	 */
	public function month_ajax ( ) {
	  $type = "energy";
	  $date = $this->input->get('date', TRUE);
	  $combined = $this->input->get('combined', TRUE);
	  $this->load->model ( 'Solarlog_model' );
	  echo ( json_encode ( array( $this->Solarlog_model->get_month_power_ajax( $type, $date, $combined ) ), JSON_NUMERIC_CHECK|JSON_FORCE_OBJECT ) );
	  //print_r ( $this->Solarlog_model->get_day_power_ajax( $type, $date, $combined, $energy, $normalized ) );
	}
}

/* End of file solar.php */
/* Location: ./application/controllers/solar.php */