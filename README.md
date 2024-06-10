# Mikrotik Nat Sync

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
