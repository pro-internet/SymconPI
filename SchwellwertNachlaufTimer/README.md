# Documentation
Modul zum Schalten eines beliebigen Ger�ts, abh�ngig von einem Senorwert und einem Schwellwert, unter Verwendung einer Nachlaufzeit.

- Sensor:
Der Sensor, dessen Wert �berpr�ft werden soll. 

- Schwellwert:
Der Schwellwert wird in der Konsole oder dem WebFront festgelegt.

Falls der Sensorwert den Schwellwert �berschreitet, wird der Status auf "AN" geschaltet und der/n Variable/n im Targets-Ordner wird der Wert, der bei AN festgelegt wurde, zugewiesen und es wird ein Timer mit einer Nachlaufzeit, die ebenfalls in der Konsole oder im Webfront festgelegt wird, gestartet.

Falls der Schwellwert nun unter den Sollwert ger�t, der Timer allerdings noch nicht abgelaufen ist, wird der Status <u>nicht</u> auf AUS geschaltet.

Falls der Schwellwert nun unter den Sollwert ger�t und der Timer abgelaufen ist, wird der Status auf AUS geschaltet und die Variable/n im Targets-Ordner wird/werden auf den im Modul festgelegten AUS Wert gesetzt.