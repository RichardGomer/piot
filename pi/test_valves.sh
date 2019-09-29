#!/bin/bash
echo "Setup"
gpio -g mode 17 out
gpio -g mode 18 out
gpio -g mode 27 out
gpio -g mode 28 out

echo "All off"
gpio -g write 17 1
gpio -g write 18 1
gpio -g write 27 1
gpio -g write 28 1
sleep 5

echo "All on"
gpio -g write 17 0
gpio -g write 18 0
gpio -g write 27 0
gpio -g write 28 0
sleep 5


echo "All off"
gpio -g write 17 1
gpio -g write 18 1
gpio -g write 27 1
gpio -g write 28 1


