#!/bin/bash

loop=0
endLoop=4
response=7 # node not available
wait=1

while [ ${response} != 0 ] && [ ${loop} != ${endLoop} ]; do
    curl -s -XHEAD http://localhost:9200
    response=$?
    loop=$(( $loop + 1))
    wait=$(( $wait*2))
    sleep ${wait}
done

if [ ${loop} == ${endLoop} ]; then
    echo "elasticsearch is not available"
    exit 1;
else
    echo "elasticsearch is available, starting php test"
fi