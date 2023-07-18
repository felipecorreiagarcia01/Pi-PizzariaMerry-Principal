<?php
/**
* WPPIZZA_MODULE_OPENINGTIMES_HOLIDAYS Class
*
* @package     WPPIZZA
* @subpackage  WPPIZZA_MODULE_OPENINGTIMES_HOLIDAYS
* @copyright   Copyright (c) 2015, Oliver Bach
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       3.17
*
*/
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/


/************************************************************************************************************************
*
*
*
*
*
*
************************************************************************************************************************/
class WPPIZZA_MODULE_OPENINGTIMES_HOLIDAYS{

	private $settings_page = 'openingtimes';/* which admin subpage (identified there by this->class_key) are we adding this to */
	private $section_key = 'holidays';/* must be unique */


	private $nextOpeningDay;

	function __construct() {
		/**********************************************************
			[add settings to admin]
		***********************************************************/
		if(is_admin()){
			/* add admin options settings page*/
			add_filter('wppizza_filter_settings_sections_'.$this->settings_page.'', array($this, 'admin_options_settings'), 50, 5);
			/* add admin options settings page fields */
			add_action('wppizza_admin_settings_section_fields_'.$this->settings_page.'', array($this, 'admin_options_fields_settings'), 10, 5);
			/**add default options **/
			add_filter('wppizza_filter_setup_default_options', array( $this, 'options_default'));
			/**validate options**/
			add_filter('wppizza_filter_options_validate', array( $this, 'options_validate'), 10, 2 );
			/** admin ajax **/
			add_action('wppizza_ajax_admin_'.$this->settings_page.'', array( $this, 'admin_ajax'));
		}
		/**********************************************************
			[filter/actions depending on settings]
		***********************************************************/
		add_filter( 'wppizza_shop_is_open', array( $this, 'on_holiday' ), 5, 2);//close for holidays
		#add_filter( 'wppizza_shop_is_open', array( $this, 'on_holiday_message' ), 5, 2);//set closed for holidays message
	}

	/*******************************************************************************************************************************************************
	*
	*
	*
	* 	[frontend filters]
	*
	*
	*
	********************************************************************************************************************************************************/
	/*******************************************************
		[force shop to be closed due to holidays]
	******************************************************/
	function on_holiday( $is_open, $args ) {
		global $wppizza_options;

		/***********************
			skip if none set
		***********************/
		if(empty($wppizza_options[$this->settings_page]['opening_times_holidays'])){
			return $is_open;
		}
		/***********************
			get current status and skip if currently closed anyway
		***********************/
		$otArgs = array( 'current' => true );
		$shop_status = wppizza_get_openingtimes($otArgs);
		$shop_status_open_date = substr($shop_status['current']['open_dt'], 0, 10);
		$shop_status_close_date = substr($shop_status['current']['close_dt'], 0, 10);
		if(empty($shop_status['is_open'])){
			return $is_open;
		}

		/***********************
			loop through set holidays
		***********************/
		foreach($wppizza_options[$this->settings_page]['opening_times_holidays'] as $holiday_range){

			/*
				set to be repeating yearly => replace year
			*/
			if(!empty($holiday_range['repeat_yearly'])){
				//holidays might go across year ends so get year offset between now and set
				$currentYear = $args['Y'];
				$setYear = substr($holiday_range['start_date'],0,4);
				$offset = ($currentYear-$setYear);

				//if there's an offset use set year plus/minus the offset
				if($offset != 0){
					$holiday_range['start_date'] = ($setYear + $offset).'-'.substr($holiday_range['start_date'], 5);
					$holiday_range['start_date_ts'] = strtotime($holiday_range['start_date'].' 00:00:00');
					$holiday_range['end_date'] = ($setYear + $offset).'-'.substr($holiday_range['end_date'], 5);
					$holiday_range['end_date_ts'] = strtotime($holiday_range['end_date'].' 23:59:59');
				}
			}

			/*
				close for holidays if:
				date of current opening is >= holiday start date and <= holiday end date
			*/
			if( $shop_status_open_date >=  $holiday_range['start_date']   && $shop_status_open_date <= $holiday_range['end_date']){

				/******************************************************
					set next opening day to use in localization
				******************************************************/
				$dateFormat = get_option( 'date_format' );
				$this-> nextOpeningDay = date_i18n($dateFormat, ($holiday_range['end_date_ts'] +1));//adding one second to get the next day - ignoring times


				/**********************************************************
					replace "currently closed" in cart and checkout
				**********************************************************/
				add_filter('wppizza_filter_maincart_shopclosed_markup', array($this, 'closed_for_holidays_markup'));
				add_filter('wppizza_filter_pages_order_markup', array($this, 'closed_for_holidays_markup'));

				return false;
			}
		}

	//retun as is if none of the above applies
	return $is_open;
	}


