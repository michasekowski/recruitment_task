<?php

class RecruitmentTaskControllerTest extends \PHPUnit\Framework\TestCase {
	public function testProcessWithNoJson() {
		$controller = new App\Controller\RecruitmentTaskController();
		$result = $controller->process();
		$this->assertStringContainsString("not found", $result);
	}
}