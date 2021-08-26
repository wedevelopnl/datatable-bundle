The Symfony DatatableBundle
=======================

The Symfony DatatableBundle is an easy way to create sortable and filterable tables

## Installation

Use composer to install webmen/datatabundle
```
composer require webmen/datatable-bundle
```

Add the assets to your package.json
```
"@webmen/datatable-bundle": "file:vendor/webmen/datatable-bundle/assets"
```

Import the javascript in your own entry file
```javascript
import '@webmen/datatable-bundle/js/index';
```

Import the SCSS styling in your own entry file
```scss
@import "~@webmen/datatable-bundle/scss/index";
```

Now you should be able to use this bundle in your symfony application

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
