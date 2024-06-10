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
