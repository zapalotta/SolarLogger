<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Inverter class.
 *
 * When data is loaded, for each inverter found, one object is created holding meta data (Serial etc.) and solar data.
 */
class Inverter {

	/**
	 * Incremental numbers of all inverters found
	 * 
	 * (default value: -1)
	 * 
	 * @var float
	 * @access public
	 */
	var $InverterNumber = -1;
	
	/**
	 * Type of Inverter
	 * 
	 * (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	var $Type = "";
	
	/**
	 * Address or Serial number of Inverter (depends on vendor)
	 * 
	 * (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	var $AddressSerial = "";
	
	/**
	 * Power connected to this inverter.
	 * 
	 * (default value: 0)
	 * 
	 * @var int
	 * @access public
	 */
	var $ConnectedPower = 0;
	
	/**
	 * Description of the Inverter
	 * 
	 * (default value: "")
	 * 
	 * @var string
	 * @access public
	 */
	var $Description = "";
	
	/**
	 * Number Of Strings connected to the inverter
	 * 
	 * (default value: 0)
	 * 
	 * @var int
	 * @access public
	 */
	var $NumberOfStrings = 0;
	
	/**
	 * Names of connected Strings
	 * 
	 * (default value: array ())
	 * 
	 * @var array
	 * @access public
	 */
	var $StringNames = array ();
	
	/**
	 * Array of Module Fields connected
	 * 
	 * (default value: array ())
	 * 
	 * @var array
	 * @access public
	 */
	var $ModuleField = array ();
	
	/**
	 * Nominal power of inverter
	 * 
	 * (default value: 0)
	 * 
	 * @var int
	 * @access public
	 */
	var $InverterPower = 0;
	
	/**
	 * Exports values of a temperatur sensor
	 * 
	 * (default value: 0)
	 * 
	 * @var int
	 * @access public
	 */
	var $HasTemp = 0;
	
	/**
	 * Array of data (short interval, usually each few minutes)
	 * 
	 * (default value: array ())
	 * 
	 * @var array
	 * @access public
	 */
	var $minutesData = array ();  
	
	/**
	 * Array of data by day.
	 * 
	 * (default value: array ())
	 * 
	 * @var array
	 * @access public
	 */
	var $dayData = array ();  

	/**
	 * Array of data by month.
	 * 
	 * (default value: array ())
	 * 
	 * @var array
	 * @access public
	 */
	var $monthData = array ();  
}


/**
 * Solarlog_model class.
 *
 * This class parses the data files from SolarLog, creates objects for each inverter found and provides methods to receive the data
 * 
 * @extends CI_Model
 */
class Solarlog_model extends CI_Model {

  /**
   * base_url to the app
   * 
   * (default value: "http://solar.doerflinger.org/index.php/solar/")
   * 
   * @var string
   * @access public
   */
   var $base_url = "http://solar.doerflinger.org/index.php/solar/";
  
  /**
   * Nominal power of whole site (kWh)
   * 
   * (default value: 0)
   * 
   * @var int
   * @access public
   */
   var $SiteKWP = 0;

  /**
   * Today's date
   * 
   * (default value: "")
   * 
   * @var string
   * @access public
   */
   var $date_today = "";

  /**
   * Numer of inverters
   * 
   * (default value: 0)
   * 
   * @var int
   * @access public
   */
   var $NumerOfInverters = 0;

  /**
   * Array of basic inverter information
   * 
   * (default value: array ())
   * 
   * @var array
   * @access public
   */
   var $InverterInfo = array ();

  /**
   * Energy produced the day before
   * 
   * (default value: 0)
   * 
   * @var int
   * @access public
   */
   var $EnergyYesterday = 0;

  /**
   * Flag which gets set as soon as data has been loaded into the object. 
   * Checked by all getter functions being able to load data into the 
   * object if not already done.
   * 
   * (default value: FALSE)
   * 
   * @var mixed
   * @access public
   */
   var $has_data = FALSE;

  /**                                                                                                                                                                          
   * Constructor                                                                                                                                                               
   *                                                                                                                                                                           
   * Initialize the class                                                                                                                                                      
   *                                                                                                                                                                           
   * @access   public                                                                                                                                                          
   */
   function __construct () {
	   parent::__construct();
	   $this->get_base_data ();
	   $this->has_data = FALSE;
}

