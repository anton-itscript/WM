max1=0
max2=0
min1=85
min2=0
while true; do
	cpuTemp0=$(cat /sys/class/thermal/thermal_zone0/temp)
    cpuTemp1=$(($cpuTemp0/1000))
    cpuTemp2=$(($cpuTemp0/100))
    cpuTempM=$(($cpuTemp2 % $cpuTemp1))

	if [ $cpuTemp1 -gt $max1 ]
	then
		max1=$cpuTemp1
		max2=$cpuTempM
	elif [ $cpuTemp1 -eq $max1 ] && [ $cpuTempM -gt $max2 ]
	then
		max2=$cpuTempM
	fi
	if [ $cpuTemp1 -lt $min1 ]
	then
		min1=$cpuTemp1
		min2=$cpuTempM
	elif [ $cpuTemp1 -eq $min1 ] && [ $cpuTempM -lt $min2 ]
	then
		min2=$cpuTempM
	fi

	printf "\r CPU $cpuTemp1.$cpuTempM'C ($min1.$min2'C/$max1.$max2'C) GPU $(/opt/vc/bin/vcgencmd measure_temp)"

	sleep 1
done

