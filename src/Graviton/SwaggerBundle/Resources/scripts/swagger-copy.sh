#!/bin/bash

DIR=$(cd $(dirname $0) && pwd)
TARGETDIR=$DIR/../../../../../web/explorer
SOURCEDIR=$DIR/../../../../../vendor/libgraviton/swagger-ui/dist

echo "Warning: swagger-copy.sh is deprecated, please switch to using composer scripts to do this"

if [ ! -d "$SOURCEDIR" ]; then
  SOURCEDIR=$DIR/../../../../../../../libgraviton/swagger-ui/dist
fi

mkdir -p $TARGETDIR
cp -R $SOURCEDIR/* $TARGETDIR/


