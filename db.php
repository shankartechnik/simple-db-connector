<?php
/*
php db connector v1.0,
code by K R Gowri Shankar,
for easy integration of any database with php,
License: MIT,
Open source code can be modified and redistibuted,
Thanks for using, K R Gowri Shankar.
*/

class DB
{
	//parsing the ini file
	protected $filename = 'db_conf.ini';
	protected $db;
	
	//varibale for end of line
	public $error_rec_eol = PHP_EOL;
	
	//global variables declaration
	public $database;
	public $connection;

	//construct function to make database connection as soon as class is invoked
	public function __construct()
	{
		//parse the db_conf file
		$this->db = parse_ini_file( $this->filename, true );
		
		//database connection variables
		$username = $this->db['username'];
		$password = $this->db['password'];
		$dbname   = $this->db['dbname'];
		$hostname = $this->db['hostname'];
		$dbtype   = $this->db['dbtype'];
		
		//check values exist for parameter
		if( $dbtype && $hostname && $dbname && $username && ( $password || $password == '' ) )
		{
			try
			{
				//pdo bridging, for this your php should have pdo extension enabled
				$this->database = new PDO("$dbtype:host=$hostname;dbname=$dbname", $username, $password);
				$this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			}
			catch( PDOException $e )
			{
				//database connection log
				error_log( $e.$this->error_rec_eol , 3, "db_error.txt" );
			}
		}
		else
		{
			//parameter missing log
			error_log( 'DB Configuration is missing please set correctly.'.$this->error_rec_eol, 3, "db_error.txt" );
			return false;
		}
	}
	
	/*insert function,
	parameter is passed as an array to the function.
	for example,
	
	array( 'table_name'=>'sample_table_name', //we can use this if insert into values not for set 'primary_id'=>'id/ID/user_id' -----------//, 'fields_values'=> array( 'field_one'=>'value_one', 'filed_two'=>'value_two', 'field_three'=>'value_three' ), 'created_modified'=>'yes/no' )
	
	required value in the array table_name, fields_values
	
	if it is '/' then any one value or kind of the value should be used here	
	as default id will be used and if it is users table then it  will be ID
	*/
	public function insert( array $array )
	{
		if( !empty( $array ) )
		{				
			if( isset( $array['table_name'] ) )
			{
				try
				{					
					//primary key set is disabled for now
					/*if( isset( $array['primary_id'] ) )
					{
						$primary_id_set = $array['primary_id'];
					}
					elseif( isset( $array['primary_id'] ) || !isset( $array['primary_id'] ) && $array['table_name'] == 'users'  )
					{
						$primary_id_set = 'ID';
					}
					else
					{
						$primary_id_set = 'id';   
					}*/
					
					if( isset( $array['fields_values'] ) )
					{
						$count = count( $array['fields_values'] );
						$i = 1;
						$query_construct = '';
						
						foreach( $array['fields_values'] as $key => $value )
						{
							if( $i  == $count )
							{
								$query_construct .= $key.' = "'.$value.'"';
							}
							else
							{
								$query_construct .= $key.' = "'.$value.'",';
							}
							
							$i++;
						}						
					}
					else
					{
						error_log( 'No column name and values are passed parameter.'.$this->error_rec_eol, 3, "db_error.txt" );
						return false;
					}
					
					
					if( isset( $array['created_modified'] ) )
					{
						if( $array['created_modified'] == 'yes' )
						{
							$date = date('Y-m-d G:i:s');
							$created_modified = 'created_at = "'.$date.'", modified_at = "'.$date.'"';
						}
						else
						{
							$created_modified = '';
						}
					}
					else
					{
						$created_modified = '';
					}
					
					if( $created_modified )
					{
						$sql = 'INSERT INTO '.$array['table_name'].' SET '.$query_construct.','.$created_modified;
					}
					else
					{
						$sql = 'INSERT INTO '.$array['table_name'].' SET '.$query_construct;
					}
					
					//execution block
					$this->database->beginTransaction();
					$executing_set = $this->database->prepare( $sql );
					$executing_set->execute();
					$commited_query = $this->database->commit();
					
					if( $commited_query )
					{
						return true;
					}
					
				}
				catch( PDOException $e )
				{
					$this->database->rollback();
					error_log( $e.$this->error_rec_eol, 3, "db_error.txt" );
					return false;
				}
				
			}
			else
			{
				error_log( 'No table name in passed parameter.'.$this->error_rec_eol, 3, "db_error.txt" );
				return false;
			}
		}
		else
		{
			error_log( 'No passed parameter in insert function.'.$this->error_rec_eol, 3, "db_error.txt" );
			return false;
		}
	
	}

