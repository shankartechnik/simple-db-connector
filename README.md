# simple-db-connector

PHP code to connect to DB this V1 has code to insert and select and it will be modified for other operation in future, the code has constructor 
to connect to the db automatically when class is invoked, and insert and select object which gets array as an parameter ie the arguments.

Insert and Select arguments are different in array key and value,

Insert function,
parameter is passed as an array to the function.
for example,

array( 'table_name'=>'sample_table_name', //we can use this if insert into values not for set 'primary_id'=>'id/ID/user_id' -----------//, 'fields_values'=> array( 'field_one'=>'value_one', 'filed_two'=>'value_two', 'field_three'=>'value_three' ), 'created_modified'=>'yes/no' )

required value in the array table_name, fields_values

if it is '/' then any one value or kind of the value should be used here	
as default id will be used and if it is users table then it  will be ID


Select function,

array('table_name' => 'sample_table_name', 'to_select' => '*(/)array('column_one','column_two')/needed',
'is_count' => 'yes/no/both(not required but replace for to select any one of two is required )',  
'where_condition' => 'array( 'select_one'=>'value', 'select_two'=>'value' )', 'limit_set' => 'integer like 10 or 0,10' )
	
either to_select or is_count is required, if count is yes to_select is ignored, if both to_select and is_count required then set it to
both and you can get the count as $returnarray['count ']


Example use,

Include the db.php file,
include 'db.php';

Initialize the class,
$new_db_connection = new DB();

Insert function arguments and call,

$args = array(
 'table_name' => 'users',
 'fields_values' => array( 'username' => 'ncnsasdfsdfmple', 'email' => 'sampbvbvhle@sample.com', 'password'=> 'xxxxxxxxxxx' ),
 'created_modified' => 'yes'
);

$insert_query = $new_db_connection->insert( $args );

Select function arguments and call,

$args = array( 
				'table_name' => 'users', 
				'to_select' => '*', 
				'where_condition' => array('username' => 'sample', 'password'=> 'xxxxxxxxxxx'), 
				'limit_set' => '1'
			);
$select = $new_db_connection->select( $args );
if( $select )
{
	foreach( $select as $select_value )
	{
		echo $select_value->username;
	}
}


