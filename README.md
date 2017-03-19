# xiami-cli

The command line interface for Xiami.

It can easily download music, and only need a command.

## Install

Installing this tool is very simple.

* Make sure php is installed
* Copy ```dist/xiami.phar``` to any path and make sure it has execute permissions

## Usage

Once xiami-cli is installed, you can use it via command line like this.

### Login to your account

After logging in, you can use the ```my-songs``` command to access your personal library.

```
$ xiami login username password
$ xiami logout
```
### Download

You can use Id to download albums, songs and collection.

```
xiami song 9998 --download ~/Music
xiami album 9986 --download ~/Music/Albums
xiami collection 9988 --download ~/Music/Collections
```

### Download from your favorite collection

You need to login or specify userid to use this command.

```
xiami my-songs --download ~/Music --page all 
xiami my-songs --download ~/Music --page 6
xiami my-songs --download ~/Music --page all --userid 123 
```