  /**
   * get_month_power_ajax function.
   * 
   * @access public
   * @param string $type (default: "energy")
   * @param string $date (default: "")
   * @param mixed $combined (default: TRUE)
   * @return void
   */
   function get_month_power_ajax ( $type="energy", $date = "", $combined = TRUE, $normalized = FALSE ) {
		if ( $date == date( 'ymd' ) ) $date = "";
		if ( !$this->has_data ) {
			$this->get_month_data ( $date, $combined );
		}
		$xAxis = array ();
		$data = array ();
		$cat = array();
		$name = array();
		$counter = 0;
		if ( count ( $this->InverterInfo ) > 0 ) {
			foreach ( $this->InverterInfo as $inverter ) {
				$name[$counter] = $inverter->Type;		
				$connectedPowerkWp = $inverter->ConnectedPower/1000;

				// $dayData starts today, we need to reverse it ...
				$vals = array_reverse( $inverter->dayData );
				foreach ( $vals as $val ) {
					if ( !$normalized ) {
						$data[$counter][] = $val ['energy' ];
						$cat[$counter][] = $val ['timestamp' ];
					}
					else {
						$data[$counter][] = $val ['energy' ];
						$cat[$counter][] = $val ['timestamp' ];						
					}
				}
				$counter ++;
			}       
		}
		
		if ( $date == "" ) $datestr = date ( 'ymd' );
		else $datestr = $date;
		
		if ( $combined ) {
			$ret = array();
			foreach ( $data as $datarray ) {
				$ret = array_map(function () {
					return array_sum(func_get_args());
				}, $ret, $datarray);
				$name[0] = "Energie gesamt";
			}		
			return ( array( $name, $cat, array( $ret ), $datestr ) );
		}
		else {
			return ( array( $name, $cat, $data, $datestr ) );
		}	
	}

  /**
   * get_year_power_ajax function.
   * 
   * @access public
   * @param string $type (default: "energy")
   * @param string $date (default: "")
   * @param mixed $combined (default: TRUE)
   * @return void
   */
   function get_year_power_ajax ( $type="energy", $date = "", $combined = TRUE ) {
		if ( $date == date( 'ymd' ) ) $date = "";
		if ( !$this->has_data ) {
			$this->get_year_data ( $date, $combined );
		}
		$xAxis = array ();
		$data = array ();
		$cat = array();
		$name = array();
		$counter = 0;
		if ( count ( $this->InverterInfo ) > 0 ) {
			foreach ( $this->InverterInfo as $inverter ) {
				$name[$counter] = $inverter->Type;		
				// $dayData starts today, we need to reverse it ...
				$vals = array_reverse( $inverter->monthData );
				foreach ( $vals as $val ) {
					$data[$counter][] = $val ['energy' ];
					$cat[$counter][] = $val ['timestamp' ];
				}
				$counter ++;
			}       
		}
		
		if ( $date == "" ) $datestr = date ( 'ymd' );
		else $datestr = $date;
		
		if ( $combined ) {
			$ret = array();
			foreach ( $data as $datarray ) {
				$ret = array_map(function () {
					return array_sum(func_get_args());
				}, $ret, $datarray);
				$name[0] = "Energie gesamt";
			}		
			return ( array( $name, $cat, array( $ret ), $datestr ) );
		}
		else {
			return ( array( $name, $cat, $data, $datestr ) );
		}	
	}



