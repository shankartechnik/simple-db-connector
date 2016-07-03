<?php
include 'db.php';
$new_db = new DB();

//insert code call
$args = array(
 'table_name' => 'users',
 'fields_values' => array( 'username' => 'sample', 'email' => 'sample@sample.com', 'password'=> 'xxxxxxxxx' ),
 'created_modified' => 'yes'
);
$insert_query = $new_db->insert( $args );

//select code call
$args_select = array( 
				'table_name' => 'users', 
				'to_select' => '*', 
				'where_condition' => array('username' => 'sample', 'password'=> 'xxxxxxxxxxx'), 
				'limit_set' => '1'
			);
$select = $new_db->select( $args_select );
if( $select )
{
	foreach( $select as $select_value )
	{
		echo $select_value->username;
	}
}