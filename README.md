# Minimum Requirements (as tested)
1GB RAM
Ubuntu 16.04 LTS

# Usage

### Build and Test using Dockerfile
```sh
$ docker build -t [REPOSITORY NAME] .
$ docker run -d -p 8080:8080 [REPOSITORY NAME]
$ ./do-package-tree_linux
```

# Design Rationale
There were a few project requirements that gave me some direction.
- MySQL DB
-- Since this should support multiple clients, data persistence is a must.  I chose MySQL out of convenience and familiarity.
- Forked Processing
-- Must support up to 100 concurrent clients.
-- I went with the simplest, fastest solution that works.  Considered alternatives include multiple threads and async job queue.  I felt that forked processes was much simpler to execute than a job queue in this case (how to reliably return accurate responses?), and slightly more robust than multithreading (single processes dedicated to processing each request without threat of race conditions, crashes, etc...).

