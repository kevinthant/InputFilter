A tiny PHP libary for input filtering and validation that can be used across with any framework or application. It uses the PHP native filters for filtering and validation. See more information on available list of PHP native filters:

http://php.net/manual/en/filter.filters.php


How to use it?
==============

```php
<?php
use Thant\Helpers\InputFilter;

$inputs = array(
            'field1' => ' 100 ',
            'field2' => 'kevin@gmail.com',
            'field3' => '4400-129-2210'
          );
                          
$sanitizer = new InputFilter();
$sanitizer->setInputs($inputs)
          ->setRequired('field1')
          ->filter('field1', array(
              FILTER_VALIDATE_INT
          ))
          ->setRequired('field2')
          ->filter('field2', array(
              FILTER_VALIDATE_EMAIL
          ), 'Valid email required')
          ->setRequired('field3', 'Field3 is required')
          //you can also filter a field by multiple filters in sequence
          ->filter('field3', array(
              FILTER_SANITIZE_STRING,
              FILTER_VALIDATE_REGEXP => [
                'options' => array(
                  'regexp' => '=^([0-9]{3})\-?([0-9]{3})\-?([0-9]{4})$='
                )
              ]
          ), 'Must be a valid US phone number')
          ->sanitize();
          
if($sanitizer->hasErrors())
{
  $errors = $sanitizer->getErrors(); //TODO do anything you want with error list failed for validation and filtering
}
else
{
  $data = $santizer->getClean(); //get the cleaned data
  $field1 = $data['field1'];
  $field2 = $data['field2'];
  $field3 = $data['field3'];
}

```

See more examples inside the test/InputFilterTest.php


How to write your own customer filter?
======================================

To use your own customer filter, you will be using PHP filter constant called "FILTER_CALLBACK" and then you can supply a callback function array. 
Currently there is a customer filter called DateTimeFilterCallback which implements IFilterCallback interface. Please take a look at that class as an example on how to implement your own custom filter.
 
Below is a bare-bone implementation of a custom filter callback class.
```php
<?php
use Thant\Helpers\InputFilter\IFilterCallback;
class MyCustomFilterCallback implements IFilterCallback
{
	
	public function filter($val)
	{
		//return false if validation or filtering fails.
		//Otherwise you can return a sanitized value back.
	}

}
```

Example code usage with custom filter:

```php
<?php

use Thant\Helpers\InputFilter;
use Thant\Helpers\InputFilter\DateTimeFilterCallback;

$inputs = array(
              'dateOfBirth' => '01/01/1990',
              'appointmentDate' => '06/01/2017'
            );
                            
$sanitizer = new InputFilter();
$sanitizer->setInputs($inputs)
          ->setRequired('dateOfBirth', 'Date of birth is required')
          ->filter('dateOfBirth', array(
            FILTER_CALLBACK => new DateTimeFilterCallback([
              'maxDate' => date('m/d/Y') //today date because birth date should be later than today,
              'format' => 'Y-m-d' //the final date format you want after filtering and validation pass
            ])
          ), 'Must be a valid date not later than today date')
          ->setRequired('appointmentDate')
          ->filter('appointmentDate', array(
            FILTER_CALLBACK => new DateTimeFilterCallback([
              'minDate' => date('m/d/Y'), //appointment date should be today or in the future
              'format' => 'Y-m-d'
            ])
          ), 'Appointment date must be a valid date starting from today')
          ->sanitize();
          
if($santizer->hasErrors())
{
  $errors = $santizer->getErrors(); // do input validation error handling 
}
else
{
  $data = $sanitizer->getClean();
  $dateOfBirth = $data['dateOfBirth']; //should be '1990-01-01'
  $appointmentDate = $data['appointmentDate']; //should be '2017-06-01'
}
```



Custom Filter using anonymous function
---------------------------------------

```php
$sanitizer = new InputFilter();
$sanitizer->filter('field1', array(
	FILTER_CALLBACK => [
		'options' => function($val)
		{
			//TODO check the $val, return false if filtering/validation fails. Otherwise, return the sanitized value
		}
	]
));
```

Example test case using anonymous function can also be found in the tests/InputFilterTest.php file.