	/*------------------------------------------------------------------------------
	*	replace default "currently closed" with something like "We're on holiday"
	*	@since 3.16.7
	------------------------------------------------------------------------------*/
	function closed_for_holidays_markup($markup){
		global $wppizza_options;

		//cart widget
		if(isset($markup['shopclosed']) && !empty($wppizza_options['localization']['closed_for_holidays'])){
			$markup['shopclosed'] = sprintf(wppizza_sanitise_forsprintf($wppizza_options['localization']['closed_for_holidays'], 1), $this-> nextOpeningDay);//cart
		}
		//checkout widget
		if(isset($markup['shop_closed']) && !empty($wppizza_options['localization']['closed_for_holidays'])){
			$markup['shop_closed'] = sprintf(wppizza_sanitise_forsprintf($wppizza_options['localization']['closed_for_holidays'], 1), $this-> nextOpeningDay);//orderpage
		}
	return $markup;
	}


	/*******************************************************************************************************************************************************
	*
	*
	*
	* 	[add admin page options]
	*
	*
	*
	********************************************************************************************************************************************************/
	/*******************************************************************************************************************************************************
	*
	* 	[admin ajax]
	*
	********************************************************************************************************************************************************/
	function admin_ajax($wppizza_options){
		/*****************************************************
			[adding new custom opening time]
		*****************************************************/
		if($_POST['vars']['field']=='opening_times_holidays'){
			$markup = $this->wppizza_admin_section_opening_times_holidays($_POST['vars']['field']);
			print $markup ;
			exit();
		}
	}
	/*------------------------------------------------------------------------------
	#
	#
	#	[settings page]
	#
	#
	------------------------------------------------------------------------------*/

	/*------------------------------------------------------------------------------
	#	[settings section - setting page]
	#	@since 3.0
	#	@return array()
	------------------------------------------------------------------------------*/
	function admin_options_settings($settings, $sections, $fields, $inputs, $help){

		/*section*/
		if($sections){
			$settings['sections'][$this->section_key] =  __('Holidays', 'wppizza-admin');
		}

		/*help*/
		if($help){
			$settings['help'][$this->section_key][] = array(
				'label'=>__('Manage Holidays', 'wppizza-admin'),
				'description'=>array(
					__('Set ranges between 2 dates - first and last date - when your shop is closed (i.e for holidays or similar).', 'wppizza-admin'),
					__('If your shop is closed only for one particular date, set both dates to be that same date.', 'wppizza-admin'),
					__('Will override any other opening times settings (but before any filters).', 'wppizza-admin'),
				)
			);
		}

		/*fields*/
		if($fields){
			$field = 'opening_times_holidays';
			$settings['fields'][$this->section_key][$field] = array( '', array(
				'value_key'=>$field,
				'option_key'=>$this->settings_page,
				'label'=>'',
				'description'=>array()
			));
		}

	return $settings;
	}
	/*------------------------------------------------------------------------------
	#	[output option fields - setting page]
	#	@since 3.0
	#	@return array()
	------------------------------------------------------------------------------*/
	function admin_options_fields_settings($wppizza_options, $options_key, $field, $label, $description){

		if($field=='opening_times_holidays'){

			echo"<div id='wppizza_".$field."_options'  class='wppizza_admin_options'>";
			if(isset($wppizza_options[$this->settings_page][$field])){
				/* sort by date */
				asort($wppizza_options[$this->settings_page][$field]);
				foreach($wppizza_options[$this->settings_page][$field] as $k=>$values){
					echo"".$this->wppizza_admin_section_opening_times_holidays($field, $values, $k);
				}}
			echo"</div>";

			/** add new button **/
			echo"<div id='wppizza-".$field."-add' class='wppizza_admin_add'>";
				echo "<a href='javascript:void(0)' id='wppizza_add_".$field."' class='button'>".__('add', 'wppizza-admin')."</a>";
			echo"</div>";
		}
	}
	/*------------------------------------------------------------------------------
	#	[insert default option on install]
	#	$parameter $options array() | filter passing on filtered options
	#	@since 3.0
	#	@return array()
	------------------------------------------------------------------------------*/
	function options_default($options){
	return $options;
	}

