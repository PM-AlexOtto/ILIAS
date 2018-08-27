<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Factory;

/**
 * Class ilTermsOfServiceDocumentCriterionAssignmentConstraintTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentCriterionAssignmentConstraintTest extends \ilTermsOfServiceCriterionBaseTest
{
	/**
	 * @return array
	 */
	public function criteriaAssignmentProvider(): array
	{
		$criterionAssignment1 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->getMock();

		$criterionAssignment1
			->expects($this->any())
			->method('getId')
			->willReturn(1);

		$criterionAssignment1
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_global_role');

		$criterionAssignment1
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['role_id' => 4]));

		$criterionAssignment2 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->getMock();

		$criterionAssignment2
			->expects($this->any())
			->method('getId')
			->willReturn(1);

		$criterionAssignment2
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_language');

		$criterionAssignment2
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['lng' => 'de']));

		$criterionAssignment3 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->disableOriginalConstructor()
			->getMock();

		$criterionAssignment3
			->expects($this->any())
			->method('getId')
			->willReturn(0);

		$criterionAssignment3
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_language');

		$criterionAssignment3
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['lng' => 'de']));

		$criterionAssignment4 = $this
			->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
			->disableOriginalConstructor()
			->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
			->getMock();

		$criterionAssignment4
			->expects($this->any())
			->method('getId')
			->willReturn(0);

		$criterionAssignment4
			->expects($this->any())
			->method('getCriterionId')
			->willReturn('usr_global_role');

		$criterionAssignment4
			->expects($this->any())
			->method('getCriterionValue')
			->willReturn($this->getCriterionConfig(['role_id' => 6]));

		return [
			[$criterionAssignment1, $criterionAssignment2, $criterionAssignment3, $criterionAssignment4]
		];
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	 */
	public function testConstraintAcceptanceWorksAsExpected(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	)
	{
		$document1 = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->setMethods(['criteria'])
			->getMock();

		$document1
			->expects($this->any())
			->method('criteria')
			->willReturn([$criterionAssignment1, $criterionAssignment2]);

		$constraint = new \ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$document1,
			new Factory()
		);

		$this->assertTrue($constraint->accepts($criterionAssignment1));
		$this->assertTrue($constraint->accepts($criterionAssignment2));
		$this->assertFalse($constraint->accepts($criterionAssignment3));
		$this->assertTrue($constraint->accepts($criterionAssignment4));
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	 */
	public function testConstraintCheckWorksAsExpected(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	)
	{
		$document1 = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->setMethods(['criteria'])
			->getMock();

		$document1
			->expects($this->any())
			->method('criteria')
			->willReturn([$criterionAssignment1, $criterionAssignment2]);

		$constraint = new \ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$document1,
			new Factory()
		);

		$raised = false;

		try {
			$constraint->check($criterionAssignment4);
		} catch (\UnexpectedValueException $e) {
			$raised = true;
		}

		$this->assertFalse($raised);

		try {
			$constraint->check($criterionAssignment3);
			$raised = false;
		} catch (\UnexpectedValueException $e) {
			$this->assertEquals('The passed assignment must be unique for the document!', $e->getMessage());
			$raised = true;
		}

		$this->assertTrue($raised);
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	 */
	public function testConstraintProblemDetectionWorksAsExpected(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	)
	{
		$document1 = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->setMethods(['criteria'])
			->getMock();

		$document1
			->expects($this->any())
			->method('criteria')
			->willReturn([$criterionAssignment1, $criterionAssignment2]);

		$constraint = new \ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$document1,
			new Factory()
		);

		$this->assertNull($constraint->problemWith($criterionAssignment1));
		$this->assertNull($constraint->problemWith($criterionAssignment2));
		$this->assertNull($constraint->problemWith($criterionAssignment4));
		$this->assertInternalType('string', $constraint->problemWith($criterionAssignment3));
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	 */
	public function testConstraintRestrictionWorksAsExpected(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	)
	{
		$document1 = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->setMethods(['criteria'])
			->getMock();

		$document1
			->expects($this->any())
			->method('criteria')
			->willReturn([$criterionAssignment1, $criterionAssignment2]);

		$dataFavtgory = new Factory();

		$constraint = new \ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$document1,
			$dataFavtgory
		);

		$ok = $dataFavtgory->ok($criterionAssignment1);
		$ok2 = $dataFavtgory->ok($criterionAssignment3);
		$error = $dataFavtgory->error('An error occurred');

		$result = $constraint->restrict($ok);
		$this->assertTrue($result->isOk());

		$result = $constraint->restrict($ok2);
		$this->assertTrue($result->isError());

		$result = $constraint->restrict($error);
		$this->assertSame($error, $result);
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	 */
	public function testConstraintProblemBuilderWorksAsExpected(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	)
	{
		$document1 = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->setMethods(['criteria'])
			->getMock();

		$document1
			->expects($this->any())
			->method('criteria')
			->willReturn([$criterionAssignment1, $criterionAssignment2]);

		$constraint = new \ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$document1,
			new Factory()
		);

		$newConstraint = $constraint->withProblemBuilder(function () {
			return 'phpunit';
		});
		$this->assertEquals('phpunit', $newConstraint->problemWith($criterionAssignment3));
	}

	/**
	 * @dataProvider criteriaAssignmentProvider
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
	 * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	 */
	public function testConstraintExposesCorrectErrorMessagesAfterMultiAccept(
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3,
		\ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment4
	)
	{
		$document1 = $this
			->getMockBuilder(\ilTermsOfServiceDocument::class)
			->disableOriginalConstructor()
			->setMethods(['criteria'])
			->getMock();

		$document1
			->expects($this->any())
			->method('criteria')
			->willReturn([$criterionAssignment1, $criterionAssignment2]);

		$constraint = new \ilTermsOfServiceDocumentCriterionAssignmentConstraint(
			$document1,
			new Factory()
		);

		$criterionAssignment5 = clone $criterionAssignment3;

		$constraint->accepts($criterionAssignment1);
		$constraint->accepts($criterionAssignment2);
		$constraint->accepts($criterionAssignment3);
		$constraint->accepts($criterionAssignment4);
		$constraint->accepts($criterionAssignment5);

		$this->assertEquals('The passed assignment must be unique for the document!', $constraint->problemWith($criterionAssignment3));
		$this->assertEquals('The passed assignment must be unique for the document!', $constraint->problemWith($criterionAssignment5));
	}
}