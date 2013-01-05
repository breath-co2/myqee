<?php
$config = array
(
    'admin' => array
    (
        'driver'             => Auth::DRIVER_DATABASE,
        'database'           => Model_Admin::DATABASE,
        'tablename'          => 'admin_member',
        'username_field'     => 'username',
        'password_field'     => 'password',
        'member_object_name' => 'ORM_Admin_Member_Data',
    ),
);