	/**
	 * get_day_power_ajax function.
	 * 
	 * @access public
	 * @param string $type (default: "Pac")
	 * @param string $date (default: "")
	 * @param mixed $combined (default: TRUE)
	 * @param mixed $show_energy (default: FALSE)
	 * @param mixed $normalized (default: FALSE)
	 * @return void
	 */
	function get_day_power_ajax( $type = "Pac", $date = "", $combined = TRUE, $show_energy = FALSE, $normalized = FALSE ) {
		// if today is chosen, use the default, empty date because 
		// another file is sent for today's data
		if ( $date == date( 'ymd' ) ) $date = "";
		if ( !$this->has_data ) {
			$this->get_day_data ( $date, $combined );
		}
		$result = array();
		$energy = 0;
		
//		print_r ( $this->InverterInfo );
		
		if ( count ( $this->InverterInfo ) > 0 ) {
			// always iterate over all inverters first.
			foreach ( $this->InverterInfo [0]->minutesData as $minData ) {
				$value = 0;
				$val = array();
				foreach ( $this->InverterInfo as $inverter ) {
					$result[0]['name'] = 'Gesamt ' . $type;
					$connectedPowerkWp = $inverter->ConnectedPower/1000;
					if ( !$normalized ) {
						$value += $inverter->minutesData [ $minData [ 'timestamp' ] ][ $type ] ;
					}
					else {
						// If normalized, we need an array of all inverters to calculate the average of these. May be wrong ...
						$val[$inverter->InverterNumber] = $inverter->minutesData [ $minData [ 'timestamp' ] ][ $type ]/$connectedPowerkWp ;
					}
				}
				if ( $normalized ) {
					$value = array_sum( $val )/count($val);
				}
				$result[0]['data'][] = array ( $minData ['timestamp'], $value );
			}	
			$datetime = str_replace ( '.', '-', $minData[ 'timestamp' ] );
			$day = substr ( $datetime, 0, 2 );
			$year = substr ( $datetime, 6, 2 );
			$datetime = substr_replace ( $datetime, $year, 0, 2 );
			$datetime = substr_replace ( $datetime, $day, 6, 2 );
			$result[0]['timestamp'][] = strtotime( "20" . $datetime ) * 1000;
			$result[0]['data'] = array_reverse ( $result[0]['data']);
			$energy = 0;
			foreach ( $result[0]['data'] as $power ) {
				$energy += $power[1]/12000;
				$result[0]['energy'][] = array ( $power[0], $energy );
			}
			if ( !$combined ) {
				// then maybe iterate over each inverter...
				$counter = 1;
				foreach ( $this->InverterInfo as $inverter ) {
					$connectedPowerkWp = $inverter->ConnectedPower/1000;
					$result[$counter]['name'] = $inverter->Type; 
					foreach ( $inverter->minutesData as $minData ) {
						$value = 0;
						if ( !$normalized ) {
							$value += $inverter->minutesData [ $minData [ 'timestamp' ] ][ $type ] ;
						}
						else {
							$value += $inverter->minutesData [ $minData [ 'timestamp' ] ][ $type ]/$connectedPowerkWp;
						}
						$result[$counter]['timestamp'][] = $minData [ 'timestamp' ];
						$result[$counter]['data'][] = array( $minData [ 'timestamp' ], $value );
					}
					$result[$counter]['timestamp'] = array_reverse ( $result[$counter]['timestamp'] );
					$result[$counter]['data'] = array_reverse ( $result[$counter]['data'] );
					$counter++;
				}
			}	
		}  // END if ( count ( $this->InverterInfo....

		if ( $date == "" ) $datestr = date ( 'ymd' );
		else $datestr = $date;
		$result['num_inverters'] = count ( $this->InverterInfo );
		$result['date'] = $datestr;
		return $result;
	}


  /**
   * Date-Format: YYMMDD
   *
   */
    function get_day_data ( $date = "", $combined ) {
		if ( $date == "" ) {
			$day_file_name = "/home/websites/solar.doerflinger.org/min_day.js";
		}
		else {
			$day_file_name = "/home/websites/solar.doerflinger.org/min$date.js";
		}
		
		if ( file_exists ( $day_file_name ) ) {
			$day_file = file ( $day_file_name );
		}
		else return 0;
		
		$this->has_data = TRUE;
		$count = 0;
		foreach ( $day_file as $min_data_line ) {
			$count ++;
			$min_data_line = substr ( substr ( trim ( $min_data_line ), 9 ), 0, -1 );
			$invertersArray = explode ( "|", $min_data_line );
			$timestamp = trim ( $invertersArray [0] );
			$this->date_today = substr ( $timestamp, 0, 8 );
			$ts_day = substr ( $timestamp, 0, 2 );
			$ts_month = substr ( $timestamp, 3, 2 );
			$ts_year = substr ( $timestamp, 6, 2 );
			$ts_hour = substr ( $timestamp, 9, 2 );
			$ts_minute = substr ( $timestamp, 12, 2 );
			$ts_second = substr ( $timestamp, 15, 2 );
			array_shift ( $invertersArray );
			foreach ( $this->InverterInfo as $inverter ) {
				$lineArray = explode ( ";", $invertersArray [ $inverter->InverterNumber ] );
				$minVals = array ();
				$minVals [ 'timestamp' ] = $timestamp;
				$minVals [ 'day' ] = $ts_day;
				$minVals [ 'month' ] = $ts_month;
				$minVals [ 'year' ] = "20" . $ts_year;
				$minVals [ 'hour' ] = $ts_hour;
				$minVals [ 'minute' ] = $ts_minute;
				$minVals [ 'second' ] = $ts_second;		
				// Get Pac and remove it from the array
				$minVals [ 'pac' ] = $lineArray [0];
				array_shift ( $lineArray);
				// get DC Power and remove each line
				for ( $i = 0; $i < $inverter->NumberOfStrings; $i++ ) {
					$minVals [ 'pdc' ][ $i ] = $lineArray [0];
					array_shift ( $lineArray );
				}
				// get todays energy
				$minVals [ 'dayEnergy' ] = $lineArray [0];
				// ASSUMING: data is in reverse order, so the last of these values must be the first of the day
				$this->EnergyYesterday = $lineArray [0];
				array_shift ( $lineArray );
				// Get DC voltages and remove
				for ( $i = 0; $i < $inverter->NumberOfStrings; $i++ ) {
					$minVals [ 'udc' ][ $i ] = $lineArray [0];
					array_shift ( $lineArray );
				}
				if ( $inverter->HasTemp == 1 ) {
					$minVals [ 'temp' ] = $lineArray [0];
				}
				$inverter->minutesData[ $timestamp ] = $minVals;
			}
		} // END foreach ( $day_file as $min_data_line )
		return $count;
	}

