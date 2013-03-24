@echo off

rem -------------------------------------------------------------
rem build script for Windows.
rem
rem This is the bootstrap script for running build on Windows.
rem
rem @author Jonwang <jonwang@myqee.com>
rem @link http://www.myqee.com/
rem @license http://www.myqee.com/license/
rem -------------------------------------------------------------

@setlocal

set BUILD_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND="php.exe"

%PHP_COMMAND% "%BUILD_PATH%merge-assets" %*

@endlocal

pause