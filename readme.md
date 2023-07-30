# Mary

:warning: **Work in progress**

Laravel blade components for Livewire / DaisyUI / Tailwind.


## Requirements

- Laravel 10+
- Livewire 3
- Daisy UI
- Tailwind

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
        ...

        @vite(['resources/css/app.css', 'resources/js/app.js'])       
        
        <!-- add this  -->
        @mary  
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

### Drawer

### Form

### Header

### Input

### ListItem

### Modal

### Nav

### Select

### Tab

### Tabs

### Toggle

## Testing

``` bash
composer test
```

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
 

## Credits

- [Robson Tenório][link-author]
- [All Contributors][link-contributors]
