<?php
require_once 'vendor/autoload.php';
use Thant\Helpers\InputFilter;

/**
 * Created by PhpStorm.
 * User: kthant
 * Date: 2/2/2017
 * Time: 4:33 PM
 */
class InputFilterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test overall operation of input filtering class
	 */
	public function testFilteringWithBadInputs()
	{
		$sanitizer = new InputFilter();
		$sanitizer->setInputs(array(
			'field1' => '100abc',
			'field2' => 'kevin@gmailcom'
		))
		          ->setRequired('field1')
		          ->filter('field1', array(
			          FILTER_VALIDATE_INT
		          ))
		          ->setRequired('field2')
		          ->filter('field2', array(
			          FILTER_VALIDATE_EMAIL
		          ), 'Valid email required')
		          ->setRequired('field3', 'Field3 is required')
		          ->filter('field3', array(
			          FILTER_SANITIZE_STRING
		          ))
		          ->sanitize();

		$this->assertTrue($sanitizer->hasErrors());
		$errors = $sanitizer->getErrors();

		$this->assertNotEmpty($errors);

		$this->assertArrayHasKey('field1', $errors);

		$this->assertArrayHasKey('field2', $errors);
		$this->assertEquals('Valid email required', $errors['field2']);

		$this->assertArrayHasKey('field3', $errors);
		$this->assertEquals('Field3 is required', $errors['field3']);
	}

	public function testFilteringWithGoodInputs()
	{
		$sanitizer = new InputFilter();
		$sanitizer->setInputs(array(
			'field1' => ' 100 ',
			'field2' => 'kevin@gmail.com',
			'field3' => 'New York'
		))
		          ->setRequired('field1')
		          ->filter('field1', array(
			          FILTER_VALIDATE_INT
		          ))
		          ->setRequired('field2')
		          ->filter('field2', array(
			          FILTER_VALIDATE_EMAIL
		          ), 'Valid email required')
		          ->setRequired('field3', 'Field3 is required')
		          ->filter('field3', array(
			          FILTER_SANITIZE_STRING
		          ))
		          ->sanitize();

		$this->assertEmpty($sanitizer->hasErrors());

		$data = $sanitizer->getClean();
		$this->assertNotEmpty($data);
		$this->assertInternalType('array', $data);
		$this->assertArrayHasKey('field1', $data);
		$this->assertEquals(100, $data['field1']);

		$this->assertArrayHasKey('field2', $data);
		$this->assertEquals('kevin@gmail.com', $data['field2']);

		$this->assertArrayHasKey('field3', $data);
		$this->assertEquals('New York', $data['field3']);
	}
}