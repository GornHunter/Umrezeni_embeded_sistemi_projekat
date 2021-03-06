#include <atmel_start.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "ftn_vip_lib/usbUART.h"
#include "ftn_vip_lib/debugUART.h"
#include "ftn_vip_lib/nbiotUART.h"
#include "ftn_vip_lib/timer_1ms.h"
#include "ftn_vip_lib/Quectel_BC68.h"

//on-board sensors:
#include "ftn_vip_lib/SHTC3.h"
#include "ftn_vip_lib/bmp280.h"
#include "ftn_vip_lib/BH1750FVI.h"
#include "ftn_vip_lib/LIS2DE12.h"

void sensorTest(void)
{
	while (1)
	{
		char str[64];
		//SHTC3
		SHTC3_update();
		uint32_t shtc3_hum = SHTC3_raw2Percent() * 100;
		uint32_t shtc3_temp = SHTC3_raw2DegC() * 100;
		sprintf(str, "SHTC3 ->\tT = %d.%d C\tH = %d.%d \%\r\n", shtc3_temp / 100, shtc3_temp % 100, shtc3_hum / 100, shtc3_hum % 100);
		usbUARTputString(str);
		
		//BMP280
		int32_t bmp280_temp, bmp280_pres;
		bmp280_measure(&bmp280_temp, &bmp280_pres);
		sprintf(str, "BMP280 ->\tT = %d.%d C\tP = %d.%d mBar\r\n", bmp280_temp / 100, bmp280_temp % 100, bmp280_pres / 100, bmp280_pres % 100);
		usbUARTputString(str);
		
		//BH1750FVI
		uint32_t lum = BH1750FVI_GetLightIntensity();
		sprintf(str, "BH1750FVI ->\tL = %ld lux\r\n\r\n", lum);
		usbUARTputString(str);
		
		delay(1000);
	}
}

void accelTest(void)
{
	while (1)
	{
		accel_3axis accel;
		char str[32];
		LIS2DE12_getAccel(&accel);

		sprintf(str, "%d,%d,%d\r\n", accel.accel_X, accel.accel_Y, accel.accel_Z);
		usbUARTputString(str);
		
		delay(2000);
	}
}

int main()
{
	/* Initializes MCU, drivers and middleware */
	atmel_start_init();
	debugUARTdriverInit();
	usbUARTinit();
	nbiotUARTinit();
	timer_1ms_init();

	
	char str[256];
	delay(3000);
	sprintf(str, "--- FTN-VIP NB-IoT ---\r\n");
	usbUARTputString(str);
	setLEDfreq(FREQ_1HZ);
	enableLED();

	//init sensors
	SHTC3_begin();
	bmp280_init();
	BH1750FVI_begin();
	LIS2DE12_init();
	
	//accelTest();
	//sensorTest();
	
	//NB-IoT connect
	BC68_debugEnable(true, DEBUG_USB);
	BC68_connect();
	
	setLEDfreq(FREQ_1HZ);
	
	
	while (1)
	{
		while (gpio_get_pin_level(BUTTON));
		
		char payload[256], response[256], data[256];
		
		SHTC3_update();
		uint32_t shtc3_hum = SHTC3_raw2Percent() * 100;
		uint32_t shtc3_temp = SHTC3_raw2DegC() * 100;
		
		int32_t bmp280_temp, bmp280_pres;
		bmp280_measure(&bmp280_temp, &bmp280_pres);
		
		uint32_t lum = BH1750FVI_GetLightIntensity();
		
		sprintf(data, "{\"temperatura\":\"%d.%d\", \"vlaznost\":\"%d.%d\", \"pritisak\":\"%d.%d\", \"osvetljenje\":\"%ld\"}", shtc3_temp/100, shtc3_temp%100, shtc3_hum/100, shtc3_hum%100, bmp280_pres / 100, bmp280_pres % 100, lum);
		sprintf(payload, "POST /tcp_server.py HTTP/1.1\r\nHost: 199.247.17.15\r\nContent-Type: application/json\r\nContent-Length: %d\r\n\r\n%s", strlen(data), data);
		
		
		char socket = BC68_openSocket(1, TCP);
		
		if(connect_TCP(socket, "199.247.17.15", 50051)){
			delay(1000);
			int16_t rxBytes = BC68_tx_TCP(socket, strlen(payload), payload);
			BC68_rx_TCP(response, rxBytes, socket);
		}

		BC68_closeSocket(socket);

		char* res = strtok(response, "\n");
		res = strtok(NULL, "\n");
		res = strtok(NULL, "\n");
		res = strtok(NULL, "\n");
		sprintf(str, "Server response -> %s\r\n", res);
		usbUARTputString(str);
		
		delay(1000);
	}
}

