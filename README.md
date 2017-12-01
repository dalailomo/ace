# ACE

[![Build Status](https://travis-ci.org/dalailomo/ace.svg?branch=master)](https://travis-ci.org/dalailomo/ace)

Async Command Executor using ReactPHP because... yes

## Installation

> ACE requires `php >= 7.1`.

Download the [latest release](https://github.com/dalailomo/ace/releases/download/1.2.0/ace.phar).

Copy the executable to your bin folder (make sure it has the proper execution permissions):

```bash
$ mv ~/Downloads/ace.phar /usr/local/bin/ace
```

## Example of usage

### Configuration

To start configuring ACE, you will need to execute the setup command:

```bash
$ ace setup
```

Choose the option number 2, "Edit configuration file". This will open a vim editor to the config file (you can edit it manually as well in `~/.ace/config.yml`).

This file defines several groups of commands. The groups will be iterated sequentially, but the commands inside of a group will be executed asynchronously. All the groups can be grouped as well by keys so you can always choose which key you want to execute.

To start, we can paste the contents below to the config file:

```yaml
sleepers:
    command-groups:
        sleeps:
            - 'sleep 3'
            - 'sleep 5'
            - 'sleep 2'
            - 'sleep 1'
        more_sleeps:
            - 'sleep 6'
            - 'sleep 2'
            - 'sleep 4'
        even_more_sleeps:
            - 'sleep 2'
            - 'sleep 8'
            - 'sleep 1'
    highlight-keywords:
        - 'words'
        - 'to be'
        - 'highlighted'
        - 'if found in output'
        - 'on each command'
```

Save the contents of the file and close vim. Then you can choose the option number 0 "Quit" to exit the setup.

### Running

To execute the groups in the order coming from the config file, just run by the desired key, in this case will be `sleepers`:

```bash
$ ./ace execute -k sleepers
```

You can filter by groups just adding them after the key name:

```bash
$ ./ace execute -k sleepers even_more_sleeps more_sleeps
```

Note that the groups will be executed in order. You can even repeat a group if you want to execute it twice (or more):

```bash
$ ./ace execute -k sleepers sleeps more_sleeps sleeps
```

If you want to see the diagnosis output (aka `STDERR` stream output) while running, add the `--diagnosis` option (or `-d`). 

> The diagnosis output will be interleaved with other diagnosis outputs from other commands. Anyway, the diagnosis output for each command will be logged in a separate file.

A log file for each run will be created on `~/.ace/log/` in json format. 

### Known issues & annoyances

#### High CPU usage if you are greedy

At the moment, there is no control over the resources used by the commands you put on a group, so be careful and try not to put too many commands on a group. The CPU usage can go nuts and there is a remote possibility that you can create a high enough energy event pushing a tiny region of the universe from the false vacuum into the true bacon, creating a bubble that will expand in all directions at the speed of light. 
