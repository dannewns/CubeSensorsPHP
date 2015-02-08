<?php

use Jump24\CubeSensors\Traits\ValidationTrait;
use Carbon\Carbon;

class ValidationTraitTest extends PHPUnit_Framework_TestCase {

    use ValidationTrait;

    public function setUp() 
    {

    }
    
    public function tearDown() 
    {
   
    }

    /**
     * tests the is date valid method with an incorrect string which should throw an exception
     * @return [type] [description]
     */
    public function testDateValidWithInvalidStringData()
    {

        $result = $this->isDateValid('asdsad');

        $this->assertFalse($result);

        $this->assertSame('The date format you have used is invalid', $this->getErrorMessage());

    }

    /**
     * tests the is date in the future method and making sure it handles incorrect date formates
     * @return [type] [description]
     */
    public function testDateValidWithValidDate()
    {

        $result = $this->isDateValid('2014-01-01');

        $this->assertTrue($result);

    }
  
    /**
     * tests that the validation traits is the date in the future method returns the correct response when a date is in the future
     * @return [type] [description]
     */
    public function testDateIsInPastMethodWithFutureDate()
    {
      
      $date = Carbon::now()->addDays(1);
 
      $result = $this->isDateInThePast($date->format('Y-m-d'));

      $this->assertFalse($result);

      $this->assertSame('The date you provided is in the future', $this->getErrorMessage());
   
    }

    /**
     * test to see if the date passed in is in
     * @return [type] [description]
     */
    public function testDateIsInPastMethodWithPastDate()
    {
      $date = Carbon::now()->subDays(1);

      $result = $this->isDateInThePast($date->format('Y-m-d'));

      $this->assertTrue($result);
   
    }

    /**
     * [testDateIsNotPastApiHistoryLimitValidation description]
     * @return [type] [description]
     */
    public function testDateIsNotPastApiHistoryLimitValidation()
    {
        $date = Carbon::now()->subDays(3);

        $result = $this->isDatePastApiHistoryLimit($date->format('Y-m-d'));

        $this->assertFalse($result);

        $this->assertSame('The date you are trying to use is past the 48 hour API History limit', $this->getErrorMessage());

    }

    /**
     * should return true when a valid date range is passed into the validation method with no error message set
     * @return [type] [description]
     */
    public function testValidAPIDateRangeValidation()
    {

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->subDays(2);

    	$result = $this->validateDateDifferenceIsInBetweenApiLimit($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

        $this->assertTrue($result);
   
    }

    /**
     * should return true when a valid date range is passed into the validation method with no error message set
     * @return [type] [description]
     */
    public function testInValidAPIDateRangeValidation()
    {

        $start_date = Carbon::now();

        $end_date = Carbon::now()->subDays(3);

        $result = $this->validateDateDifferenceIsInBetweenApiLimit($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

        $this->assertFalse($result);

        $this->assertSame('The date you are trying to use is past the 48 hour API History limit', $this->getErrorMessage());
   
    }

    /**
     * tests isEndDateAfterStartDate method with invalid dates to flag a error
     * @return [type] [description]
     */
    public function testValidateIfEndDateIsBeforeStartDateWithEndDateBeforeStart()
    {

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->subDay();

    	$result = $this->isEndDateAfterStartDate($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

      	$this->assertFalse($result);

      	$this->assertSame('The end date you supplied is before the start date', $this->getErrorMessage());
   
    }

    /**
     * tests isEndDateAfterStartDate method with valid dates
     * @return [type] [description]
     */
    public function testValidateIfEndDateIsBeforeStartDateWithValidDates()
    {

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->addDay();

    	$result = $this->isEndDateAfterStartDate($start_date->format('Y-m-d'), $end_date->format('Y-m-d'));

      	$this->assertTrue($result);

      	$this->assertNull( $this->getErrorMessage());
    
    }
    
 
}
