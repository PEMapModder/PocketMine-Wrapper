# PocketMine-Wrapper
Simple scripts for controlling the lifecycle of PocketMine servers

This is a proof of concept that is tested on Windows.

Installation
===
* Get a clean installation of PocketMine-MP using the PocketMine installer.
* Download [`php/functions.php`](php/functions.php) and [`php/wrapper.php`](php/wrapper.php) **together** into anywhere in your system.
* Open command terminal and `cd` to your PocketMine-MP install directory. If `./PocketMine-MP.phar` is absent, the script will attempt to load `src/pocketmine/PocketMine.php` (run-from-source) instead.
* Run your downloaded `php/wrapper.php` with PHP (_with the current working directory at your PocketMine-MP install directory!_). You are recommended to use the PHP binaries provided by PocketMine-MP installer, since the `php_pthreads` extension is required. For example, on Windows:

```
bin\php\php.exe %USERPROFILE%\Downloads\wrapper.php
```

> Note: To avoid warnings related to timezones, edit your `php.ini` (usually next to your `php.exe`) and add this line:
> 
> ```
> date.timezone=Asia/Hong_Kong
> ```
> 
> Replace Asia/Hong_Kong with your timezone (https://php.net/timezones)
 
* Type `0` to make server run for infinite times, type `1` to run server once only, `2` for twice, vice versa. Then click enter.
* After that, the server will start and you can use the terminal like normal console.
* To shutdown the server (like `/stop`) and then stop restarting, type `-die` then enter.
* To forcefully shutdown the server, type `-kill` then enter.
