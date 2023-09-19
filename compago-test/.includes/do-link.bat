@Echo OFF
Echo Launch dir: "%~dp0"
Echo "requires cmd admin"
mklink /D "%~dp0\Compago" "Z:\elixom-micro-framework\Compago"
mklink /D "%~dp0\ELIX" "Z:\LIBRARIES\ELIX"
mklink /D "%~dp0\ELI" "Z:\LIBRARIES\ELI"
mklink /D "%~dp0\JOL" "Z:\stjago.new\.includes\JOL"
mklink /D "%~dp0\JNEW" "Z:\stjago.new\.includes\JNEW"
mklink /D "%~dp0SAP" "Z:\SMS\.includes\SAP"
Pause&Exit