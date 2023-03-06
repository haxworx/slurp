#!/bin/sh
mkdir certs
openssl req -x509 -new -out certs/mycert.crt -keyout certs/mycert.key -days 365 -newkey rsa:4096 -sha256 -nodes
