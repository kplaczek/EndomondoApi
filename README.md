EndomondoApi
============

Simple Endomondo Api Class


Quick and simple class designed to access some of the Endomondo data using the same way that mobile endomondo app is using. 
Since endomondo doesn't provide any official api I had to improvise and retrieve that information some other way. Many interesting informations can be found that and only that way ex. gps coords of a place wnere person were when song started to play. 0.o! Creepy. I know. 


This is a early version and will be developed.


- [ ] Update to the latest version
  - [ ] reverse engineering mobile application packets
  - [ ] tidy up code


## Example 1 

Simple example using some basic features of endomondo class. This reads some recent workouts and one by one lat and lngs are glued into one big array of coordinates. After that the array is being soften by including to final array only some points so the fina array contains at most 10k elements. It is easier for leaflet heatmap to render 10k than ex. 100k and it doesnt change the appearance too much.


![example1](http://techtube.pl/images/endomondo_example1.jpg)
