# ACE
Async Command Executer using ReactPHP because... yes

## How the **** I use this

The only thing that needs to be done is to create a `config.yml` file in the same working directory of the `ace` executable.

This file defines several chunks of commands. The chunks will be iterated sequentially, but the commands inside of a chunk will be executed asynchronously.

```yaml
ace:
    command-chunks:
        hilarious-echoes:
            - 'echo huehue >> ~/output.txt'
            - 'echo muahaha >> ~/output.txt'
            - 'echo hohoho >> ~/output.txt'
            - 'echo hihihi >> ~/output.txt'
        still-better-than-finder:
            - 'find ~/meows/ -name "kittens"'
            - 'find ~/barks/ -name "puppies"'
            - 'find /pub -name "the_drunken_clam"'
```
