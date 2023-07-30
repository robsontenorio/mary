# Mary


Blade components for Livewire 3 based on Tailwind + Daisy UI.


## Requirements

- Laravel 10 >=
- Vite
- Livewire 3
- Tailwind
- Daisy UI

## Installation


Require the package.

```bash
composer require robsontenorio/mary
```

Add a new entry on `tailwind.config.js`.

```js
content: [
    ...

    "./vendor/robsontenorio/mary/src/View/Components/**/*.php"
  ],
```

Add `@mary` on your blade layout.

```html
<html>
    <head>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
                
        @mary  <!-- add this  -->
    </head>

    <body>
        ...
    </body>
</html>
```

## Usage

### Alert

### Button

### Card

## Testing

``` bash
composer test
```

## Contributing

:warning: Make sure your run this steps **ON YOUR OWN APP.**

- Clone this repository into some folder  **on your own app**.

- On **your own app** `composer.json` add this settings.

```json
"minimum-stability": "dev", // <---- change to "dev"
"repositories": {
    "robsontenorio/mary": {
        "type": "path",
        "url": "/path/to/mary", // <--- change the path
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

- Start vite 

```bash
yarn dev
```
 

## Credits

- [Robson Tenório][link-author]
- [All Contributors][link-contributors]
