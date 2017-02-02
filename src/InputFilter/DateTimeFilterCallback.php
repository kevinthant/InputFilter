<?php

namespace Thant\Helpers\InputFilter;

class DateTimeFilterCallback implements IFilterCallback
{

	protected $minDate = null;
	protected $maxDate = null;
	protected $format  = null;

	/**
	 * DateTimeFilterCallback constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		if(isset($options['minDate']))
		{
			$this->minDate = strtotime($options['minDate']);
		}

		if(isset($options['maxDate']))
		{
			$this->maxDate = strtotime($options['maxDate']);
		}

		//the date format to which you want to transform the given date after running this filter
		if(isset($options['format']))
		{
			$this->format = $options['format'];
		}
	}

	/**
	 * @param $val
	 *
	 * @return bool|false|string
	 */
	public function filter($val)
	{
		$time = strtotime($val);

		if(!$time)
		{
			return false;
		}

		if($this->minDate && $time < $this->minDate)
		{
			return false;
		}

		if($this->maxDate && $time > $this->maxDate)
		{
			return false;
		}

		if($this->format)
		{
			return date($this->format, $time);
		}

		return $val;
	}
}