#!/usr/bin/env bash

JMETER_VERSION="${JMETER_VERSION:-2.13}"

# 1 = Version
download() {
    VERSION="$1"

    DIR="apache-jmeter-${VERSION}"
    FILE="${DIR}.tgz"
    URL="https://archive.apache.org/dist/jmeter/binaries/${FILE}"

    if [ -f $FILE ];
    then
       echo "File $FILE exists."
    else
       echo "File $FILE does not exist. Downloading JMeter from $URL ..."
       curl -s -O $URL
       echo "Downloaded!"
    fi
    tar -zxf $FILE
    rm $FILE
    echo "Extracted JMeter"

    EXTENSIONS_DIR="JMeterPlugins-Extras-1.2.1"
    EXTENSIONS_FILE="${EXTENSIONS_DIR}.zip"
    EXTENSIONS_URL="http://jmeter-plugins.org/files/${EXTENSIONS_FILE}"

    if [ -f "$EXTENSIONS_FILE" ]
    then
        echo "File ${EXTENSION_FILE} exists."
    else
        echo "File ${EXTENSION_FILE} does not exist. Downloading JMeter Plugins from ${EXTENSIONS_URL} ..."
        curl -s -O "${EXTENSIONS_URL}"
    fi

    unzip -oq "${EXTENSIONS_FILE}" -x "LICENSE" "README" -d "${DIR}"
    rm $EXTENSIONS_FILE
    echo "Extracted JMeterPlugins-Extras"
}

download $JMETER_VERSION
