# Mary

:warning: **Work in progress**

https://mary-ui.com

## Contributing

:warning: Make sure your run this steps **INSIDE YOUR OWN APP.**

- Clone this repository into some folder.

- Change `composer.json`

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
