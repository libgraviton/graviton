#!/bin/bash

DIR=$(cd $(dirname $0) && pwd)
TARGETDIR=$DIR/../../web/explorer
SOURCEDIR=$DIR/../../vendor/libgraviton/swagger-ui/dist

mkdir -p $TARGETDIR
cp -R $SOURCEDIR/* $TARGETDIR/


