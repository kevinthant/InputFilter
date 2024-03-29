<?php
namespace Thant\Helpers;

/**
 * Created by PhpStorm.
 * User: Kevin Thant
 * Date: 2/2/2017
 */

use Thant\Helpers\InputFilter\IFilterCallback;

class InputFilter
{
    protected $inputs = array();
    protected $cleaned = array();
    protected $errors = array();
    protected $required = array();
    protected $filters = array();

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setInputs(array $data)
    {
        $this->inputs = $data;
        $this->cleaned = array();
        //trim for string values
        foreach ($this->inputs as $key => $val) {
            if (is_string($val)) {
                $this->inputs[$key] = trim($val);
            }

        }

        return $this;
    }

    /**
     * @return array
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @param      $key
     * @param null $errorMessage
     *
     * @return $this
     */
    public function setRequired($key, $errorMessage = null)
    {
        $this->required[$key] = $errorMessage == null ? "$key is required" : $errorMessage;
        return $this;
    }

    /**
     * Clear any required settings
     * @return $this
     */
    public function clearRequired()
    {
        $this->required = array();
        return $this;
    }

    /**
     * @param        $key
     * @param array $filters
     * @param string $errorMessage
     *
     * @return $this
     */
    public function filter($key, array $filters, $errorMessage = 'Invalid input')
    {
        $this->filters[$key] = array($filters, $errorMessage);
        return $this;
    }

    /**
     * Run the input filtering and validations
     */
    public function sanitize()
    {
        $this->errors = array();
        foreach ($this->filters as $key => $val) {
            list($filters, $errorMessage) = $val;
            $this->_filter($key, $filters, $errorMessage);
        }
    }

    /**
     * See the list of available filters on http://php.net/manual/en/filter.filters.php
     * @param       $key
     * @param array $filters
     * @param string|null $errorMessage
     *
     * @return $this
     */
    protected function _filter($key, array $filters, $errorMessage)
    {
        $val = $this->getValue($key, $this->inputs);

        if (is_string($val) && trim($val) == '') {
            $val = false;
        }

        if ($val === false) {
            if (array_key_exists($key, $this->required)) {
                $this->errors[$key] = $this->required[$key];
            }
            return $this;
        }


        foreach ($filters as $filter => $options) {

            if ($options instanceof IFilterCallback) {
                $options = array('options' => [$options, 'filter']);
            }

            if (is_array($options)) {
                $val = filter_var($val, $filter, $options);
            } else {
                $val = filter_var($val, $options);
            }

            if ($filter !== FILTER_VALIDATE_BOOLEAN && is_array($val) && array_search(FALSE, $val) >= 0) {
                $this->errors[$key] = $errorMessage;
                break;
            }

            if ($val === false) {
                $this->errors[$key] = $errorMessage;
                break;
            }
        }
        $this->setValue($key, $val, $this->cleaned);
    }

    /**
     * @return array
     */
    public function getClean()
    {
        return $this->cleaned;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    protected function getValue($key, $inputs)
    {
        $pattern = '=^([\w\-]+)((\[[\w\-]+\])+)$=';

        if (preg_match($pattern, $key, $matches)) {
            $parent = $matches[1];

            if (!array_key_exists($parent, $inputs)) {
                return false;
            }

            $inputs = $inputs[$parent];
            $key = preg_replace('=^\[([\w\-]+)\](.*)$=', '$1$2', $matches[2]);

            return $this->getValue($key, $inputs);
        } elseif (!array_key_exists($key, $inputs)) {
            return false;
        } else {
            return $inputs[$key];
        }
    }

    protected function setValue($key, $value, &$data)
    {
        $pattern = '=^([\w\-]+)((\[[\w\-]+\])+)$=';

        if (preg_match($pattern, $key, $matches)) {
            $parent = $matches[1];

            if (!array_key_exists($parent, $data)) {
                $data[$parent] = array();
            }


            $key = preg_replace('=^\[([\w\-]+)\](.*)$=', '$1$2', $matches[2]);

            return $this->setValue($key, $value, $data[$parent]);
        } else {
            $data[$key] = $value;
        }
    }
}