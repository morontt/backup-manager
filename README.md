# Backup Manager

## Installation

```bash
    composer install
    # and edit config/config.yml
```

Create [Dropbox application](https://www.dropbox.com/developers/apps),
choose an *Dropbox API* and *App folder*, save **key** and **secret** to *config.yml*

Get access token for site *myblog*

```bash
    ./console dropbox:auth myblog
```

### Requirements

* openssl

### Decryption

Decrypt encrypted file

```bash
    openssl enc -d -{cipher} -in /path/to/file.enc -out /path/to/decrypted/file
    # and enter encryption key
```
