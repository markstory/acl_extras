<?php

use PHPUnit\Framework\TestSuite;

/**
 * All Tests Test case.
 */
class AllTestsTest extends TestSuite {

/**
 * suite method, defines tests for this suite.
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Tests');
		$suite->addTestDirectoryRecursive(App::pluginPath('AclExtras') . 'Test' . DS . 'Case' . DS);

		return $suite;
	}
}
