@ECHO OFF
REM Adds the vendor\bin folder to the path
REM This simplifies running Phing from poject root 
SET PATH=%~dp0vendor\bin;%PATH% 
