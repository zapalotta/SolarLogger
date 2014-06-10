<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

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

	  /*
	  $data = array ( 
			 "currentday" => $this->Solarlog_model->get_day_data (),
			 "daypowervalues" => $this->Solarlog_model->get_day_data_js_combined (),  
			 "date_today" => $this->Solarlog_model->get_date_today (),
			 "dayenergydata" => $this->Solarlog_model->get_current_day_energy (),  
			  );


	  */
	  $this->load->view('start_view', $data);
	  

	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */