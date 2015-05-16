<?php
exit;


abstract class Database_Driver extends Module_Database_Driver{}
abstract class Ex_Database_Driver extends Module_Database_Driver{}

class Database_Expression extends Module_Database_Expression{}
class Ex_Database_Expression extends Module_Database_Expression{}

class Database_QueryBuilder extends Module_Database_QueryBuilder{}
class Ex_Database_QueryBuilder extends Module_Database_QueryBuilder{}

abstract class Database_Result extends Module_Database_Result{}
abstract class Ex_Database_Result extends Module_Database_Result{}

abstract class Database_Transaction extends Module_Database_Transaction{}
abstract class Ex_Database_Transaction extends Module_Database_Transaction{}

class Database extends Module_Database{}
class Ex_Database extends Module_Database{}