  /**
   * 
   */
   
  /**
   * get_month_data function.
   * 
   * @access public
   * @param string $date (default: "")
   * @param mixed $combined
   * @return void
   *
   * Date-Format: YYMM
   *
   */
    function get_month_data ( $date = "", $combined ) {
		$month_file_name = "/home/websites/solar.doerflinger.org/days_hist.js";
		if ( file_exists ( $month_file_name ) ) {
			$month_file = file ( $month_file_name );
		}
		else return 0;
		if ( $date == "" ) {
			$month = date ( "m" );
			$year = date ( "y" );
		}
		else {
			$month = substr ( $date, 2, 2 );
			$year = substr ( $date, 0, 2 );
		}
		
		$this->has_data = TRUE;
		$count = 0;
		foreach ( $month_file as $day_data_line ) {
			$count ++;
			$month_year = "$month.$year";	
			if ( substr ( $day_data_line, 13, 5 ) == $month_year ) {
				$day_data_line = substr ( substr ( trim ( $day_data_line ), 10 ), 0, -1 );
				$invertersArray = explode ( "|", $day_data_line );
				$timestamp = trim ( $invertersArray [0] );
				$this->month_today = $month;
				$ts_day = substr ( $timestamp, 0, 2 );
				$ts_month = substr ( $timestamp, 3, 2 );
				$ts_year = substr ( $timestamp, 6, 2 );
				array_shift ( $invertersArray );
				foreach ( $this->InverterInfo as $inverter ) {
					$lineArray = explode ( ";", $invertersArray [ $inverter->InverterNumber ] );
					$dayVals = array ();
					$dayVals [ 'timestamp' ] = $timestamp;
					$dayVals [ 'day' ] = $ts_day;
					$dayVals [ 'month' ] = $ts_month;
					$dayVals [ 'year' ] = "20" . $ts_year;
					$dayVals [ 'energy' ] = $lineArray [0];
					$dayVals [ 'pac_max' ] = $lineArray [1];
					$inverter->dayData[ $timestamp ] = $dayVals;
				}
			}      
		}
		return $count;
	}

  /**
   * get_year_data function.
   * 
   * @access public
   * @param string $date (default: "")
   * @param mixed $combined
   * @return void
   *
   * Date-Format: YYMM
   *
   */
    function get_year_data ( $date = "", $combined ) {
		$year_file_name = "/home/websites/solar.doerflinger.org/months.js";
		if ( file_exists ( $year_file_name ) ) {
			$year_file = file ( $year_file_name );
		}
		else return 0;
		if ( $date == "" ) {
			$month = date ( "m" );
			$year = date ( "y" );
		}
		else {
			$month = substr ( $date, 2, 2 );
			$year = substr ( $date, 0, 2 );
		}
		
		$this->has_data = TRUE;
		$count = 0;
		foreach ( $year_file as $month_data_line ) {
			$count ++;
			if ( substr ( $month_data_line, 16, 2 ) == $year ) {
				$month_data_line = substr ( substr ( trim ( $month_data_line ), 10 ), 0, -1 );
				$invertersArray = explode ( "|", $month_data_line );
				$timestamp = trim ( $invertersArray [0] );
				$ts_month = substr ( $timestamp, 3, 2 );
				$ts_year = substr ( $timestamp, 6, 2 );
				array_shift ( $invertersArray );
				foreach ( $this->InverterInfo as $inverter ) {
					$lineArray = explode ( ";", $invertersArray [ $inverter->InverterNumber ] );
					$monthVals = array ();
					$monthVals [ 'timestamp' ] = $ts_month;
					$monthVals [ 'month' ] = $ts_month;
					$monthVals [ 'year' ] = "20" . $ts_year;
					$monthVals [ 'energy' ] = $lineArray [0];
					$inverter->monthData[ $timestamp ] = $monthVals;
				}
			}
		}
		return $count;
	}