	/*select function,
	array('table_name' => 'sample_table_name', 'to_select' => '*(/)array('column_one','column_two')/needed', 'is_count' => 'yes/no/both(not required but replace for to select any one of two is required )',  'where_condition' => 'array( 'select_one'=>'value', 'select_two'=>'value' )', 'limit_set' => 'integer like 10 or 0,10' )
	
	either to_select or is_count is required, if count is yes to_select is ignored, if both to_select and is_count required then set it to both and you can get the count as $returnarray['count ']
	*/
	public function select( array $array )
	{
		if( !empty( $array ) )
		{				
			if( isset( $array['table_name'] ) )
			{
				try
				{
					
					if( isset( $array['to_select'] ) )
					{
						if ( is_array( $array['to_select'] ) or ( $array['to_select'] instanceof Traversable ) )
						{
							$select_column = implode(',', $array['to_select'] ).' FROM '.$array['table_name'];
						}
						else
						{
							$select_column = '*'.' FROM '.$array['table_name'];
						}
					}
					else
					{
						error_log( 'No column to select if no column please set it to *.'.$this->error_rec_eol, 3, "db_error.txt" );
						return false;
					}
					
					if( isset( $array['where_condition'] ) )
					{
						$count = count( $array['where_condition'] );
						$i = 1;
						$query_construct = '';
						
						foreach( $array['where_condition'] as $key => $value )
						{
							if( $i  == $count )
							{
								$query_construct .= ' AND '.$key.' = "'.$value.'"';
							}
							elseif( $i == 1 )
							{
								$query_construct .= 'WHERE '.$key.' = "'.$value.'"';
							}
							elseif( $i > 1 )
							{
								$query_construct .= 'AND '.$key.' = "'.$value.'"';
							}
							
							$i++;
						}						
					}
					else
					{
						error_log( 'No column name and values are passed parameter.'.$this->error_rec_eol, 3, "db_error.txt" );
						return false;
					}
					
					if( isset( $array['is_count'] ) )
					{
						if( isset($array['limit_set'] ) )
						{
							$limit_set = 'LIMIT '.$array['limit_set'];
						}
						else
						{
							$limit_set = '';
						}
						
						if( $array['is_count'] == 'yes' )
						{
							$sql = 'SELECT * '.$query_construct.' '.$limit_set;
						}
						elseif( $array['is_count'] == 'both' )
						{
							$sql = 'SELECT count(*) as count '.$select_column.' '.$query_construct.' '.$limit_set;
						}
						elseif(  $array['is_count'] == 'no' )
						{
							$sql = 'SELECT '.$select_column.' '.$query_construct.' '.$limit_set;
						}
						else
						{
							$sql = 'SELECT '.$select_column.' '.$query_construct.' '.$limit_set;
						}
						
					}
					else
					{
						if( isset($array['limit_set'] ) )
						{
							$limit_set = 'LIMIT '.$array['limit_set'];
						}
						else
						{
							$limit_set = '';
						}
						
						$sql = 'SELECT '.$select_column.' '.$query_construct.' '.$limit_set;
					}					
					
					//execution block
					$this->database->beginTransaction();
					$executing_set = $this->database->prepare( $sql );
					$executing_set->execute();
					$result = $executing_set->setFetchMode(PDO::FETCH_OBJ); 
					$commited_query = $this->database->commit();
					
					if( $commited_query )
					{
						return $executing_set->fetchAll();
					}
				}
				catch( PDOException $e )
				{
					$this->database->rollback();
					error_log( $e.$this->error_rec_eol, 3, "db_error.txt" );
					return false;
				}
				
			}
			else
			{
				error_log( 'No table name in passed parameter.'.$this->error_rec_eol, 3, "db_error.txt" );
				return false;
			}
		}
		else
		{
			error_log( 'No passed parameter in insert function.'.$this->error_rec_eol, 3, "db_error.txt" );
			return false;
		}

	}
	
	/*update funcion,
	
	*/
	public function update()
	{
		if( !empty( $array ) )
		{				
			if( isset( $array['table_name'] ) )
			{
				try
				{
					$this->database->beginTransaction();
					
					if( $commited_query )
					{
						return true;
					}
				}
				catch( PDOException $e )
				{
					$this->database->rollback();
					error_log( $e.$this->error_rec_eol, 3, "db_error.txt" );
					return false;
				}
				
			}
			else
			{
				error_log( 'No table name in passed parameter.'.$this->error_rec_eol, 3, "db_error.txt" );
				return false;
			}
		}
		else
		{
			error_log( 'No passed parameter in insert function.'.$this->error_rec_eol, 3, "db_error.txt" );
			return false;
		}
	
	}
	
	public function delete()
	{
		if( !empty( $array ) )
		{				
			if( isset( $array['table_name'] ) )
			{
				try
				{
					$this->database->beginTransaction();
					
					if( $commited_query )
					{
						return true;
					}
				}
				catch( PDOException $e )
				{
					$this->database->rollback();
					error_log( $e.$this->error_rec_eol, 3, "db_error.txt" );
					return false;
				}
				
			}
			else
			{
				error_log( 'No table name in passed parameter.'.$this->error_rec_eol, 3, "db_error.txt" );
				return false;
			}
		}
		else
		{
			error_log( 'No passed parameter in insert function.'.$this->error_rec_eol, 3, "db_error.txt" );
			return false;
		}
	
	}
}
?>