	/*------------------------------------------------------------------------------
	#	[validate options on save/update]
	#
	#	@since 3.0
	#	@return array()
	------------------------------------------------------------------------------*/
	function options_validate($options, $input){
		/**make sure we get the full array on install/update**/
		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}

		if(isset($_POST[''.WPPIZZA_SLUG.'_'.$this->settings_page.''])){

			$options[$this->settings_page]['opening_times_holidays'] = array();//re - initialize array

			if(isset($input[$this->settings_page]['opening_times_holidays']['start_date'])){

				foreach($input[$this->settings_page]['opening_times_holidays']['start_date'] as $key => $range){

					/*
						making sure we have a range set and end is later than start
					*/
					$start_date = $input[$this->settings_page]['opening_times_holidays']['start_date'][$key];
					$start_date = !empty($start_date) ? wppizza_validate_date($start_date,'Y-m-d') : false;
					$end_date = $input[$this->settings_page]['opening_times_holidays']['end_date'][$key];
					$end_date = !empty($end_date) ? wppizza_validate_date($end_date,'Y-m-d') : false;

					if( !empty($start_date) && !empty($end_date) && $start_date <= $end_date ){
						$options[$this->settings_page]['opening_times_holidays'][$key]['start_date_ts']=strtotime($start_date.' 00:00:00');
						$options[$this->settings_page]['opening_times_holidays'][$key]['end_date_ts']=strtotime($end_date.' 23:59:59');
						$options[$this->settings_page]['opening_times_holidays'][$key]['start_date']=$start_date;
						$options[$this->settings_page]['opening_times_holidays'][$key]['end_date']=$end_date;
						$options[$this->settings_page]['opening_times_holidays'][$key]['repeat_yearly']=!empty($input[$this->settings_page]['opening_times_holidays']['repeat_yearly'][$key]) ? true : false;
					}
				}
			//simple sort by start_date_ts
			asort($options[$this->settings_page]['opening_times_holidays']);

			}
		}

	return $options;
	}
	/*********************************************************
			[helper - holidays date range also used when adding via ajax]
	*********************************************************/
	function wppizza_admin_section_opening_times_holidays($field, $values = false, $key = false){

		$start_date_formatted = !empty($values['start_date']) ? date_i18n("d M Y",strtotime($values['start_date'])) : '';
		$start_date = !empty($values['start_date']) ? $values['start_date'] : '';

		$end_date_formatted = !empty($values['end_date']) ? date_i18n("d M Y",strtotime($values['end_date'])) : '';
		$end_date = !empty($values['end_date']) ? $values['end_date'] : '';


		$str='';
		$str.="<div class='wppizza_option'>";

			$str.="".__('From', 'wppizza-admin').":";
			$str.="<input name='".WPPIZZA_SLUG."[".$this->settings_page."][".$field."][start_date_formatted][]' size='10' type='text' class='wppizza-date-range' autocomplete='off' value='". $start_date_formatted ."' />";
			$str.="<input name='".WPPIZZA_SLUG."[".$this->settings_page."][".$field."][start_date][]' type='hidden' class='wppizza-date' value='".$start_date."' />";


			$str.="".__('To', 'wppizza-admin').":";
			$str.="<input name='".WPPIZZA_SLUG."[".$this->settings_page."][".$field."][end_date_formatted][]' size='10' type='text' class='wppizza-date-range' autocomplete='off' value='". $end_date_formatted ."' />";
			$str.="<input name='".WPPIZZA_SLUG."[".$this->settings_page."][".$field."][end_date][]' type='hidden' class='wppizza-date' value='".$end_date."' />";


			$str.="<label>".__('Every year', 'wppizza-admin').":";
			$str.="<input name='".WPPIZZA_SLUG."[".$this->settings_page."][".$field."][repeat_yearly][]' type='checkbox' ".checked(!empty($values['repeat_yearly']), true, false )." value='1' title='".__('Repeat every year for this date', 'wppizza-admin')."'/></label>";

			$str.="<a href='javascript:void(0);' class='wppizza-delete ".$field." ".WPPIZZA_SLUG."-dashicons dashicons-trash' title='".__('delete', 'wppizza-admin')."'></a>";

		$str.="</div>";

		return $str;
	}


}
/***************************************************************
*
*	[ini]
*
***************************************************************/
$WPPIZZA_MODULE_OPENINGTIMES_HOLIDAYS = new WPPIZZA_MODULE_OPENINGTIMES_HOLIDAYS();
?>