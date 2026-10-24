@echo off

start "" "C:\wamp64\wampmanager.exe"

timeout /t 15 /nobreak > nul

start "" "http://localhost/tontine-hdj"

exit