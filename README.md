# WindSpots-Status
WindSpots Weather Stations Status

Status page give latest information sent by a station

Each minute, when the station is up, the station send a report on http://status.windspots.org/station_report.php via a POST

POST data:

<pre>
  station Station ID			          6 Characters
  date    Station time and date  		14 Charaters PHP "YmdHis"
  data    Array
    stationtime   Station Time HHMMSS
    direction     Wind Direction in °
    speed         Wind Speed in knts
    averagespeed  Wind Average Speed in knts
    temperature   Temperature in °C
    barometer     Barometer in hpa
    battery       Battery level (only Solar)
    name          Station Name
    imageage      Image Age in seconds
  location  Array
    altitude      Altitue in meter
    latitude
    longitude
 </pre>

station_report store data in SQLite database (insert or if station exists update)

<pre>
  Table station_report
		station		TEXT	UNIQUE
		name		TEXT
		stndate		TEXT
		stntime		TEXT
		direction	TEXT
		speed		TEXT
		averagespeed	TEXT
		temperature	TEXT
		barometer	TEXT
		battery		TEXT
		imageage	TEXT
		altitude	TEXT
		latitude	TEXT
		longitude	TEXT
</pre>

http://status.windspots.org/index.php	display a web page with last data received
	the <a> link to the image is pointing to www site and may be not accurate
