# ACE
Async Command Executor using ReactPHP because... yes

## How the **** I use this

### Configuration
The only thing that needs to be done is to create a `config.yml` file in the same working directory of the `ace` executable.

This file defines several chunks of commands. The chunks will be iterated sequentially, but the commands inside of a chunk will be executed asynchronously. All the chunks can be grouped as well by keys so you can always choose which key you want to execute.

```yaml
yourkey:
    command-chunks:
        sleeps:
            - 'sleep 3'
            - 'sleep 5'
            - 'sleep 2'
            - 'sleep 1'
        more_sleeps:
            - 'sleep 6'
            - 'sleep 2'
            - 'sleep 4'
```

### Running

To execute the chunks, just run:

```bash
$ ./ace ace:execute -k yourkey
```

If you want to see the diagnosis output while running, add the `--diagnosis` option (or `-d`). 

> The diagnosis output will be interleaved with other diagnosis outputs from other commands. Anyway, the diagnosis output for each command will be logged in a separate file.

```bash
$ ./ace ace:execute -d -k yourkey
```

Diagnosis will output the contents streamed to STDERR by the commands executed.

A log file for each run will be created on `<ace root dir>/var/log` (on this project, not the absolute `/var/log`) in json format. 

### Interactive setup

To execute the interactive setup, just run:

```bash
$ ./ace ace:setup
```

## Known issues & annoyances

#### High CPU usage if you are greedy

At the moment, there is no control over the resources used by the commands you put on a chunk, so be careful and try not to put too many commands on a chunk. The CPU usage can go nuts and there is a remote possibility that you can create a high enough energy event pushing a tiny region of the universe from the false vacuum into the true vacuum, creating a bubble that will expand in all directions at the speed of light. 

## Pro tip

You can create an alias to execute the chunks and setup.

```bash
$ alias acex="path/to/ace ace:execute"
$ alias aces="path/to/ace ace:setup"
```
