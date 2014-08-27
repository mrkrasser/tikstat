# tikstat - A simple Mikrotik traffic counter script with web interface

## why?
- Very simple. Receive interface counters, parse on hours and save in SQLite database.
- Work without public static ip. Router send data to script on hosting
- Notify users about amount of traffic

## how?
1. Create mikrotik script 
```
:local sysnumber [/system routerboard get value-name=serial-number]
:local txbyte [/interface ethernet get ether1-gateway value-name=driver-tx-byte]
:local rxbyte [/interface ethernet get ether1-gateway value-name=driver-rx-byte]
/tool fetch url=("http://server.com/tikstat/collector.php\?sn=$sysnumber&tx=$txbyte&rx=$rxbyte") mode=http keep-result=no
```
2. Add this script to mikrotik scheduler
3. View graphs

## needed
tikstat use [PHPMailer](https://github.com/PHPMailer/PHPMailer) for sending messages for users