	function get_base_data () {
    	$base_vars_file = file ( "/home/websites/solar.doerflinger.org/base_vars.js" );
		foreach ( $base_vars_file as $base_var ) {			
			if ( substr ( $base_var, 0, 14 ) == "var AnlagenKWP" ) $this->SiteKWP = substr ( $base_var, strpos ( $base_var, "=" )+1 );
			if ( substr ( $base_var, 0, 12 ) == "var AnzahlWR" ) $this->NumberOfInverters = substr ( $base_var, strpos ( $base_var, "=" )+1 );
	  
			if ( substr ( $base_var, 0, 6 ) == "WRInfo" ) {
				// Found WR Info
				// FIXME: Fix char encoding!
	
				// FIXME: take care of more than ten inverters!
				if ( substr( $base_var, 9, 1 ) == "=" ) {
					// We have a "Master"
					$inverterNumber = substr( $base_var, 7, 1 );
					$this->InverterInfo [ $inverterNumber ] = new Inverter ();
					$this->InverterInfo [ $inverterNumber ]->InverterNumber = $inverterNumber;
					// UGLY hack: replace possible commas and quotes in descriptions etc.
					preg_match_all('/\"(.*?)\"/', $base_var, $matches);
					foreach ( $matches[1] as $match ) {
						$ret = str_replace ( ",",";", $match );
						$base_var = substr ( trim ( $base_var ), 0, strpos ( $base_var, $match )-1 ) .
							$ret .
							substr ( trim ( $base_var ), strpos ( $base_var, $match ) + strlen ( $match )+1 )
						;
					}
					$inverterArray = explode ( ',', htmlentities( utf8_encode ( str_replace ( "\"", "", substr ( trim ( substr ( $base_var, 20 ) ), 0, -1 ) ) ) ) );
					$this->InverterInfo [ $inverterNumber ]->Type = $inverterArray [0];
					$this->InverterInfo [ $inverterNumber ]->AddressSerial = $inverterArray [1];
					$this->InverterInfo [ $inverterNumber ]->ConnectedPower = $inverterArray [2];
					$this->InverterInfo [ $inverterNumber ]->Description = $inverterArray [4];
					$this->InverterInfo [ $inverterNumber ]->NumberOfStrings = $inverterArray [5];
					$this->InverterInfo [ $inverterNumber ]->HasTemp = $inverterArray [12];
	  
				}
				else {
					if ( substr( $base_var, 10, 1 ) == "6" ) {
						// Array of String Names
						preg_match_all('/\"(.*?)\"/', $base_var, $matches);
						foreach ( $matches[1] as $match ) {
							$ret = str_replace ( ",",";", $match );
							$base_var = substr ( trim ( $base_var ), 0, strpos ( $base_var, $match )-1 ) .
								$ret .
								substr ( trim ( $base_var ), strpos ( $base_var, $match ) + strlen ( $match )+1 )
							;
						}
						$stringNameArray = explode ( ',', htmlentities( str_replace ( "\"", "", substr ( trim ( substr ( $base_var, 23 ) ), 0, -1 ) ) ) );
						$inverterNumber = substr( $base_var, 7, 1 );
						$this->InverterInfo [ $inverterNumber ]->StringNames = $stringNameArray;
					}
					else if ( substr ( $base_var, 10, 1 ) == "7" ) {
						// Array of Module Fields
						// FIXME: use these Values!
					}
				}  // END else of if ( substr( $base_var, 9, 1 ) == "=" ) {
			} // END of if ( substr ( $base_var, 0, 6 ) == "WRInfo" ) {
		}  // END of foreach ( $base_vars_file ...
		// FIXME: get site data!
		// End parse base_vars.js
	}

}

?>
