<?php 

namespace Jump24\CubeSensors;

use Carbon\Carbon;

class CubeSensorsValidation  {

  	protected $error = NULL;

  	const API_DAY_DIFFERENCE_LIMIT = 2;

  	const START_DATE_IN_FUTURE_ERROR = 'The start date you provided is in the future';

  	const END_DATE_BEFORE_START_DATE_ERROR = 'The end date you supplied is before the start date you supplied';

  	protected $api_range_limit_error = 'There was more than 2 days between the start and end date The API currently only supports a 2 day limit';


  	/**
  	 * pulls back the latest error set on the class
  	 * @return [type] [description]
  	 */
  	public function getErrorMessage()
  	{
  		return $this->error;
  	}

  	/**
 	 * checks to see if the given date is in the past and not a future date
 	 * @param  [type] $start_date [description]
 	 * @return [type]             [description]
 	 */
 	public function validateStartDateIsInPast(Carbon $start_date)
 	{
 	
 		if ($start_date->isFuture()) {

 			$this->error = self::START_DATE_IN_FUTURE_ERROR;

 			return false;
 		} 

 		return true;
 	}


 	/**
 	 * checks the date difference isnt more than the api limit
 	 * @param  Carbon $start_date [description]
 	 * @param  Carbon $end_date   [description]
 	 * @return [type]             [description]
 	 */
 	public function validateDateDifferenceIsInBetweenApiLimit(Carbon $start_date, Carbon $end_date)
 	{
 		if ( $start_date->diffInDays($end_date) > self::API_DAY_DIFFERENCE_LIMIT) {

 			$this->error = $this->api_range_limit_error;

 			return false;	
 		
 		} 

 		return true;

 	}


 	/**
 	 * checks to see if the end date supplied is before the start date
 	 * @return [type] [description]
 	 */
 	public function validateIfEndDateIsBeforeStartDate(Carbon $start_date, Carbon $end_date)
 	{


    $difference = $start_date->diffInDays($end_date, false);
    
 		if ($difference  > 0)

 			return true;

 		else {

 			$this->error = self::END_DATE_BEFORE_START_DATE_ERROR;;

 			return false;
 		}
 		
 	}

}