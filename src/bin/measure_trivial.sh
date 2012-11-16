#!/bin/bash
ez_last_attribute_count="0"
echo ""
while true; do
    ez_current_attribute_count="$(echo 'SELECT COUNT(*) FROM ezcontentobject_attribute' | mysql -u root ezpublish | tail -n1)"
    let 'ez_attribute_count_diff = ez_current_attribute_count - ez_last_attribute_count'
    echo -en "\r${ez_attribute_count_diff} attributes per second (${ez_current_attribute_count})                    "
    ez_last_attribute_count="${ez_current_attribute_count}"
    sleep 1;
done
