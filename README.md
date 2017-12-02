# ACE

[![Build Status](https://travis-ci.org/dalailomo/ace.svg?branch=master)](https://travis-ci.org/dalailomo/ace)

Async Command Executor using ReactPHP because... yes

## Installation

> ACE requires `php >= 7.1`.

Download the [latest release](https://github.com/dalailomo/ace/releases/latest).

Copy the executable to your bin folder (make sure it has the proper execution permissions):

```bash
$ mv ~/Downloads/ace.phar /usr/local/bin/ace
```

## Example of usage

### The `setup` command

To start using ACE, type on the terminal:

```bash
$ ace setup
```

The first time we run this command, the following files and folders will be created behind the scenes:

```
~/.ace/
|--- config.yml
|--- log/
``` 

- The `config.yml` file will be generated with some default command groups as an example.
- The `log/` folder is used to store all the output streams for each command we run.

Then, we will see a menu with some choices.

- `[0] Quit` 
- `[1] List command groups` 
- `[2] Execute commands`
- `[3] Logs`
- `[4] Edit configuration file`

#### `[0] Quit`

This option just quits the program

#### `[1] List command groups`

This option provides a list of the defined keys and command groups to be executed. In this case it will show something like the following:

```
finder-examples
-------------------------------------------------
find-phars
        find -E ~/ -regex ".*\.(phar)"
        find -E /usr -regex ".*\.(phar)"

find-images
        find -E ~/Desktop -regex ".*\.(jpg|gif|png|jpeg)"
        find -E ~/Downloads -regex ".*\.(jpg|gif|png|jpeg)"
        find -E ~/Documents -regex ".*\.(jpg|gif|png|jpeg)"

more-finder-examples
-------------------------------------------------
find-zips
        find -E ~/Desktop -regex ".*\.(zip)"
        find -E ~/Downloads -regex ".*\.(zip)"

find-tarballs
        find -E ~/Desktop -regex ".*\.(tar)"
        find -E ~/Downloads -regex ".*\.(tar)"
```

So this means that the key `finder-examples` has two command groups, `find-phars`, and `find-images`.

The two commands on `find-phars` will be executed in parallel. Then, when all the commands finished the execution, then the three commands on the second group `find-images` will start executing in parallel.

Also the key `finder-examples` has two command groups, `find-zips`, and `find-tarballs`.

The two commands on `find-zips` will be executed in parallel. Then, when all the commands finished the execution, then the two commands on the second group `find-tarballs` will start executing in parallel.

#### `[2] Execute commands`

This option allows to execute the command groups found on a key.

It will show the following choices:

- `[0] Back to main menu` Goes back to the main menu
- `[1] Execute group with key "finder-examples"` Executes all the command groups found on the key `finder-examples`
- `[2] Execute group with key "more-finder-examples"` Executes all the command groups found on the key `more-finder-examples`

When executing a key, you will see something like the following:

```
Starting process group find-phars
Started: find -E ~/ -regex ".*\.(phar)"
Started: find -E /usr -regex ".*\.(phar)"
Finished 56848 : find -E /usr -regex ".*\.(phar)" : Exit code (1)
Finished 56847 : find -E ~/ -regex ".*\.(phar)" : Exit code (0)


Starting process group find-images
Started: find -E ~/Desktop -regex ".*\.(jpg|gif|png|jpeg)"
Started: find -E ~/Downloads -regex ".*\.(jpg|gif|png|jpeg)"
Started: find -E ~/Documents -regex ".*\.(jpg|gif|png|jpeg)"
Finished 56883 : find -E ~/Desktop -regex ".*\.(jpg|gif|png|jpeg)" : Exit code (0)
Finished 56884 : find -E ~/Downloads -regex ".*\.(jpg|gif|png|jpeg)" : Exit code (0)
Finished 56885 : find -E ~/Documents -regex ".*\.(jpg|gif|png|jpeg)" : Exit code (0)


Time spent: 8.45 seconds
Log file: /Users/dalai/.ace/log/1512140675.finder-examples.log.json


Finished. Press "Enter" to continue
```

Let's explain what's going on here:

```
Starting process group find-phars
```

This is telling us that the processes on the group are about to be executed.

```
Started: find -E ~/ -regex ".*\.(phar)"
Started: find -E /usr -regex ".*\.(phar)"
```

This is telling us that the two commands have been started running.

```
Finished 56848 : find -E /usr -regex ".*\.(phar)" : Exit code (1)
Finished 56847 : find -E ~/ -regex ".*\.(phar)" : Exit code (0)
```

After some time (depending on how long it takes for the processes), we will see that the execution of the processes have finished, showing as well the process id and the exit code returned by the process.

As you can see, in my case the first one returned an exit code of 1 because we might not have permissions to `find` anything on the `/usr` folder.

#### `[3] Logs`

This option is to view the outputs of the commands. 

It will show something like the following:

- `[0] Back to main menu` Goes back to the main menu
- `[1] finder-examples @ 2017-12-01T15:04:35+0000 [ png(45) ]` These are the logs for the commands we just executed. As you can see, it indicates the date it was executed and the number of occurrences found on the output of all the commands executed for the highlighted keyword `png` in this case.

#### `[4] Edit configuration file`

This just opens the `~/.ace/config.yml` with a text editor. 

You can also open this configuration file with any other text editor, actually it makes no difference. 

### The `execute` command

This is used to execute the groups of commands. Once you finished configuring ACE with the `setup` command, you can run the following to execute the commands under the `finder-examples` key:

```bash
$ ./ace execute -k finder-examples
```

You can also filter by group just adding the group name after the key name:

```bash
$ ./ace execute -k finder-examples find-phars 

$ ./ace execute -k finder-examples find-images
```

Multiple groups can be added as well:

```bash
$ ./ace execute -k finder-examples find-images find-phars
```

Note that the groups will be executed in the specified order. You can even repeat a group if you want to execute it twice (or more):

```bash
$ ./ace execute -k finder-examples find-images find-phars find-images
```

If you want to see the diagnosis output (aka `STDERR` stream output) while running, add the `--diagnosis` option (or `-d`). 

> The diagnosis output will be interleaved with other diagnosis outputs from other commands. Anyway, the diagnosis output for each command will be logged in a separate file.

A log file for each run will be created on `~/.ace/log/` in json format. 

### Known issues & annoyances

#### High CPU usage if you are greedy

At the moment, there is no control over the resources used by the commands you put on a group, so be careful and try not to put too many commands on a group. The CPU usage can go nuts and there is a remote possibility that you can create a high enough energy event pushing a tiny region of the universe from the false vacuum into the true bacon, creating a bubble that will expand in all directions at the speed of light. 
