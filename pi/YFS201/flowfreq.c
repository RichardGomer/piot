#include <stdio.h>
#include <stdlib.h>
#include <wiringPi.h>

int sensorPin;

volatile int pulseCount;

float freq;
unsigned long oldTime;

void pulseCounter()
{
  pulseCount++;
}

void setup(int argc, char *argv[])
{
  wiringPiSetupGpio();

  if(argc < 2) {
	printf("USAGE: ./flowfreq bcm_sense_pin\n");
	exit(0);
  }

  // Broadcom pin number must be passed as first argument
  char* p = NULL;
  sensorPin = strtol(argv[1], &p, 10);

  printf("Freq monitor attached to BCOM %i\n", sensorPin);

  pinMode(sensorPin, INPUT);
  digitalWrite(sensorPin, HIGH);

  pulseCount        = 0;
  freq              = 0.0;
  oldTime           = 0;

  wiringPiISR(sensorPin, INT_EDGE_FALLING, &pulseCounter);

}

void loop()
{
  int now = millis();

  if((now - oldTime) >= 1000)    // Only process counters once per second
  {
    freq = ((1000.0 / (now - oldTime)) * pulseCount);

    printf("%f\n", freq);

    oldTime = now;

    pulseCount = 0;
  }
}

int main(int argc, char *argv[])
{
  setup(argc, argv);

  while(1) {
    loop();
    delay(100);
  }
}



