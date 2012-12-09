<?php
$config = array( 
	'default' => array( 
		'current_page' => array( 
			'source' => 'default',  /* source: "query_string" or "route" or "default" */ 
			'key' => '0' ,
        ), 
        'total_items' => 0, 
        'items_per_page' => 10, 
        'view' => 'pagination/basic', 
        'auto_hide' => TRUE 
    ) 
);