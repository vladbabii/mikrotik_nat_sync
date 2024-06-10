#!/bin/bash
docker buildx create --use --name mybuilder
docker buildx build --platform linux/amd64,linux/arm64,linux/arm/v7 -t vladbabii0/mikrotik_nat_sync:1.0 --push .
