# Mary

:warning: **Work in progress**

https://mary-ui.com

## Contributing


- Clone the repositoy `git clone git@github.com:robsontenorio/mary.git`
  into some folder **inside your app**.
  
- Change `composer.json` from **your app**

```json
"minimum-stability": "dev",      // <- change to "dev"
"repositories": {
    "robsontenorio/mary": {
        "type": "path",
        "url": "/path/to/mary",  // <- change the path
        "options": {
            "symlink": true
        }
    }
}
```

- Require the package again for local symlink.

```bash
composer require robsontenorio/mary
```

- Start dev  

```bash
yarn dev
```
