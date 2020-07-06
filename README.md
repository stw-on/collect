# Collect

[![Docker Cloud Build Status](https://img.shields.io/docker/cloud/build/stwon/collect?style=for-the-badge)](https://hub.docker.com/r/stwon/collect)
[![Docker Image Size (tag)](https://img.shields.io/docker/image-size/stwon/collect/latest?style=for-the-badge)](https://hub.docker.com/r/stwon/collect)
[![Built with science](https://img.shields.io/badge/Built%20with-Science-orange?style=for-the-badge)](https://www.youtube.com/watch?v=dQw4w9WgXcQ)

Collect is a simple file transfer tool suitable for environments where
structured files are collected from many people (e.g. job applications).

**Please note that this project is under active development and might not be
stable in its current state!**

## Features

- file validation
  - file count
  - file size
  - MIME types
- automatic scan for viruses using [docker-clamav](https://github.com/stw-on/docker-clamav)
- on-server file encryption (see below)
- simple UI

## Setup

#### Prerequisites

- Docker
- Docker Compose or Docker Swarm
- time

1. Copy `docker-compose.prod.yml` to your server and rename it to `docker-compose.yml`
2. Copy `.env.prod.example` to `.env` next to `docker-compose.yml`
3. Fill `APP_KEY` (32 char random string), `APP_URL`, `DB_PASSWORD` (random) and all `MAIL_*`
   variables in your `.env`
4. Run `docker-compose up`
5. Profit

The example `docker-compose.prod.yml` contains labels for use with [Træfik](https://traefik.io)
as reverse proxy and TLS terminator. You can safely remove these if you don't need
TLS or want to use another reverse proxy.

## Security & Encryption

After validation and checking for viruses, all files of a transfer will be
encrypted with a new random key. This key is never stored on the server itself
making it impossible to decrypt uploaded data in case of a data breach (e.g. due
to a misconfigured webserver).
However, due to the fact that the server has to check files before encryption and
send the key to its recipient, data can be compromised in case the entire server
is compromised.

We may add asymmetric encryption in the future, thus making it unnecessary
for the server to send keys to recipients. 
