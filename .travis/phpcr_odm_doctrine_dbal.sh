#!/bin/bash

set -e

DIR_NAME=`dirname $0`
CONSOLE_DIR=$DIR_NAME"/../tests/Fixtures/App/bin"

$CONSOLE_DIR"/console" doctrine:phpcr:init:dbal --drop --force
$CONSOLE_DIR"/console" doctrine:phpcr:repository:init
