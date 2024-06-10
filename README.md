# Mikrotik Nat Sync
* Docker Hub: https://hub.docker.com/r/vladbabii0/mikrotik_nat_sync

# Description
Sync NAT list with Mikrotik RouterOS API

# Environment variables
```
$HOST='127.0.0.1';
$USER='admin'; 
$PASS=''; 
$TAG='automanaged';
$SOURCE='./files/list.json';
$DEBUG='';
$DELAY=5;
```
source can also be an url
```
$SOURCE='http://example.com/nat-list.json';
```

where
* HOST - ip of router. uses default http api port
* USER / PASS - username and password to access the router
* TAG - this will be the text with which all comments will starts; comment format will be "automananged{...<json>...}
* DEBUG - if set to any value longer than 1 character it will be enabled
* DELAY - how long to wait after one round of processing before starting the next one
* SOURCE - url or file path (starting with ./) that contains the configuration to be applied on the router. If a file path is used you must mount your folder to /app/files/ container folder


# JSON payload structure
```
{
  "<source>": {
    "<id>": {
       ... settings ...
    }
  }
}
```

# JSON Payload example
In this example we forward external port 80 to internal 192.168.50.100:80
```
{
  "manual": {
    "web": {
      "to-addresses": "192.168.50.100",
      "to-ports": "80",
      "dst-port": "80"
    }
  }
}
```

# Usage
1. on router create a nat rule that is attached and has comment 'automanaged{"source":"template","id":"template"}' and disable it. The values in this rule will be used as a template when creating/updating rules generated from the json payload (for example if you set the chain to dstnat then all the rules from json that to do not have chain set will use dstnat value)
2. deploy this via docker (or however else you like)


