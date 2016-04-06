# Configuration Validator

Use this command line tool to validate a config against a JSON schema.

## Installation 

    git clone git@github.com:keboola/configuration-validator.git
    cd configuration-validator
    composer install

## Usage

### Locally

If you have both a config and a schema at hand, use this command

    php ./application.php validate schema.json configuration.json

### From a Storage API table

You can validate more configurations at the same time. Just prepare a table in a Storage API bucket and put the serialized configuration in the first column

    php ./application.php validate-table schema.json out.c-transformation.json-configs {STORAGE_API_TOKEN}


