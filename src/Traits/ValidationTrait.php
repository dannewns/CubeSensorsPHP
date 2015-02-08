<?php 

namespace Jump24\CubeSensors\Traits;

use Carbon\Carbon;
use Jump24\CubeSensors\Exceptions\InvalidDateFormatException;
use Jump24\CubeSensors\Exceptions\ApiHistoryLimitException;
use Jump24\CubeSensors\Exceptions\FutureDateException;

trait ValidationTrait  {

	public $validation_error = NULL;

	/**
	 * checks to see if the date is a valid format
	 * @param  string  $date the date being evaluated
	 * @return boolean       the result of the validation
	 */
	public function isDateValid($date)
	{
		try {

			$date = Carbon::createFromFormat('Y-m-d', $date);

			return isset($date);
		
		} catch (\InvalidArgumentException $e) {

			$this->validation_error = 'The date format you have used is invalid';

			return false;

		}

	}

	/**
	 * checks to see if the date passed to it is earlier than the 48 hour limit that the api currently has
	 * @param  string  $date the date being checked
	 * @return boolean       the result of the validaiton
	 */
	public function isDatePastApiHistoryLimit($date, $end_date = null)
	{

		$start_date = Carbon::createFromFormat('Y-m-d', $date);

		$end_date = Carbon::now();

		if ( $end_date->diffInDays($start_date) > 2) {

			$this->validation_error = 'The date you are trying to use is past the 48 hour API History limit';

			return false;
 		
 		} 

 		return true;

	}

  	/**
  	 * pulls back the latest error set on the class
  	 * @return string the current error message stored
  	 */
  	public function getErrorMessage()
  	{
  		return $this->validation_error;
  	}

	/**
	 * validates to check if the date being passed in is in the future or not
	 * @param  string  $date the date to validate
	 * @return boolean       the result of the future check
	 */
	public function isDateInThePast($date)
	{
	
		$date = Carbon::createFromFormat('Y-m-d', $date);

		if ($date->isFuture()) {

			$this->validation_error = 'The date you provided is in the future';

			return false;

 		} 

 		return true;
	
	}

 	/**
 	 * checks the date difference isnt more than the api limit
 	 * @param  string $start_date the start date passed in for validation
 	 * @param  string $end_date   the end date passed in for validation
 	 * @return boolean            the result of the validation
 	 */
 	public function validateDateDifferenceIsInBetweenApiLimit($start_date, $end_date)
 	{

 		$start_date = Carbon::createFromFormat('Y-m-d', $start_date);

		$end_date =  Carbon::createFromFormat('Y-m-d', $end_date);

 		if ( $start_date->diffInDays($end_date) > 2) {

 			$this->validation_error = 'The date you are trying to use is past the 48 hour API History limit';

 			return false;
 		
 		} 

 		return true;

 	}

 	/**
	 * validates that the start date is actually before the end date
	 * @param  string  $start_date the start date being used 
	 * @param  string  $end_date   the end date to evaluate against
	 * @return boolean             the result
	 */
	public function isEndDateAfterStartDate($start_date, $end_date)
	{

		$start_date = Carbon::createFromFormat('Y-m-d', $start_date);

		$end_date = Carbon::createFromFormat('Y-m-d', $end_date);

		$difference = $start_date->diffInDays($end_date, false);
    
 		if ($difference  >= 0)

 			return true;

 		else {

 			$this->validation_error = 'The end date you supplied is before the start date';

 			return false;

 		}
 		
	}

}