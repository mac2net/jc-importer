<?php
class UserMapperTest extends WP_UnitTestCase{

	var $importer;

	public function setUp(){
		parent::setUp();        
        $this->importer = $GLOBALS['jcimporter'];
	}

    /**
     * @group core
     * @group mapper
     */
    public function test_insert(){

        $mapper = new JC_UserMapper();
        $result = $mapper->insert('user', array(
            'user_email' => 'james@test.com',
            'user_login' => 'jcadmin'
        ));
        $user = new WP_User_Query(array(
            'search' => $result,
            'search_columns' => array('ID')
        ));

        $this->assertEquals(1, $user->total_users);
    }

    /**
     * @expectedException JCI_Exception
     * @group core
     * @group mapper
     */
    public function test_insert_no_login(){

    	$mapper = new JC_UserMapper();
        $result = $mapper->insert('user', array(
            'user_email' => 'james@test.com'
        ));
  	
    }
}